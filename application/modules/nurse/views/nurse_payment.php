<?php
$cur = $this->currency;
$t_unit = array('Daily' => '/ day', 'Weekly' => '/ week', 'Monthly' => '/ month', 'Flexible' => 'agreed');
$f_unit = array('Daily' => '/ day', 'Weekly' => '/ week', 'Monthly' => '/ month');
$balance = $totals['balance_till_today'];
?>
<section id="main-content">
    <section class="wrapper">

        <section class="panel">

            <header class="panel-heading">
                <?php echo html_escape($nurse->name); ?> — Payment Details
                <span class="pull-right">
                    <a href="<?php echo base_url('nurse/unpaid/' . $nurse->id); ?>" class="btn btn-warning btn-xs"><i class="fa fa-calendar-times"></i> Unpaid days / weeks</a>
                    <a href="<?php echo base_url('nurse/record/' . $nurse->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-folder-open"></i> Nurse record</a>
                </span>
            </header>

            <div class="panel-body">

                <?php if ($this->session->flashdata('success')) { ?>
                    <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
                <?php } ?>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                <?php } ?>

                <!-- SUMMARY -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="alert alert-info">
                            <small>Total billing (full placements)</small>
                            <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($totals['contract'], 2); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-warning">
                            <small>Total balance till today (<?php echo date('d M Y', strtotime($today)); ?>)</small>
                            <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($totals['till_today'], 2); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="alert alert-success">
                            <small>Total paid</small>
                            <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($totals['paid'], 2); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php if ($balance >= 0) { ?>
                            <div class="alert alert-danger">
                                <small>Due today</small>
                                <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($balance, 2); ?></h4>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-success">
                                <small>Advance / credit (paid ahead)</small>
                                <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format(-$balance, 2); ?></h4>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <p class="text-muted" style="font-size:12px;margin-top:-8px;">
                    Remaining for the full placements after payments: <strong><?php echo $cur; ?> <?php echo number_format($totals['remaining_contract'], 2); ?></strong>.
                    "Total balance till today" counts only the days served up to today; "Due today" is that amount minus payments.
                </p>

                <hr>

                <!-- PAYMENT ADD FORM -->
                <h4><b>Record Payment</b></h4>
                <form method="post" action="<?php echo base_url('nurse/addPayment'); ?>">
                    <input type="hidden" name="nurse_id" value="<?php echo $nurse->id; ?>">

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Amount (<?php echo $cur; ?>)</label>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="<?php echo html_escape($prefill['amount']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Assignment</label>
                                <select name="assignment_id" class="form-control">
                                    <option value="">Not linked — apply to oldest unpaid</option>
                                    <?php foreach ($assignments as $a) { ?>
                                        <option value="<?php echo $a->id; ?>" <?php echo $prefill['assignment_id'] == $a->id ? 'selected' : ''; ?>>
                                            <?php echo html_escape($a->patient_name); ?> (<?php echo date('d M Y', strtotime($a->start_date)); ?> – <?php echo date('d M Y', strtotime($a->end_date)); ?>)
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Covers From <small class="text-muted">(optional)</small></label>
                                <input type="date" name="period_from" class="form-control" value="<?php echo html_escape($prefill['period_from']); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Covers To <small class="text-muted">(optional)</small></label>
                                <input type="date" name="period_to" class="form-control" value="<?php echo html_escape($prefill['period_to']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Note</label>
                                <input type="text" name="note" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div class="checkbox" style="margin-top:6px;">
                                <label><input type="checkbox" name="is_advance" value="1"> Advance payment (paid ahead)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block">Add Payment</button>
                        </div>
                    </div>
                </form>

                <hr>

                <!-- BILLING TABLE -->
                <h4><b>Billing by Assignment</b></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Period</th>
                            <th>Current Arrangement</th>
                            <th>Total (full placement)</th>
                            <th>Total balance till today</th>
                            <th>Paid</th>
                            <th>Due today</th>
                            <th class="no-print"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($assignments)) { ?>
                            <?php foreach ($assignments as $a) { $cp = $a->current; ?>
                                <tr>
                                    <td><?php echo html_escape($a->patient_name); ?></td>
                                    <td><?php echo date('d M Y', strtotime($a->start_date)); ?> – <?php echo date('d M Y', strtotime($a->end_date)); ?></td>
                                    <td>
                                        <?php echo $cp->billing_frequency; ?>: <?php echo $cur . ' ' . number_format($cp->rate, 2) . ' ' . $f_unit[$cp->billing_frequency]; ?>
                                        <?php if ($cp->transport_charge > 0) { ?>
                                            <br><small class="text-muted">Transport: <?php echo $cur . ' ' . number_format($cp->transport_charge, 2) . ' ' . $t_unit[billing_normalize_transport_frequency($cp->transport_frequency)]; ?></small>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $cur; ?> <?php echo number_format($a->contract['total'], 2); ?></td>
                                    <td><?php echo $cur; ?> <?php echo number_format($a->till_today, 2); ?></td>
                                    <td><?php echo $cur; ?> <?php echo number_format($a->paid_applied, 2); ?></td>
                                    <td>
                                        <b><?php echo $cur; ?> <?php echo number_format($a->due_today, 2); ?></b>
                                        <?php if ($a->credit > 0) { ?>
                                            <br><small class="text-success">Advance credit: <?php echo $cur . ' ' . number_format($a->credit, 2); ?></small>
                                        <?php } ?>
                                    </td>
                                    <td class="no-print">
                                        <a href="<?php echo base_url('nurse/billing/' . $a->id); ?>" class="btn btn-primary btn-xs">Billing</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <th colspan="3" class="text-right">Totals</th>
                                <th><?php echo $cur; ?> <?php echo number_format($totals['contract'], 2); ?></th>
                                <th><?php echo $cur; ?> <?php echo number_format($totals['till_today'], 2); ?></th>
                                <th><?php echo $cur; ?> <?php echo number_format($totals['till_today'] - $totals['due_today'], 2); ?></th>
                                <th><?php echo $cur; ?> <?php echo number_format($totals['due_today'], 2); ?></th>
                                <th class="no-print"></th>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="text-center">No active assignments</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if ($totals['credit'] > 0) { ?>
                    <p class="text-success" style="font-size:12px;">
                        <?php echo $cur . ' ' . number_format($totals['credit'], 2); ?> has been paid ahead of the days served so far
                        and will be applied automatically as further days accrue.
                    </p>
                <?php } ?>

                <!-- PAYMENT HISTORY -->
                <h4><b>Payment History</b></h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Assignment</th>
                            <th>Covers</th>
                            <th>Type</th>
                            <th>Note</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)) { ?>
                            <?php foreach (array_reverse($payments) as $pay) { ?>
                                <tr>
                                    <td><?php echo !empty($pay->payment_date) ? date('d M Y', strtotime($pay->payment_date)) : '—'; ?></td>
                                    <td><?php echo $cur; ?> <?php echo number_format($pay->amount, 2); ?></td>
                                    <td><?php echo !empty($pay->patient_name) ? html_escape($pay->patient_name) : '<span class="text-muted">Not linked</span>'; ?></td>
                                    <td><?php echo !empty($pay->period_from) ? date('d M Y', strtotime($pay->period_from)) . ' – ' . date('d M Y', strtotime($pay->period_to)) : '—'; ?></td>
                                    <td><?php echo !empty($pay->is_advance) ? '<span class="label label-info">Advance</span>' : '<span class="label label-default">Payment</span>'; ?></td>
                                    <td><?php echo html_escape($pay->note); ?></td>
                                    <td><?php echo html_escape($pay->recorded_by_name); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="7" class="text-center">No Payment Found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>

        </section>

    </section>
</section>
