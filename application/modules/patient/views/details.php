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

                <aside class="profile-info col-lg-9">
                    <section class="panel">
                        <div class="bio-graph-heading">
                            <?php echo lang('doctor'); ?> : <?php echo $this->doctor_model->getDoctorById($patient->doctor)->name; ?>
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
                                        <?php
                                        echo $this->doctor_model->getDoctorById($patient->doctor)->name;
                                        ?>
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
                                            <th>Nurse Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($care_nurses as $cn) { ?>
                                            <tr>
                                                <td><?php echo $cn->nurse_name; ?></td>
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
                                                <td><?php echo $cn->nurse_status; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } else { ?>
                                <p class="text-muted">No bedside nurse currently assigned.</p>
                            <?php } ?>

                            <h4>Assigned Doctor (Clinical)</h4>
                            <?php
                            $care_doctor = !empty($patient->doctor)
                                ? $this->doctor_model->getDoctorById($patient->doctor)
                                : null;
                            ?>
                            <?php if (!empty($care_doctor)) { ?>
                                <p><strong><?php echo $care_doctor->name; ?></strong></p>
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

                            <?php if ($this->session->flashdata('feedback')) { ?>
                                <div class="alert alert-success"><?php echo $this->session->flashdata('feedback'); ?></div>
                            <?php } ?>

                            <p class="text-muted" style="font-size:12px;">
                                Authorised assigned nurses and doctors can record updates on the patient's
                                recovery / progress. Previous entries remain part of the patient's history.
                            </p>

                            <!-- Add entry -->
                            <form method="post" action="<?php echo base_url('patient/addRecoveryJournal'); ?>">
                                <input type="hidden" name="patient_id" value="<?php echo $patient->id; ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Date / Time</label>
                                            <input type="datetime-local" name="entry_datetime" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <select name="role" class="form-control">
                                                <option value="Nurse">Nurse</option>
                                                <option value="Doctor">Doctor</option>
                                            </select>
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
                                                <td><?php echo $j->entered_by_name; ?></td>
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

                </aside>
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!--footer start -->
