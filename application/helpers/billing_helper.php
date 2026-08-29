<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bedside Nursing billing helpers.
 *
 * Billing rounds UP to whole billing periods:
 *   - Daily   : days x daily_rate
 *   - Weekly  : ceil(days / 7)  x weekly_rate   (any part-week counts as a full week)
 *   - Monthly : ceil(days / 30) x monthly_rate  (any part-month counts as a full month)
 *
 * Each effective-dated period is charged independently using the rate/frequency
 * that applied during that period.
 */

if (!function_exists('billing_per_day_rate')) {
    /**
     * Convert a billing rate at a given frequency into an equivalent per-day rate.
     * (Kept for reference/reporting; the charge uses whole-period rounding below.)
     */
    function billing_per_day_rate($rate, $frequency)
    {
        $rate = (float) $rate;
        switch ($frequency) {
            case 'Weekly':
                return $rate / 7;
            case 'Monthly':
                return $rate / 30; // 30-day month convention
            case 'Daily':
            default:
                return $rate;
        }
    }
}

if (!function_exists('billing_days_inclusive')) {
    /**
     * Inclusive number of days between two dates (both ends counted).
     */
    function billing_days_inclusive($start, $end)
    {
        if (empty($start) || empty($end)) {
            return 0;
        }
        $s = strtotime($start);
        $e = strtotime($end);
        if ($e < $s) {
            return 0;
        }
        return (int) floor(($e - $s) / 86400) + 1;
    }
}

if (!function_exists('billing_period_charge')) {
    /**
     * Charge for a number of days at a rate/frequency, rounding UP to whole
     * billing periods (whole weeks / whole months).
     */
    function billing_period_charge($rate, $frequency, $days)
    {
        $rate = (float) $rate;
        $days = (int) $days;
        if ($days <= 0) {
            return 0.0;
        }
        switch ($frequency) {
            case 'Weekly':
                return ceil($days / 7) * $rate;
            case 'Monthly':
                return ceil($days / 30) * $rate;
            case 'Daily':
            default:
                return $days * $rate;
        }
    }
}

if (!function_exists('billing_assignment_breakdown')) {
    /**
     * Compute the billing breakdown for an assignment across its effective-dated
     * billing periods, clamped to the service duration.
     *
     * @param string $service_start  assignment start date (Y-m-d)
     * @param string $service_end    assignment end date (Y-m-d)
     * @param array  $periods        rows from nurse_billing_periods (objects), ordered by effective_from
     * @return array  ['rows' => [...], 'total' => float]
     */
    function billing_assignment_breakdown($service_start, $service_end, $periods)
    {
        $rows = array();
        $total = 0.0;

        // Without a start date there is nothing to calculate.
        if (empty($service_start)) {
            return array('rows' => $rows, 'total' => $total);
        }

        foreach ($periods as $p) {
            if (empty($p->effective_from)) {
                continue;
            }

            // Effective service end for this segment: period end, else placement end,
            // else the period's own effective_from (single-day fallback for ongoing/unbounded).
            $period_end = !empty($p->effective_to) ? $p->effective_to
                : (!empty($service_end) ? $service_end : $p->effective_from);
            $service_end_eff = !empty($service_end) ? $service_end : $period_end;

            // Clamp the period to the service duration.
            $seg_start = max(strtotime($p->effective_from), strtotime($service_start));
            $seg_end = min(strtotime($period_end), strtotime($service_end_eff));

            if ($seg_end === false || $seg_start === false || $seg_end < $seg_start) {
                continue; // period does not overlap the service duration
            }

            $seg_start_d = date('Y-m-d', $seg_start);
            $seg_end_d = date('Y-m-d', $seg_end);
            $days = billing_days_inclusive($seg_start_d, $seg_end_d);
            $charge = billing_period_charge($p->rate, $p->billing_frequency, $days);
            $total += $charge;

            $rows[] = (object) array(
                'billing_frequency' => $p->billing_frequency,
                'rate' => $p->rate,
                'seg_start' => $seg_start_d,
                'seg_end' => $seg_end_d,
                'days' => $days,
                'charge' => $charge,
            );
        }

        return array('rows' => $rows, 'total' => $total);
    }
}
