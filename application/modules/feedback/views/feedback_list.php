<section id="main-content">
    <section class="wrapper site-min-height">

        <?php if ($this->session->flashdata('feedback_msg')) { ?>
            <div class="alert alert-success"><?php echo $this->session->flashdata('feedback_msg'); ?></div>
        <?php } ?>

        <?php if ($this->session->flashdata('feedback_link')) { ?>
            <div class="alert alert-info">
                <strong>Feedback request link generated.</strong> Send this link to the customer/patient:<br>
                <input type="text" class="form-control" readonly
                    value="<?php echo $this->session->flashdata('feedback_link'); ?>"
                    onclick="this.select();">
            </div>
        <?php } ?>

        <div class="row">
            <!-- Manual entry -->
            <div class="col-md-6">
                <section class="panel">
                    <header class="panel-heading">Record Feedback (Manual Entry)</header>
                    <div class="panel-body">
                        <p class="text-muted" style="font-size:12px;">
                            For feedback received from a patient/customer and entered by staff.
                        </p>
                        <form method="post" action="<?php echo base_url('feedback/manualAdd'); ?>">
                            <div class="form-group">
                                <label>Feedback Type</label>
                                <select name="feedback_type" class="form-control" required>
                                    <option value="Nurse">Nurse Feedback</option>
                                    <option value="Business">Business / Service Feedback</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nurse (for nurse feedback)</label>
                                <select name="nurse_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php foreach ($nurses as $n) { ?>
                                        <option value="<?php echo $n->id; ?>"><?php echo $n->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Patient / Customer</label>
                                <select name="patient_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php foreach ($patients as $p) { ?>
                                        <option value="<?php echo $p->id; ?>"><?php echo $p->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Customer Name (if not a listed patient)</label>
                                <input type="text" name="customer_name" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Rating (1-5)</label>
                                <select name="rating" class="form-control">
                                    <option value="">-- N/A --</option>
                                    <?php for ($i = 5; $i >= 1; $i--) { ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Comments</label>
                                <textarea name="comments" class="form-control" rows="2" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Save Feedback</button>
                        </form>
                    </div>
                </section>
            </div>

            <!-- Generate request link -->
            <div class="col-md-6">
                <section class="panel">
                    <header class="panel-heading">Send Feedback Request Link</header>
                    <div class="panel-body">
                        <p class="text-muted" style="font-size:12px;">
                            Generate a link to send to the customer/patient. They complete the feedback
                            externally and it appears here, marked as submitted by the customer.
                        </p>
                        <form method="post" action="<?php echo base_url('feedback/generateLink'); ?>">
                            <div class="form-group">
                                <label>Feedback Type</label>
                                <select name="feedback_type" class="form-control" required>
                                    <option value="Nurse">Nurse Feedback</option>
                                    <option value="Business">Business / Service Feedback</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nurse (for nurse feedback)</label>
                                <select name="nurse_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php foreach ($nurses as $n) { ?>
                                        <option value="<?php echo $n->id; ?>"><?php echo $n->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Patient / Customer</label>
                                <select name="patient_id" class="form-control">
                                    <option value="">-- None --</option>
                                    <?php foreach ($patients as $p) { ?>
                                        <option value="<?php echo $p->id; ?>"><?php echo $p->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Generate Link</button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <!-- Feedback list -->
        <section class="panel">
            <header class="panel-heading">All Feedback</header>
            <div class="panel-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Nurse</th>
                            <th>Patient / Customer</th>
                            <th>Rating</th>
                            <th>Comments</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Recorded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($feedback)) { ?>
                            <?php foreach ($feedback as $f) { ?>
                                <tr>
                                    <td><?php echo $f->feedback_type; ?></td>
                                    <td><?php echo $f->nurse_name; ?></td>
                                    <td><?php echo !empty($f->patient_name) ? html_escape($f->patient_name) : html_escape($f->customer_name); ?></td>
                                    <td><?php echo $f->rating ? $f->rating . '/5' : '—'; ?></td>
                                    <td><?php echo htmlspecialchars((string)$f->comments); ?></td>
                                    <td>
                                        <?php if ($f->source == 'Customer') { ?>
                                            <span class="label label-success">Customer (via link)</span>
                                        <?php } else { ?>
                                            <span class="label label-default">Manually recorded</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($f->status == 'Pending') { ?>
                                            <span class="label label-warning">Pending</span>
                                        <?php } else { ?>
                                            <span class="label label-info">Submitted</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($f->submitted_at) ? date('d M Y H:i', strtotime($f->submitted_at)) : date('d M Y H:i', strtotime($f->created_at)); ?>
                                        <?php echo !empty($f->recorded_by_name) ? '<br><small>by ' . $f->recorded_by_name . '</small>' : ''; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="8" class="text-center">No feedback yet.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

    </section>
</section>
