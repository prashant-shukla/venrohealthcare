<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Assigned Nurses
            </header>

            <div class="panel-body">

                <form method="get" action="<?php echo base_url('nurse/assignments'); ?>">

                    <div class="row" style="margin-bottom:15px;">

                        <div class="col-md-3">

                            <label>Filter Nurse</label>

                            <select name="nurse" class="form-control">

                                <option value="">All Nurses</option>

                                <?php foreach ($nurses as $n) { ?>

                                    <option value="<?php echo $n->name; ?>"
                                        <?php if ($this->input->get('nurse') == $n->name) echo 'selected'; ?>>

                                        <?php echo $n->name; ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label>Search</label>

                            <input type="text"
                                name="search"
                                value="<?php echo $this->input->get('search'); ?>"
                                class="form-control"
                                placeholder="Search nurse, patient, date">

                        </div>

                        <div class="col-md-2" style="margin-top:25px">

                            <button type="submit" class="btn btn-primary">
                                Search
                            </button>

                            <a href="<?php echo base_url('nurse/assignments'); ?>"
                                class="btn btn-default">
                                Reset
                            </a>

                        </div>

                    </div>

                </form>


                <table class="table table-bordered table-striped" id="assignmentTable">

                    <thead>

                        <tr>
                            <th>Nurse</th>
                            <th>Patient</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Fees</th>
                            <!-- <th>Week Fee</th>
                            <th>Month Fee</th> -->
                            <th>Payment Term</th>
                            <th>Transport Charge</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($assignments as $row) { ?>

                            <tr>

                                <td><?php echo $row->nurse_name; ?></td>
                                <td><?php echo $row->patient_name; ?></td>
                                <td><?php echo  date('d/m/Y', strtotime($row->start_date)); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row->end_date)); ?></td>
                                <td>
                                    <?php

                                    if (isset($row->per_day_fee) && $row->per_day_fee > 0) {

                                        echo $row->per_day_fee;
                                    } elseif (isset($row->per_week_fee) && $row->per_week_fee > 0) {

                                        echo $row->per_week_fee;
                                    } elseif (isset($row->per_month_fee) && $row->per_month_fee > 0) {

                                        echo $row->per_month_fee;
                                    } else {

                                        echo 0;
                                    }

                                    ?>
                                </td>
                                <td><?php echo $row->payment_term; ?></td>
                                <td><?php echo $row->transport_charge; ?></td>

                                <td>
                                    <a href="<?php echo base_url('nurse/editAssignment/' . $row->id); ?>"
                                        class="btn btn-info btn-xs">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);"
                                        class="btn btn-danger btn-xs"
                                        onclick="removeAssignment2(<?php echo $row->id; ?>)">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </section>

<script>
    function removeAssignment2(id) {
        if (!confirm('Remove this assignment? The historical record will be preserved.')) {
            return;
        }
        var reason = prompt('Reason for removal (recorded in the audit trail):', '');
        if (reason === null) {
            return;
        }
        window.location.href = '<?php echo base_url('nurse/deleteAssignment/'); ?>' + id + '?reason=' + encodeURIComponent(reason);
    }
</script>

    </section>
</section>