<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">
            <header class="panel-heading">
                Nurse Record &mdash; <?php echo $nurse->name; ?>
                <?php if (isset($nurse->is_active) && $nurse->is_active == 0) { ?>
                    <span class="label label-danger">Deactivated / Archived</span>
                <?php } ?>
                <a href="<?php echo base_url('nurse'); ?>" class="btn btn-default btn-xs pull-right">Back</a>
            </header>
            <div class="panel-body">
                <p class="text-muted" style="font-size:12px;">
                    This record remains available even when the nurse is no longer active,
                    has been discontinued, or is no longer on placement.
                </p>

                <div class="row">
                    <div class="col-md-3">
                        <?php if (!empty($nurse->img_url)) { ?>
                            <img src="<?php echo base_url($nurse->img_url); ?>" style="max-width:100%;">
                        <?php } ?>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr><th style="width:30%;">Name</th><td><?php echo $nurse->name; ?></td></tr>
                            <tr><th>Email</th><td><?php echo $nurse->email; ?></td></tr>
                            <tr><th>Phone</th><td><?php echo $nurse->phone; ?></td></tr>
                            <tr><th>Address</th><td><?php echo $nurse->address; ?></td></tr>
                            <tr><th>Age / Sex</th><td><?php echo $nurse->age; ?> / <?php echo $nurse->sex; ?></td></tr>
                            <tr><th>Availability</th><td><?php echo $nurse->availability; ?>
                                <?php echo !empty($nurse->available_days) ? '(' . $nurse->available_days . ')' : ''; ?></td></tr>
                            <tr><th>Current Status</th><td><?php echo $nurse->status; ?></td></tr>
                            <tr><th>Licence Expiry</th>
                                <td><?php echo !empty($nurse->license_expiry_date) && $nurse->license_expiry_date != '0000-00-00'
                                    ? date('d M Y', strtotime($nurse->license_expiry_date)) : 'Not set'; ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Certificates / Licences -->
        <section class="panel">
            <header class="panel-heading">Certificates / Licences</header>
            <div class="panel-body">
                <?php if (!empty($nurse->nurse_license_pdf)) { ?>
                    <a href="<?php echo base_url($nurse->nurse_license_pdf); ?>" target="_blank" class="btn btn-primary btn-sm">
                        <i class="fa fa-certificate"></i> View Licence
                    </a>
                <?php } ?>
                <?php if (!empty($nurse->nurse_profile_pdf)) { ?>
                    <a href="<?php echo base_url($nurse->nurse_profile_pdf); ?>" target="_blank" class="btn btn-default btn-sm">
                        <i class="fa fa-file-pdf-o"></i> View Profile Document
                    </a>
                <?php } ?>
                <?php if (empty($nurse->nurse_license_pdf) && empty($nurse->nurse_profile_pdf)) { ?>
                    <p class="text-muted">No certificates on file.</p>
                <?php } ?>
            </div>
        </section>

        <!-- Previous & current patient assignments -->
        <section class="panel">
            <header class="panel-heading">Patient Assignments (History)</header>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr><th>Patient</th><th>Role</th><th>Start</th><th>End</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($assignments)) { ?>
                            <?php foreach ($assignments as $a) { ?>
                                <tr>
                                    <td><?php echo $a->patient_name; ?></td>
                                    <td><?php echo isset($a->assignment_role) ? $a->assignment_role : '—'; ?></td>
                                    <td><?php echo !empty($a->start_date) ? date('d M Y', strtotime($a->start_date)) : '—'; ?></td>
                                    <td><?php echo !empty($a->end_date) ? date('d M Y', strtotime($a->end_date)) : 'Ongoing'; ?></td>
                                    <td>
                                        <?php if (isset($a->is_active) && $a->is_active == 0) { ?>
                                            <span class="label label-default">Removed</span>
                                        <?php } else { ?>
                                            <span class="label label-success">Active</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="5" class="text-center">No assignments.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Professional fee / payment records -->
        <section class="panel">
            <header class="panel-heading">Professional Fee / Payment Records</header>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Date</th><th>Amount</th><th>Note</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($payments)) { ?>
                            <?php foreach ($payments as $p) { ?>
                                <tr>
                                    <td><?php echo !empty($p->payment_date) ? date('d M Y', strtotime($p->payment_date)) : '—'; ?></td>
                                    <td><?php echo $this->currency; ?> <?php echo number_format($p->amount, 2); ?></td>
                                    <td><?php echo html_escape($p->note); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="3" class="text-center">No payment records.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Status history & audit activity -->
        <section class="panel">
            <header class="panel-heading">Status History &amp; Activity</header>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr><th>When</th><th>Action</th><th>Change</th><th>Reason</th><th>By</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($audit)) { ?>
                            <?php foreach ($audit as $ev) { ?>
                                <tr>
                                    <td><?php echo date('d M Y H:i', strtotime($ev->created_at)); ?></td>
                                    <td><?php echo $ev->action; ?></td>
                                    <td>
                                        <?php if ($ev->old_value !== null || $ev->new_value !== null) {
                                            echo htmlspecialchars((string)$ev->old_value) . ' &rarr; ' . htmlspecialchars((string)$ev->new_value);
                                        } else { echo '—'; } ?>
                                    </td>
                                    <td><?php echo html_escape($ev->reason); ?></td>
                                    <td><?php echo html_escape($ev->performed_by_name); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="5" class="text-center">No recorded activity.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

    </section>
</section>
