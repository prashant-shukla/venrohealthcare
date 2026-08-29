<section id="main-content">
    <section class="wrapper site-min-height">

        <?php if ($this->session->flashdata('success')) { ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php } ?>

        <!-- Placement summary -->
        <section class="panel">
            <header class="panel-heading">
                Billing Plan &mdash; <?php echo $assignment->nurse_name; ?>
                for <?php echo $assignment->patient_name; ?>
            </header>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4"><strong>Service Duration:</strong>
                        <?php echo !empty($assignment->start_date) ? date('d M Y', strtotime($assignment->start_date)) : '—'; ?>
                        &ndash;
                        <?php echo !empty($assignment->end_date) ? date('d M Y', strtotime($assignment->end_date)) : 'Ongoing'; ?>
                    </div>
                    <div class="col-md-4"><strong>Current Frequency:</strong>
                        <?php echo !empty($current) ? $current->billing_frequency : '—'; ?>
                    </div>
                    <div class="col-md-4"><strong>Current Rate:</strong>
                        <?php echo !empty($current) ? $this->currency . ' ' . number_format($current->rate, 2) : '—'; ?>
                        <?php echo !empty($current) ? '/ ' . rtrim(str_replace(array('Daily','Weekly','Monthly'), array('day','week','month'), $current->billing_frequency)) : ''; ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="row">
            <!-- Change billing arrangement -->
            <div class="col-md-6">
                <section class="panel">
                    <header class="panel-heading">Change Billing Arrangement</header>
                    <div class="panel-body">
                        <p class="text-muted" style="font-size:12px;">
                            A change does not overwrite the previous arrangement &mdash; the old rate/frequency
                            stays in the billing history and still applies to its period.
                        </p>
                        <form method="post" action="<?php echo base_url('nurse/changeBilling'); ?>">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment->id; ?>">

                            <div class="form-group">
                                <label>New Billing Frequency</label>
                                <select name="billing_frequency" class="form-control" required>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>New Rate</label>
                                <input type="number" step="0.01" name="rate" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Effective From</label>
                                <input type="date" name="effective_from" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Reason / Note</label>
                                <textarea name="note" class="form-control" rows="2"
                                    placeholder="e.g. patient agreement changed to weekly"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Apply Change</button>
                        </form>
                    </div>
                </section>
            </div>

            <!-- Extend service duration -->
            <div class="col-md-6">
                <section class="panel">
                    <header class="panel-heading">Extend Service Duration</header>
                    <div class="panel-body">
                        <form method="post" action="<?php echo base_url('nurse/extendService'); ?>">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment->id; ?>">
                            <div class="form-group">
                                <label>New End Date</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="<?php echo $assignment->end_date; ?>"
                                    min="<?php echo $assignment->start_date; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-info">Extend Placement</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <!-- Effective-dated billing history -->
        <section class="panel">
            <header class="panel-heading">Effective-Dated Billing History</header>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Frequency</th>
                            <th>Rate</th>
                            <th>Effective From</th>
                            <th>Effective To</th>
                            <th>Changed By</th>
                            <th>Recorded</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($periods)) { ?>
                            <?php foreach ($periods as $i => $p) { ?>
                                <tr>
                                    <td><?php echo $p->billing_frequency; ?></td>
                                    <td><?php echo $this->currency; ?> <?php echo number_format($p->rate, 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($p->effective_from)); ?></td>
                                    <td><?php echo !empty($p->effective_to) ? date('d M Y', strtotime($p->effective_to)) : 'Ongoing'; ?></td>
                                    <td><?php echo html_escape($p->created_by_name); ?></td>
                                    <td><?php echo !empty($p->created_at) ? date('d M Y H:i', strtotime($p->created_at)) : '—'; ?></td>
                                    <td><?php echo html_escape($p->note); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="7" class="text-center">No billing history.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Computed billing (per applicable period, pro-rated) -->
        <section class="panel">
            <header class="panel-heading">Calculated Billing (per applicable period)</header>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Frequency</th>
                            <th>Rate</th>
                            <th>Days</th>
                            <th>Charge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($breakdown['rows'])) { ?>
                            <?php foreach ($breakdown['rows'] as $r) { ?>
                                <tr>
                                    <td><?php echo date('d M Y', strtotime($r->seg_start)); ?>
                                        &ndash; <?php echo date('d M Y', strtotime($r->seg_end)); ?></td>
                                    <td><?php echo $r->billing_frequency; ?></td>
                                    <td><?php echo $this->currency; ?> <?php echo number_format($r->rate, 2); ?></td>
                                    <td><?php echo $r->days; ?></td>
                                    <td><b><?php echo $this->currency; ?> <?php echo number_format($r->charge, 2); ?></b></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="4" class="text-right"><b>Total (excl. transport)</b></td>
                                <td><b><?php echo $this->currency; ?> <?php echo number_format($breakdown['total'], 2); ?></b></td>
                            </tr>
                        <?php } else { ?>
                            <tr><td colspan="5" class="text-center">No billing to calculate.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p class="text-muted" style="font-size:12px;">
                    Weekly/monthly charges are rounded up to whole billing periods (any part-week is
                    charged as a full week, any part-month as a full month). Historical periods are
                    calculated using the rate/frequency that applied at that time.
                </p>
            </div>
        </section>

    </section>
</section>
