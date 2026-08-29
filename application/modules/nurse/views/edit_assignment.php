<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Edit Nurse Assignment
            </header>

            <div class="panel-body col-md-12">

                <form method="post" action="<?php echo base_url('nurse/updateAssignment'); ?>">

                    <input type="hidden" name="id" value="<?php echo $assignment->id; ?>">

                    <div class="form-group col-md-12">
                        <label>Select Patient</label>

                        <select name="patient_id" class="form-control">

                            <?php foreach ($patients as $p) { ?>

                                <option value="<?php echo $p->id; ?>"
                                    <?php if ($assignment->patient_id == $p->id) echo 'selected'; ?>>

                                    <?php echo $p->name; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="form-group col-md-6">
                        <label>Start Date</label>

                        <input type="date" name="start_date"
                            value="<?php echo $assignment->start_date; ?>"
                            class="form-control">

                    </div>

                    <div class="form-group col-md-6">
                        <label>End Date</label>

                        <input type="date" name="end_date"
                            value="<?php echo $assignment->end_date; ?>"
                            class="form-control">

                    </div>


                    <div class="form-group col-md-6">
                        <label>Assignment Role</label>
                        <select name="assignment_role" class="form-control">
                            <option value="Primary"    <?php if ($assignment->assignment_role == 'Primary')    echo 'selected'; ?>>Primary (Day) Nurse</option>
                            <option value="Additional" <?php if ($assignment->assignment_role == 'Additional') echo 'selected'; ?>>Additional / Alternate Nurse</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Payment Term</label>

                        <select name="payment_term" id="edit_payment_term" class="form-control">

                            <option value="Day" <?php if ($assignment->payment_term == 'Day') echo 'selected'; ?>>
                                Daily
                            </option>

                            <option value="Week" <?php if ($assignment->payment_term == 'Week') echo 'selected'; ?>>
                                Weekly
                            </option>

                            <option value="Month" <?php if ($assignment->payment_term == 'Month') echo 'selected'; ?>>
                                Monthly
                            </option>

                        </select>
                    </div>


                    <div class="form-group col-md-6" id="editDayBox">
                        <label>Per Day Fee</label>
                        <input type="number" name="per_day_fee"
                            value="<?php echo $assignment->per_day_fee; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6" id="editWeekBox">
                        <label>Per Week Fee</label>
                        <input type="number" name="per_week_fee"
                            value="<?php echo $assignment->per_week_fee; ?>" class="form-control">
                    </div>

                    <div class="form-group col-md-6" id="editMonthBox">
                        <label>Per Month Fee</label>
                        <input type="number" name="per_month_fee"
                            value="<?php echo $assignment->per_month_fee; ?>" class="form-control">
                    </div>



                    <div class="form-group col-md-6">
                        <label>Commute Frequency</label>
                        <select name="transport_frequency" id="edit_transport_frequency" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="Daily"    <?php if ($assignment->transport_frequency == 'Daily')    echo 'selected'; ?>>Daily</option>
                            <option value="Weekly"   <?php if ($assignment->transport_frequency == 'Weekly')   echo 'selected'; ?>>Weekly</option>
                            <option value="Monthly"  <?php if ($assignment->transport_frequency == 'Monthly')  echo 'selected'; ?>>Monthly</option>
                            <option value="Flexible" <?php if ($assignment->transport_frequency == 'Flexible') echo 'selected'; ?>>Flexible Commute</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Transport Charge</label>

                        <input type="number" step="0.01"
                            name="transport_charge"
                            value="<?php echo $assignment->transport_charge; ?>"
                            class="form-control">

                    </div>

                    <div class="form-group col-md-12" id="editTransportNoteBox"
                        style="<?php if ($assignment->transport_frequency != 'Flexible') echo 'display:none;'; ?>">
                        <label>Agreed Arrangement (Flexible Commute)</label>
                        <textarea name="transport_note" class="form-control" rows="2"
                            placeholder="Describe the agreed transport/commute arrangement"><?php echo isset($assignment->transport_note) ? html_escape($assignment->transport_note) : ''; ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        Update Assignment
                    </button>

                </form>

            </div>

        </section>

    </section>
</section>

<script>
    function toggleEditPaymentFields() {

        var term = document.getElementById("edit_payment_term").value;

        // hide all
        document.getElementById("editDayBox").style.display = "none";
        document.getElementById("editWeekBox").style.display = "none";
        document.getElementById("editMonthBox").style.display = "none";

        // show selected
        if (term === "Day") {
            document.getElementById("editDayBox").style.display = "block";
        } else if (term === "Week") {
            document.getElementById("editWeekBox").style.display = "block";
        } else {
            document.getElementById("editMonthBox").style.display = "block";
        }
    }

    // change event
    document.getElementById("edit_payment_term").addEventListener("change", toggleEditPaymentFields);

    // page load
    toggleEditPaymentFields();

    // Transport / Commute frequency: show note only for Flexible
    function toggleEditTransportNote() {
        var freq = document.getElementById("edit_transport_frequency").value;
        document.getElementById("editTransportNoteBox").style.display =
            (freq === "Flexible") ? "block" : "none";
    }
    document.getElementById("edit_transport_frequency")
        .addEventListener("change", toggleEditTransportNote);
    toggleEditTransportNote();
</script>