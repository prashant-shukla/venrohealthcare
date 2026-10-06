<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bedside Nursing billing helpers.
 *
 * Charges are pro-rata:
 *   - Daily   : days x daily rate
 *   - Weekly  : days x (weekly rate / 7)
 *   - Monthly : whole months counted from the period start (1 Jan - 31 Jan = 1 month,
 *               25 Aug - 24 Sep = 1 month); remaining days are pro-rated over the
 *               length of that month.
 *
 * Transport follows its own frequency the same way; "Flexible" transport is a
 * one-off agreed amount charged once at the start of its billing period.
 *
 * Each effective-dated billing period is charged independently using the
 * rate/frequency/transport that applied during that period. Superseded
 * (corrected) periods are kept for history but never charged.
 */

if (!function_exists('billing_add_days')) {
    function billing_add_days($date, $days)
    {
        $d = new DateTime($date);
        $d->modify(($days >= 0 ? '+' : '') . (int) $days . ' day');
        return $d->format('Y-m-d');
    }
}

if (!function_exists('billing_days_inclusive')) {
    /**
     * Inclusive number of days between two dates (both ends counted).
     */
    function billing_days_inclusive($start, $end)
    {
        if (empty($start) || empty($end) || $end < $start) {
            return 0;
        }
        $s = new DateTime($start);
        $e = new DateTime($end);
        return (int) $s->diff($e)->days + 1;
    }
}

if (!function_exists('billing_add_months')) {
    /**
     * Anchor date + N months, keeping the anchor's day of month where possible
     * (31 Jan + 1 month = 28/29 Feb, not 3 Mar).
     */
    function billing_add_months($anchor, $months)
    {
        $y = (int) substr($anchor, 0, 4);
        $m = (int) substr($anchor, 5, 2) + (int) $months;
        $d = (int) substr($anchor, 8, 2);
        $y += intdiv($m - 1, 12);
        $m = (($m - 1) % 12) + 1;
        $dim = (int) date('t', mktime(0, 0, 0, $m, 1, $y));
        return sprintf('%04d-%02d-%02d', $y, $m, min($d, $dim));
    }
}

if (!function_exists('billing_normalize_transport_frequency')) {
    /**
     * A transport amount without a frequency (legacy rows) is a one-off amount.
     */
    function billing_normalize_transport_frequency($frequency)
    {
        return in_array($frequency, array('Daily', 'Weekly', 'Monthly'), true) ? $frequency : 'Flexible';
    }
}

if (!function_exists('billing_cumulative')) {
    /**
     * Amount accrued from $anchor up to and including $until for a rate at a frequency.
     */
    function billing_cumulative($rate, $frequency, $anchor, $until)
    {
        $rate = (float) $rate;
        if ($rate == 0 || empty($anchor) || empty($until) || $until < $anchor) {
            return 0.0;
        }

        switch ($frequency) {
            case 'Daily':
                return billing_days_inclusive($anchor, $until) * $rate;

            case 'Weekly':
                return billing_days_inclusive($anchor, $until) * $rate / 7;

            case 'Monthly':
                $next_day = billing_add_days($until, 1);
                $full = 0;
                while (billing_add_months($anchor, $full + 1) <= $next_day) {
                    $full++;
                }
                $month_start = billing_add_months($anchor, $full);
                $month_end = billing_add_days(billing_add_months($anchor, $full + 1), -1);
                $partial_days = billing_days_inclusive($month_start, $until);
                $month_len = billing_days_inclusive($month_start, $month_end);
                return $full * $rate + ($month_len > 0 ? $partial_days / $month_len * $rate : 0);

            case 'Flexible':
            default:
                return $rate;
        }
    }
}

if (!function_exists('billing_range_amount')) {
    /**
     * Amount for the days $from..$to of a period anchored at $anchor.
     */
    function billing_range_amount($rate, $frequency, $anchor, $from, $to)
    {
        if ($to < $from) {
            return 0.0;
        }
        $before = ($from > $anchor) ? billing_cumulative($rate, $frequency, $anchor, billing_add_days($from, -1)) : 0.0;
        return billing_cumulative($rate, $frequency, $anchor, $to) - $before;
    }
}

if (!function_exists('billing_active_periods')) {
    function billing_active_periods($periods)
    {
        $out = array();
        foreach ((array) $periods as $p) {
            if (empty($p->is_superseded) && !empty($p->effective_from)) {
                $out[] = $p;
            }
        }
        return $out;
    }
}

if (!function_exists('billing_segments')) {
    /**
     * Each non-superseded period clamped to the service duration.
     *
     * @return array of objects: period, seg_start, seg_end
     */
    function billing_segments($service_start, $service_end, $periods, $until = null)
    {
        $segments = array();
        if (empty($service_start)) {
            return $segments;
        }

        foreach (billing_active_periods($periods) as $p) {
            $period_end = !empty($p->effective_to) ? $p->effective_to
                : (!empty($service_end) ? $service_end : (!empty($until) ? $until : $p->effective_from));

            $seg_start = max($p->effective_from, $service_start);
            $seg_end = !empty($service_end) ? min($period_end, $service_end) : $period_end;

            if ($seg_end < $seg_start) {
                continue;
            }
            $segments[] = (object) array('period' => $p, 'seg_start' => $seg_start, 'seg_end' => $seg_end);
        }
        return $segments;
    }
}

if (!function_exists('billing_assignment_breakdown')) {
    /**
     * Billing breakdown for an assignment across its effective-dated periods.
     *
     * @param string      $service_start assignment start date (Y-m-d)
     * @param string      $service_end   assignment end date (Y-m-d)
     * @param array       $periods       rows from nurse_billing_periods
     * @param string|null $until         only count days up to this date (accrued to date)
     * @return array rows, billing_total, transport_total, total
     */
    function billing_assignment_breakdown($service_start, $service_end, $periods, $until = null)
    {
        $rows = array();
        $billing_total = 0.0;
        $transport_total = 0.0;

        foreach (billing_segments($service_start, $service_end, $periods, $until) as $s) {
            $p = $s->period;
            $end = (!empty($until) && $until < $s->seg_end) ? $until : $s->seg_end;
            if ($end < $s->seg_start) {
                continue;
            }

            $t_freq = billing_normalize_transport_frequency(isset($p->transport_frequency) ? $p->transport_frequency : null);
            $t_rate = isset($p->transport_charge) ? (float) $p->transport_charge : 0.0;

            $charge = round(billing_cumulative($p->rate, $p->billing_frequency, $s->seg_start, $end), 2);
            $transport = round(billing_cumulative($t_rate, $t_freq, $s->seg_start, $end), 2);

            $billing_total += $charge;
            $transport_total += $transport;

            $rows[] = (object) array(
                'period_id' => isset($p->id) ? $p->id : null,
                'billing_frequency' => $p->billing_frequency,
                'rate' => $p->rate,
                'transport_frequency' => $t_rate > 0 ? $t_freq : null,
                'transport_charge' => $t_rate,
                'seg_start' => $s->seg_start,
                'seg_end' => $end,
                'days' => billing_days_inclusive($s->seg_start, $end),
                'charge' => $charge,
                'transport' => $transport,
                'total' => $charge + $transport,
            );
        }

        return array(
            'rows' => $rows,
            'billing_total' => $billing_total,
            'transport_total' => $transport_total,
            'total' => $billing_total + $transport_total,
        );
    }
}

if (!function_exists('billing_build_units')) {
    /**
     * Split an assignment into payable units up to $until:
     * one unit per day (Daily), per 7-day week (Weekly) or per month (Monthly),
     * counted from the start of each billing period. A unit still in progress
     * on $until is cut at $until.
     *
     * @return array of unit objects
     */
    function billing_build_units($assignment_id, $service_start, $service_end, $periods, $until)
    {
        $units = array();

        foreach (billing_segments($service_start, $service_end, $periods, $until) as $s) {
            $p = $s->period;
            $anchor = $s->seg_start;
            $t_freq = billing_normalize_transport_frequency(isset($p->transport_frequency) ? $p->transport_frequency : null);
            $t_rate = isset($p->transport_charge) ? (float) $p->transport_charge : 0.0;

            $i = 0;
            while (true) {
                if ($p->billing_frequency == 'Monthly') {
                    $from = billing_add_months($anchor, $i);
                    $to = billing_add_days(billing_add_months($anchor, $i + 1), -1);
                } elseif ($p->billing_frequency == 'Weekly') {
                    $from = billing_add_days($anchor, 7 * $i);
                    $to = billing_add_days($from, 6);
                } else {
                    $from = billing_add_days($anchor, $i);
                    $to = $from;
                }
                $i++;

                if ($from > $s->seg_end || $from > $until) {
                    break;
                }
                $full_to = min($to, $s->seg_end);
                $eff_to = min($full_to, $until);

                $billing = billing_range_amount($p->rate, $p->billing_frequency, $anchor, $from, $eff_to);
                $transport = billing_range_amount($t_rate, $t_freq, $anchor, $from, $eff_to);

                $units[] = (object) array(
                    'assignment_id' => $assignment_id,
                    'period_id' => isset($p->id) ? $p->id : null,
                    'frequency' => $p->billing_frequency,
                    'from' => $from,
                    'to' => $eff_to,
                    'in_progress' => $eff_to < $full_to,
                    'billing' => round($billing, 2),
                    'transport' => round($transport, 2),
                    'amount' => round($billing + $transport, 2),
                    'paid' => 0.0,
                );
            }
        }

        return $units;
    }
}

if (!function_exists('billing_allocate_payments')) {
    /**
     * Apply payments to units (sets $unit->paid). Order:
     *   1. payments for an assignment and a covered period -> units in that period
     *   2. the rest of payments for an assignment -> oldest unpaid units of that assignment
     *   3. payments not linked to an assignment -> oldest unpaid units overall
     * Whatever cannot be applied is an advance / credit.
     *
     * @return array general_credit, assignment_credit [assignment_id => amount]
     */
    function billing_allocate_payments($units, $payments)
    {
        usort($units, function ($a, $b) {
            if ($a->from == $b->from) {
                return $a->assignment_id - $b->assignment_id;
            }
            return strcmp($a->from, $b->from);
        });

        $payments = (array) $payments;
        usort($payments, function ($a, $b) {
            if ($a->payment_date == $b->payment_date) {
                return $a->id - $b->id;
            }
            return strcmp((string) $a->payment_date, (string) $b->payment_date);
        });

        $known = array();
        foreach ($units as $u) {
            $known[$u->assignment_id] = true;
        }

        $apply = function ($amount, $filter) use ($units) {
            foreach ($units as $u) {
                if ($amount <= 0.004) {
                    break;
                }
                if (!$filter($u)) {
                    continue;
                }
                $room = round($u->amount - $u->paid, 2);
                if ($room <= 0) {
                    continue;
                }
                $take = min($room, $amount);
                $u->paid = round($u->paid + $take, 2);
                $amount = round($amount - $take, 2);
            }
            return $amount;
        };

        $left = array();
        foreach ($payments as $k => $pay) {
            $left[$k] = (float) $pay->amount;
        }

        // 1. linked to an assignment and a covered period
        foreach ($payments as $k => $pay) {
            if (!empty($pay->assignment_id) && isset($known[$pay->assignment_id]) && !empty($pay->period_from) && !empty($pay->period_to)) {
                $aid = $pay->assignment_id;
                $pf = $pay->period_from;
                $pt = $pay->period_to;
                $left[$k] = $apply($left[$k], function ($u) use ($aid, $pf, $pt) {
                    return $u->assignment_id == $aid && $u->to >= $pf && $u->from <= $pt;
                });
            }
        }

        // 2. linked to an assignment
        $assignment_credit = array();
        foreach ($payments as $k => $pay) {
            if (!empty($pay->assignment_id) && isset($known[$pay->assignment_id])) {
                $aid = $pay->assignment_id;
                $left[$k] = $apply($left[$k], function ($u) use ($aid) {
                    return $u->assignment_id == $aid;
                });
                if ($left[$k] > 0.004) {
                    $assignment_credit[$aid] = (isset($assignment_credit[$aid]) ? $assignment_credit[$aid] : 0) + $left[$k];
                }
                $left[$k] = 0;
            }
        }

        // 3. not linked (or linked to an assignment that is no longer billed)
        $general_credit = 0.0;
        foreach ($payments as $k => $pay) {
            if ($left[$k] > 0.004) {
                $left[$k] = $apply($left[$k], function ($u) {
                    return true;
                });
                $general_credit += $left[$k];
            }
        }

        return array('general_credit' => round($general_credit, 2), 'assignment_credit' => $assignment_credit);
    }
}

if (!function_exists('billing_unit_label')) {
    function billing_unit_label($unit)
    {
        if ($unit->from == $unit->to) {
            return date('D, d M Y', strtotime($unit->from));
        }
        return date('d M Y', strtotime($unit->from)) . ' – ' . date('d M Y', strtotime($unit->to));
    }
}
