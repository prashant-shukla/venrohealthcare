<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Nurse Patient Assignments
            </header>
            <div class="row" style="margin-bottom:15px">

                <div class="col-md-6">
                    <h4></h4>
                </div>

                <div class="col-md-6 text-right">

                    <form method="get" style="display:inline-block; width:220px; margin-right: 10px;">

                        <select name="nurse_name" class="form-control" onchange="this.form.submit()">
                            <option value="">All Nurses</option>

                            <?php foreach ($nurses as $n) { ?>

                                <option value="<?php echo $n->name ?>"
                                    <?php if ($this->input->get('nurse_name') == $n->name) echo "selected"; ?>>

                                    <?php echo $n->name ?>

                                </option>

                            <?php } ?>

                        </select>

                    </form>

                </div>

            </div>
            <div class="panel-body">

                <div class="table-responsive">

                    <table class="table table-striped table-hover table-bordered">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nurse</th>
                                <th>Patient</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($assignments)) { ?>

                                <?php foreach ($assignments as $a) { ?>

                                    <tr>

                                        <td><?php echo $a->id; ?></td>

                                        <td><?php echo $a->nurse_name; ?></td>

                                        <td><?php echo $a->patient_name; ?></td>

                                        <td><?php echo $a->start_date; ?></td>

                                        <td><?php echo $a->end_date ? $a->end_date : '-'; ?></td>

                                        <td>

                                            <?php if ($a->status == 'Active') { ?>

                                                <span class="label label-success">Active</span>

                                            <?php } else { ?>

                                                <span class="label label-default">Completed</span>

                                            <?php } ?>

                                        </td>
                                        <td>

                                            <a href="<?php echo base_url('bedside_nurse/editAssignment/' . $a->id); ?>"
                                                class="btn btn-info btn-xs">

                                                <i class="fa fa-edit"></i>

                                            </a>

                                            <a href="<?php echo base_url('bedside_nurse/deleteAssignment/' . $a->id); ?>"
                                                class="btn btn-danger btn-xs"
                                                onclick="return confirm('Are you sure you want to delete this assignment?')">

                                                <i class="fa fa-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>
                                    <td colspan="7" class="text-center">
                                        No Assignments Found
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </section>
</section>