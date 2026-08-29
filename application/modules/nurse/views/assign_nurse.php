<section id="main-content">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <section class="wrapper site-min-height">

        <section class="panel">

            <?php if ($this->session->flashdata('error')) { ?>

                <div class="alert alert-danger">

                    <?php echo $this->session->flashdata('error'); ?>

                </div>

            <?php } ?>
            <header class="panel-heading">
                Assign <?php echo $nurse->name; ?> To Patient
            </header>
            <div class="panel-body">

                <form method="post" action="<?php echo base_url('nurse/saveAssign'); ?>">

                    <input type="hidden" name="nurse_id" value="<?php echo $nurse->id; ?>">

                    <div class="row">

                        <!-- Patient -->
                        <!-- <div class="col-md-12">
                            <div class="form-group">
                                <label for="patient_id">Select Patient</label>

                                <select name="patient_id" id="patient_id" class="form-control">

                                    <option value="">Select Patient</option>

                                    <?php
                                    //  foreach ($patients as $p) { 
                                        ?>

                                        <option value="<?php 
                                        // echo $p->id
                                         ?>">
                                            <?php 
                                            // echo $p->name
                                             ?>
                                        </option>

                                    <?php 
                                    //} 
                                    ?>

                                </select>

                            </div>
                        </div> -->



                        <!-- Patient -->
<!-- Patient -->
<div class="col-md-12">

    <div class="form-group">

        <label for="patient_id">

            Select Patient

        </label>

        <select name="patient_id"
            id="patient_id"
            class="form-control">

            <option value="">
                Select Patient
            </option>

            <?php foreach ($patients as $p) { ?>

                <?php

                // DEFAULT
                $color = '';
                $flag = '';

                // CHECK PATIENT ALREADY ASSIGNED
                $this->db->where('patient_id', $p->id);

                // OPTIONAL:
                // only active assignments
                $this->db->where('end_date >=', date('Y-m-d'));

                $assigned = $this->db
                    ->get('nurse_assignments')
                    ->num_rows();


                // ORANGE = CURRENTLY ON BEDSIDE NURSING
                if ($assigned > 0) {

                    $color = 'orange';
                    $flag = '🟡';
                }

                // GREEN = DISCHARGED / DECEASED
                elseif (
                    $p->status == 'Discharged' ||
                    $p->status == 'Deceased'
                ) {

                    $color = 'green';
                    $flag = '🟢';
                }

                // RED = ACTIVE
                else {

                    $color = 'red';
                    $flag = '🔴';
                }

                ?>

                <option value="<?php echo $p->id; ?>"
                    style="color:<?php echo $color; ?>;
                    font-weight:bold;">

                    <?php echo $flag . ' ' . $p->name; ?>

                </option>

            <?php } ?>

        </select>

    </div>

</div>




                     

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assignment_role">Assignment Role</label>
                                    <select name="assignment_role" id="assignment_role" class="form-control">
                                        <option value="Primary">Primary (Day) Nurse</option>
                                        <option value="Additional">Additional / Alternate Nurse</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <!-- <label for="payment_term">Payment Terms</label> -->
                                    <label for="payment_term">billing type </label>

                                    <select name="payment_term" id="payment_term" class="form-control">
                                        <option value="Day">Daily</option>
                                        <option value="Week">Weekly</option>
                                        <option value="Month">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Day -->
                            <div class="col-md-6" id="dayBox">
                                <div class="form-group">
                                    <label>Per Day amount</label>
                                    <input type="number" name="per_day_fee" id="per_day_fee" class="form-control">
                                </div>
                            </div>

                            <!-- Week -->
                            <div class="col-md-6" id="weekBox">
                                <div class="form-group">
                                    <label>Per Week amount</label>
                                    <input type="number" name="per_week_fee" id="per_week_fee" class="form-control">
                                </div>
                            </div>

                            <!-- Month -->
                            <div class="col-md-6" id="monthBox">
                                <div class="form-group">
                                    <label>Per Month amount</label>
                                    <input type="number" name="per_month_fee" id="per_month_fee" class="form-control">
                                </div>
                            </div>

                        <hr
                            <!-- Start Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control">
                            </div>


                        </div>

                        <!-- End Date -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control">
                            </div>
                        </div>

                    </div>
                    <hr>

                    <h4>Transport / Commute Charges (Optional)</h4>

                    <div class="row">

                        <!-- Transport / Commute Frequency -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transport_frequency">Commute Frequency</label>
                                <select name="transport_frequency" id="transport_frequency" class="form-control">
                                    <option value="">-- Select --</option>
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Monthly">Monthly</option>
                                    <option value="Flexible">Flexible Commute</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="transport_charge">
                                    Transport Charge
                                    <small id="transport_charge_hint" class="text-muted"></small>
                                </label>
                                <input type="number" step="0.01" name="transport_charge" id="transport_charge" class="form-control">
                            </div>
                        </div>

                        <!-- Flexible arrangement note (only for Flexible Commute) -->
                        <div class="col-md-12" id="transportNoteBox" style="display:none;">
                            <div class="form-group">
                                <label for="transport_note">Agreed Arrangement (Flexible Commute)</label>
                                <textarea name="transport_note" id="transport_note" class="form-control" rows="2"
                                    placeholder="Describe the agreed transport/commute arrangement"></textarea>
                            </div>
                        </div>

                    </div>
                    
                    </div>

                    <br>

                    <button type="submit" class="btn btn-success">
                        Assign Nurse
                    </button>

                </form>

            </div>

        </section>




        <hr>

        <h4><?php echo $nurse->name; ?> Assignment History</h4>

        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Payment Term</th>
                    <th>Fee</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (!empty($history)) { ?>

                    <?php foreach ($history as $row) { ?>

                        <tr>

                            <td><?php echo $row->patient_name; ?></td>

                            <td><?php echo  date('d/m/Y', strtotime($row->start_date)); ?></td>


                            <td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>

                            <td><?php echo $row->payment_term; ?></td>

                            <td>

                                <?php
                                if ($row->per_day_fee > 0) {
                                    echo $row->per_day_fee;
                                } elseif ($row->per_week_fee > 0) {
                                    echo $row->per_week_fee;
                                } else {
                                    echo $row->per_month_fee;
                                }
                                ?>

                            </td>
                            <td>

                                <a href="<?php echo base_url('nurse/billing/' . $row->id); ?>"
                                    class="btn btn-sm btn-primary">
                                    Billing
                                </a>

                                <a href="javascript:void(0);"
                                    class="btn btn-sm btn-danger"
                                    onclick="removeAssignment(<?php echo $row->id; ?>)">
                                    Remove
                                </a>
                            </td>

                        </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>
                        <td colspan="6" class="text-center">No History Found</td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>
    </section>


</section>



<script>
    // Soft-remove an assignment: confirm + capture reason (audit trail)
    function removeAssignment(id) {
        if (!confirm('Remove this assignment? The historical record will be preserved.')) {
            return;
        }
        var reason = prompt('Reason for removal (recorded in the audit trail):', '');
        if (reason === null) {
            return;
        }
        window.location.href = '<?php echo base_url('nurse/deleteAssignments/'); ?>' + id + '?reason=' + encodeURIComponent(reason);
    }
</script>

<script>
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    startDate.addEventListener('change', function() {

        endDate.min = this.value;

        if (endDate.value < this.value) {
            endDate.value = '';
        }

    });
</script>
<script>
    function togglePaymentFields() {

        var term = document.getElementById("payment_term").value;

        var dayBox = document.getElementById("dayBox");
        var weekBox = document.getElementById("weekBox");
        var monthBox = document.getElementById("monthBox");

        // hide all
        dayBox.style.display = "none";
        weekBox.style.display = "none";
        monthBox.style.display = "none";

        // show based on selection
        if (term === "Day") {
            dayBox.style.display = "block";
        } else if (term === "Week") {
            weekBox.style.display = "block";
        } else {
            monthBox.style.display = "block";
        }

    }

    // change event
    document.getElementById("payment_term").addEventListener("change", togglePaymentFields);

    // page load
    window.onload = togglePaymentFields;
</script>

<script>
    $(document).ready(function() {

        $('#patient_id').select2({
            placeholder: "Search Patient",
            allowClear: true,
            width: '100%'
        });

    });
</script>

<script>
    // Transport / Commute frequency behaviour
    function toggleTransportFields() {

        var freq = document.getElementById("transport_frequency").value;
        var noteBox = document.getElementById("transportNoteBox");
        var hint = document.getElementById("transport_charge_hint");

        if (freq === "Flexible") {
            // Flexible Commute: amount is entered per the agreed arrangement
            noteBox.style.display = "block";
            hint.textContent = "(agreed amount)";
        } else {
            noteBox.style.display = "none";
            if (freq === "Daily") {
                hint.textContent = "(per day)";
            } else if (freq === "Weekly") {
                hint.textContent = "(per week)";
            } else if (freq === "Monthly") {
                hint.textContent = "(per month)";
            } else {
                hint.textContent = "";
            }
        }
    }

    document.getElementById("transport_frequency")
        .addEventListener("change", toggleTransportFields);

    toggleTransportFields();
</script>