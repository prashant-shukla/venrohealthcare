<!-- sidebar end-->
<!--main content start-->
<section id="main-content">
    <section class="wrapper site-min-height">
        <section class="">
            <!-- page start-->
            <div class="row">
                <aside class="profile-nav col-lg-3">
                    <section class="panel">
                        <div class="user-heading round">
                            <a>
                                <img src="<?php echo $patient->img_url ?>" alt="">
                            </a>
                            <h1><?php echo $patient->name ?></h1>
                         <!--   <p><?php echo $patient->email ?></p> -->
                        </div>
                    </section>
                </aside>

                <style>

                    .bio-row {
                        width: 50%;
                        float: none;
                        margin-bottom: 10px;
                        padding: 15px;
                        border: 2px solid #f1f1f1;
                    }

                    .bio-graph-info {
                        color: #89817e;
                        padding: 23px;
                    }

                </style>

                <?php
                $care_doctor = !empty($patient->doctor) ? $this->doctor_model->getDoctorById($patient->doctor) : null;
                $care_doctor_name = !empty($care_doctor) ? $care_doctor->name : '';
                ?>
                <aside class="profile-info col-lg-9">
                    <?php if (isset($patient->is_active) && $patient->is_active == 0) { ?>
                        <div class="alert alert-warning">
                            <strong>Archived patient.</strong> Archived on <?php echo date('d M Y', strtotime($patient->deleted_at)); ?>
                            <?php echo !empty($patient->deleted_reason) ? ' &mdash; ' . html_escape($patient->deleted_reason) : ''; ?>.
                            The record and its history are kept for reference.
                        </div>
                    <?php } ?>
                    <section class="panel">
                        <div class="bio-graph-heading">
                            <?php echo lang('doctor'); ?> : <?php echo html_escape($care_doctor_name); ?>
                        </div>
                        <div class="bio-graph-info">
                            <h1>Bio Graph</h1>
                            <div class="row">
                                <div class="bio-row">
                                    <p><span><?php echo lang('name'); ?> </span>: <?php echo $patient->name; ?></p>
                                </div>

                                <div class="bio-row">
                                    <p><span><?php echo lang('email'); ?> </span>: <?php echo $patient->email; ?></p>
                                </div>

                                <div class="bio-row">
                                    <p><span><?php echo lang('address'); ?></span>: <?php echo $patient->address; ?></p>
                                </div>
                                <div class="bio-row">
                                    <p><span><?php echo lang('phone'); ?> </span>: <?php echo $patient->phone; ?></p>
                                </div>
                                <div class="bio-row">
                                    <p><span><?php echo lang('sex'); ?> </span>: <?php echo $patient->sex; ?></p>
                                </div>

                                <div class="bio-row">
                                    <p><span><?php echo lang('birth_date'); ?> </span>: <?php echo $patient->birthdate; ?></p>
                                </div>
                                <div class="bio-row">
                                    <p><span><?php echo lang('blood_group'); ?> </span>: <?php echo $patient->bloodgroup; ?></p>
                                </div>
                                <div class="bio-row">
                                    <p><span><?php echo lang('age'); ?></span>: 
                                        <?php
                                        $birthDate = strtotime($patient->birthdate);
                                        $birthDate = date('m/d/Y', $birthDate);
                                        $birthDate = explode("/", $birthDate);
                                        $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md") ? ((date("Y") - $birthDate[2]) - 1) : (date("Y") - $birthDate[2]));
                                        echo $age . ' Year(s)';
                                        ?>
                                    </p>
                                </div>

                                <div class="bio-row">
                                    <p>
                                        <span><?php echo lang('doctor'); ?> </span>:
                                        <?php echo html_escape($care_doctor_name); ?>
                                    </p>
                                </div>
                                <div class="bio-row">
                                    <p><span><?php echo lang('patient_id'); ?> </span>: <?php echo $patient->id; ?></p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ================= ASSIGNED CARE TEAM ================= -->
                    <section class="panel">
                        <header class="panel-heading">
                            Assigned Care Team
                        </header>
                        <div class="panel-body">

                            <h4 style="margin-top:0;">Bedside Nurse(s)</h4>
                            <?php if (!empty($care_nurses)) { ?>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nurse</th>
                                            <th>Role</th>
                                            <th>Period</th>
                                            <th>Active Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($care_nurses as $cn) { ?>
                                            <tr>
                                                <td><?php echo html_escape($cn->nurse_name); ?></td>
                                                <td>
                                                    <?php if ($cn->assignment_role == 'Additional') { ?>
                                                        <span class="label label-info">Additional / Alternate</span>
                                                    <?php } else { ?>
                                                        <span class="label label-success">Primary (Day)</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php echo !empty($cn->start_date) ? date('d M Y', strtotime($cn->start_date)) : '—'; ?>
                                                    &ndash;
                                                    <?php echo !empty($cn->end_date) ? date('d M Y', strtotime($cn->end_date)) : 'Ongoing'; ?>
                                                </td>
                                                <td>
                                                    <?php if ($cn->start_date > date('Y-m-d')) { ?>
                                                        <span class="label label-default">Upcoming</span>
                                                    <?php } else { ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p class="text-muted">No bedside nurse currently assigned.</p>
                            <?php } ?>

                            <h4>Assigned Doctor (Clinical)</h4>
                            <?php if (!empty($care_doctor)) { ?>
                                <p><strong><?php echo html_escape($care_doctor->name); ?></strong></p>
                            <?php } else { ?>
                                <p class="text-muted">No doctor assigned.</p>
                            <?php } ?>
                            <p class="text-muted" style="font-size:12px;">
                                <em>Doctors are assigned for clinical / care participation only and are
                                <strong>not billable</strong> through the Bedside Nursing Module.</em>
                            </p>

                        </div>
                    </section>
                    <!-- ================= /ASSIGNED CARE TEAM ================= -->

                    <!-- ================= PATIENT RECOVERY JOURNAL ================= -->
                    <section class="panel">
                        <header class="panel-heading">
                            Patient Recovery Journal
                        </header>
                        <div class="panel-body">

                            <?php if ($this->session->flashdata('journal_success')) { ?>
                                <div class="alert alert-success"><?php echo html_escape($this->session->flashdata('journal_success')); ?></div>
                            <?php } ?>
                            <?php if ($this->session->flashdata('journal_error')) { ?>
                                <div class="alert alert-danger"><?php echo html_escape($this->session->flashdata('journal_error')); ?></div>
                            <?php } ?>

                            <p class="text-muted" style="font-size:12px;">
                                Authorised assigned nurses and doctors can record updates on the patient's
                                recovery / progress. Previous entries remain part of the patient's history.
                            </p>

                            <?php if ($journal_access['allowed']) { ?>
                            <!-- Add entry -->
                            <form method="post" action="<?php echo base_url('patient/addRecoveryJournal'); ?>">
                                <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date / Time</label>
                                            <input type="datetime-local" name="entry_datetime" class="form-control" value="<?php echo date('Y-m-d\TH:i'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <?php if ($journal_access['role'] === null) { ?>
                                                <select name="role" class="form-control">
                                                    <option value="Nurse">Nurse</option>
                                                    <option value="Doctor">Doctor</option>
                                                </select>
                                            <?php } else { ?>
                                                <input type="text" class="form-control" value="<?php echo $journal_access['role']; ?>" disabled>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Update / Notes</label>
                                            <textarea name="notes" class="form-control" rows="2" required
                                                placeholder="Recovery / progress update"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-success">Add Journal Entry</button>
                                    </div>
                                </div>
                            </form>
                            <?php } else { ?>
                                <p class="text-muted"><em>Only nurses assigned to this patient, the assigned doctor or an administrator can add entries.</em></p>
                            <?php } ?>

                            <hr>

                            <!-- Entries -->
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date / Time</th>
                                        <th>Update / Notes</th>
                                        <th>Entered By</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recovery_journal)) { ?>
                                        <?php foreach ($recovery_journal as $j) { ?>
                                            <tr>
                                                <td><?php echo date('d M Y H:i', strtotime($j->entry_datetime)); ?></td>
                                                <td><?php echo nl2br(htmlspecialchars($j->notes)); ?></td>
                                                <td><?php echo html_escape($j->entered_by_name); ?></td>
                                                <td>
                                                    <?php if ($j->role == 'Doctor') { ?>
                                                        <span class="label label-primary">Doctor</span>
                                                    <?php } else { ?>
                                                        <span class="label label-info">Nurse</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr><td colspan="4" class="text-center">No journal entries yet.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                    </section>
                    <!-- ================= /PATIENT RECOVERY JOURNAL ================= -->

                    <!-- ================= FEEDBACK ================= -->
                    <section class="panel">
                        <header class="panel-heading">
                            Feedback (Nurse &amp; Service)
                            <?php if ($this->ion_auth->in_group(array('admin', 'Receptionist'))) { ?>
                                <a href="<?php echo base_url('feedback?patient_id=' . $patient->id); ?>" class="btn btn-xs btn-warning pull-right">
                                    <i class="fa fa-comments"></i> Record / request feedback
                                </a>
                            <?php } ?>
                        </header>
                        <div class="panel-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Nurse</th>
                                        <th>Rating</th>
                                        <th>Comments</th>
                                        <th>Source</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($feedback)) { ?>
                                        <?php foreach ($feedback as $f) { ?>
                                            <tr>
                                                <td><?php echo $f->feedback_type == 'Business' ? 'Business / Service' : 'Nurse'; ?></td>
                                                <td><?php echo html_escape($f->nurse_name); ?></td>
                                                <td><?php echo $f->rating ? (int) $f->rating . '/5' : '—'; ?></td>
                                                <td><?php echo $f->status == 'Pending' ? '<em class="text-muted">Awaiting customer response</em>' : nl2br(html_escape((string) $f->comments)); ?></td>
                                                <td>
                                                    <?php if ($f->source == 'Customer') { ?>
                                                        <span class="label label-success">Submitted by customer</span>
                                                    <?php } else { ?>
                                                        <span class="label label-default">Manually recorded</span>
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime(!empty($f->submitted_at) ? $f->submitted_at : $f->created_at)); ?></td>
                                            </tr>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <tr><td colspan="6" class="text-center">No feedback yet.</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <!-- ================= /FEEDBACK ================= -->

                </aside>
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!--footer start -->
