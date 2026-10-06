<?php
$cur = $this->currency;
$unit_name = array('Daily' => 'Day', 'Weekly' => 'Week', 'Monthly' => 'Month');
$unpaid_total = 0;
foreach ($assignments as $a) {
    $unpaid_total += $a->unpaid_units;
}
?>
<section id="main-content">
    <section class="wrapper">

        <section class="panel">

            <header class="panel-heading">
                <?php echo html_escape($nurse->name); ?> — Unpaid Days / Weeks
                <span class="pull-right">
                    <?php if ($show_all) { ?>
                        <a href="<?php echo base_url('nurse/unpaid/' . $nurse->id); ?>" class="btn btn-default btn-xs">Show unpaid only</a>
                    <?php } else { ?>
                        <a href="<?php echo base_url('nurse/unpaid/' . $nurse->id . '?all=1'); ?>" class="btn btn-default btn-xs">Show paid as well</a>
                    <?php } ?>
                    <a href="<?php echo base_url('nurse/payment/' . $nurse->id); ?>" class="btn btn-success btn-xs"><i class="fa fa-money-bill"></i> Payments</a>
                </span>
            </header>

            <div class="panel-body">

                <?php if ($this->session->flashdata('success')) { ?>
                    <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('success')); ?></div>
                <?php } ?>
                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="alert alert-danger">
                            <small>Due today (<?php echo date('d M Y', strtotime($today)); ?>)</small>
                            <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($totals['due_today'], 2); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-warning">
                            <small>Unpaid / part-paid periods</small>
                            <h4 style="margin:4px 0 0;"><?php echo $unpaid_total; ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-success">
                            <small>Advance / credit not yet applied</small>
                            <h4 style="margin:4px 0 0;"><?php echo $cur; ?> <?php echo number_format($totals['credit'], 2); ?></h4>
                        </div>
                    </div>
                </div>

                <p class="text-muted" style="font-size:12px;">
                    Daily placements are listed per day, weekly per week and monthly per month, counted from the start of each
                    billing arrangement, up to today. Payments recorded for a specific period are applied to that period first;
                    other payments are applied to the oldest unpaid periods.
                </p>

                <?php if (empty($assignments)) { ?>
                    <p class="text-center text-muted">No active assignments.</p>
                <?php } ?>

                <?php foreach ($assignments as $a) { ?>
                    <h4 style="margin-top:25px;">
                        <b><?php echo html_escape($a->patient_name); ?></b>
                        <small><?php echo date('d M Y', strtotime($a->start_date)); ?> – <?php echo date('d M Y', strtotime($a->end_date)); ?>
                            · Due today: <?php echo $cur . ' ' . number_format($a->due_today, 2); ?></small>
                    </h4>

                    <?php
                    $rows = array();
                    foreach ($a->units as $u) {
                        if ($show_all || $u->amount - $u->paid > 0.004) {
                            $rows[] = $u;
                        }
                    }
                    ?>

                    <?php if (empty($a->units)) { ?>
                        <p class="text-muted">This placement has not started yet.</p>
                    <?php } elseif (empty($rows)) { ?>
                        <p class="text-success"><i class="fa fa-check-circle"></i> All days served so far are paid.</p>
                    <?php } else { ?>
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Type</th>
                                    <th>Fee</th>
                                    <th>Transport</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                    <th>Status</th>
                                    <th class="no-print"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $u) {
                                    $outstanding = round($u->amount - $u->paid, 2);
                                ?>
                                    <tr>
                                        <td><?php echo billing_unit_label($u); ?></td>
                                        <td>
                                            <?php echo $unit_name[$u->frequency]; ?>
                                            <?php if ($u->in_progress) { ?><small class="text-muted">(in progress)</small><?php } ?>
                                        </td>
                                        <td><?php echo $cur . ' ' . number_format($u->billing, 2); ?></td>
                                        <td><?php echo $u->transport > 0 ? $cur . ' ' . number_format($u->transport, 2) : '—'; ?></td>
                                        <td><?php echo $cur . ' ' . number_format($u->amount, 2); ?></td>
                                        <td><?php echo $cur . ' ' . number_format($u->paid, 2); ?></td>
                                        <td><b><?php echo $cur . ' ' . number_format($outstanding, 2); ?></b></td>
                                        <td>
                                            <?php if ($outstanding <= 0) { ?>
                                                <span class="label label-success">Paid</span>
                                            <?php } elseif ($u->paid > 0) { ?>
                                                <span class="label label-warning">Part paid</span>
                                            <?php } else { ?>
                                                <span class="label label-danger">Unpaid</span>
                                            <?php } ?>
                                        </td>
                                        <td class="no-print">
                                            <?php if ($outstanding > 0) { ?>
                                                <a class="btn btn-success btn-xs" href="<?php echo base_url('nurse/payment/' . $nurse->id
                                                    . '?assignment_id=' . $a->id . '&period_from=' . $u->from . '&period_to=' . $u->to . '&amount=' . $outstanding); ?>">
                                                    Record payment
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                <?php } ?>

            </div>
        </section>

    </section>
</section>
