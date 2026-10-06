<?php
$cur = $this->currency;
$term_of = array('Daily' => 'Day', 'Weekly' => 'Week', 'Monthly' => 'Month');
$cur_term = !empty($current) ? $term_of[$current->billing_frequency] : ($assignment->payment_term ?: 'Day');
$cur_rate = !empty($current) ? (float) $current->rate : null;
$fee_value = function ($term, $field) use ($cur_term, $cur_rate, $assignment) {
    if ($cur_rate !== null) {
        return $term === $cur_term ? $cur_rate : '';
    }
    return $assignment->$field;
};
$cur_tcharge = !empty($current) ? (float) $current->transport_charge : (float) $assignment->transport_charge;
$cur_tfreq = !empty($current) ? $current->transport_frequency : $assignment->transport_frequency;
$cur_tnote = !empty($current) ? $current->transport_note : $assignment->transport_note;

$eff_min = !empty($current) ? $current->effective_from : $assignment->start_date;
$eff_default = max(date('Y-m-d'), $eff_min);
if (!empty($assignment->end_date) && $eff_default > $assignment->end_date) {
    $eff_default = $assignment->end_date;
}
$t_unit = array('Daily' => '/ day', 'Weekly' => '/ week', 'Monthly' => '/ month', 'Flexible' => 'agreed amount');
$back_url = $return === 'assign' ? base_url('nurse/assign/' . $assignment->nurse_id) : base_url('nurse/assignments');
?>
<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Edit Nurse Assignment — <?php echo html_escape($assignment->nurse_name); ?>
                <a href="<?php echo $back_url; ?>" class="btn btn-default btn-xs pull-right"><i class="fa fa-arrow-left"></i> Back</a>
            </header>

            <div class="panel-body">

                <?php if ($this->session->flashdata('error')) { ?>
                    <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('error')); ?></div>
                <?php } ?>

                <form method="post" action="<?php echo base_url('nurse/updateAssignment'); ?>">

                    <input type="hidden" name="id" value="<?php echo $assignment->id; ?>">
                    <input type="hidden" name="return" value="<?php echo $return; ?>">

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Patient</label>
                            <select name="patient_id" class="form-control">
                                <?php foreach ($patients as $p) { ?>
                                    <option value="<?php echo $p->id; ?>" <?php if ($assignment->patient_id == $p->id) echo 'selected'; ?>>
                                        <?php echo html_escape($p->name); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Assignment Role</label>
                            <select name="assignment_role" class="form-control">
                                <option value="Primary" <?php if ($assignment->assignment_role == 'Primary') echo 'selected'; ?>>Primary (Day) Nurse</option>
                                <option value="Additional" <?php if ($assignment->assignment_role == 'Additional') echo 'selected'; ?>>Additional / Alternate Nurse</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $assignment->start_date; ?>" class="form-control" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label>End Date</label>
                            <input type="date" name="end_date" value="<?php echo $assignment->end_date; ?>" class="form-control" required>
                        </div>
                    </div>

                    <h4 style="margin-top:10px;">Service Fee &amp; Transport</h4>
                    <p class="text-muted" style="font-size:12px;">
                        A fee or transport change does not overwrite the previous values: it starts a new billing period from
                        the "effective from" date below, and the old values keep applying before that date.
                        Use <?php echo date('d M Y', strtotime($eff_min)); ?> to correct the current values from the date they started.
                    </p>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Billing Frequency</label>
                            <select name="payment_term" id="edit_payment_term" class="form-control">
                                <option value="Day" <?php if ($cur_term == 'Day') echo 'selected'; ?>>Daily</option>
                                <option value="Week" <?php if ($cur_term == 'Week') echo 'selected'; ?>>Weekly</option>
                                <option value="Month" <?php if ($cur_term == 'Month') echo 'selected'; ?>>Monthly</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4" id="editDayBox">
                            <label>Per Day Fee (<?php echo $cur; ?>)</label>
                            <input type="number" step="0.01" min="0" name="per_day_fee" value="<?php echo $fee_value('Day', 'per_day_fee'); ?>" class="form-control">
                        </div>
                        <div class="form-group col-md-4" id="editWeekBox">
                            <label>Per Week Fee (<?php echo $cur; ?>)</label>
                            <input type="number" step="0.01" min="0" name="per_week_fee" value="<?php echo $fee_value('Week', 'per_week_fee'); ?>" class="form-control">
                        </div>
                        <div class="form-group col-md-4" id="editMonthBox">
                            <label>Per Month Fee (<?php echo $cur; ?>)</label>
                            <input type="number" step="0.01" min="0" name="per_month_fee" value="<?php echo $fee_value('Month', 'per_month_fee'); ?>" class="form-control">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Commute Frequency</label>
                            <select name="transport_frequency" id="edit_transport_frequency" class="form-control">
                                <option value="">-- None --</option>
                                <option value="Daily" <?php if ($cur_tfreq == 'Daily') echo 'selected'; ?>>Daily</option>
                                <option value="Weekly" <?php if ($cur_tfreq == 'Weekly') echo 'selected'; ?>>Weekly</option>
                                <option value="Monthly" <?php if ($cur_tfreq == 'Monthly') echo 'selected'; ?>>Monthly</option>
                                <option value="Flexible" <?php if ($cur_tfreq == 'Flexible') echo 'selected'; ?>>Flexible Commute</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Transport Charge (<?php echo $cur; ?>)</label>
                            <input type="number" step="0.01" min="0" name="transport_charge" value="<?php echo $cur_tcharge > 0 ? $cur_tcharge : ''; ?>" class="form-control">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Fee / Transport Change Effective From</label>
                            <input type="date" name="effective_from" value="<?php echo $eff_default; ?>"
                                min="<?php echo $eff_min; ?>" max="<?php echo $assignment->end_date; ?>" class="form-control">
                        </div>

                        <div class="form-group col-md-12" id="editTransportNoteBox">
                            <label>Agreed Arrangement (Flexible Commute)</label>
                            <textarea name="transport_note" class="form-control" rows="2"
                                placeholder="Describe the agreed transport/commute arrangement"><?php echo html_escape($cur_tnote); ?></textarea>
                        </div>

                        <div class="form-group col-md-12">
                            <label>Reason for Change</label>
                            <input type="text" name="reason" class="form-control" placeholder="Recorded in the change history (e.g. family agreed a new weekly rate)">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Update Assignment</button>
                    <a href="<?php echo base_url('nurse/billing/' . $assignment->id); ?>" class="btn btn-default">Open Billing Plan</a>

                </form>

            </div>

        </section>

        <!-- Fee / transport history -->
        <section class="panel">
            <header class="panel-heading">Fee &amp; Transport History</header>
            <div class="panel-body">
                <?php if (!empty($periods)) { ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>Frequency</th><th>Fee</th><th>Transport</th><th>Effective From</th><th>Effective To</th><th>Changed By</th><th>Note</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periods as $p) { ?>
                                <tr<?php echo !empty($p->is_superseded) ? ' class="text-muted"' : ''; ?>>
                                    <td><?php echo $p->billing_frequency; ?></td>
                                    <td><?php echo $cur . ' ' . number_format($p->rate, 2); ?></td>
                                    <td><?php echo $p->transport_charge > 0 ? $cur . ' ' . number_format($p->transport_charge, 2) . ' ' . $t_unit[$p->transport_frequency] : '—'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($p->effective_from)); ?></td>
                                    <td><?php echo !empty($p->effective_to) ? date('d M Y', strtotime($p->effective_to)) : 'Ongoing'; ?></td>
                                    <td><?php echo html_escape($p->created_by_name); ?><br><small class="text-muted"><?php echo date('d M Y H:i', strtotime($p->created_at)); ?></small></td>
                                    <td><?php echo html_escape($p->note); ?></td>
                                    <td><?php echo !empty($p->is_superseded) ? '<span class="label label-default">Superseded</span>' : '<span class="label label-success">Applies</span>'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="text-muted">No fee history recorded yet.</p>
                <?php } ?>
            </div>
        </section>

        <!-- Change log -->
        <section class="panel">
            <header class="panel-heading">Change Log</header>
            <div class="panel-body">
                <?php if (!empty($audit)) { ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>Date</th><th>Action</th><th>From</th><th>To</th><th>By</th><th>Reason</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($audit as $ev) { ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($ev->created_at)); ?></td>
                                    <td><?php echo html_escape($ev->action); ?></td>
                                    <td><?php echo html_escape((string) $ev->old_value); ?></td>
                                    <td><?php echo html_escape((string) $ev->new_value); ?></td>
                                    <td><?php echo html_escape($ev->performed_by_name); ?></td>
                                    <td><?php echo html_escape((string) $ev->reason); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <p class="text-muted">No changes recorded yet.</p>
                <?php } ?>
            </div>
        </section>

    </section>
</section>

<script>
    function toggleEditPaymentFields() {
        var term = document.getElementById("edit_payment_term").value;
        document.getElementById("editDayBox").style.display = term === "Day" ? "block" : "none";
        document.getElementById("editWeekBox").style.display = term === "Week" ? "block" : "none";
        document.getElementById("editMonthBox").style.display = term === "Month" ? "block" : "none";
    }
    document.getElementById("edit_payment_term").addEventListener("change", toggleEditPaymentFields);
    toggleEditPaymentFields();

    function toggleEditTransportNote() {
        var freq = document.getElementById("edit_transport_frequency").value;
        document.getElementById("editTransportNoteBox").style.display = (freq === "Flexible") ? "block" : "none";
    }
    document.getElementById("edit_transport_frequency").addEventListener("change", toggleEditTransportNote);
    toggleEditTransportNote();
</script>
