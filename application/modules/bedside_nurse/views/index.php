<!--sidebar end-->
<!--main content start-->

<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">

                Bedside Nurse Database

                <div class="pull-right">

                    <a href="<?php echo base_url('bedside_nurse/assignments'); ?>"
                        class="btn btn-info btn-xs">

                        <i class="fa fa-list"></i> View Assignments

                    </a>

                    <a href="<?php echo base_url('bedside_nurse/add'); ?>"
                        class="btn btn-success btn-xs">

                        <i class="fa fa-plus-circle"></i> Add Bedside Nurse

                    </a>

                </div>
                <?php if ($this->session->flashdata('error')) { ?>

                    <div class="alert alert-danger">
                        <?php echo $this->session->flashdata('error'); ?>
                    </div>

                <?php } ?>
            </header>

            <div class="panel-body">

                <div class="adv-table editable-table">

                    <table class="table table-striped table-hover table-bordered">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($nurses)) { ?>

                                <?php foreach ($nurses as $nurse) { ?>

                                    <tr>

                                        <td><?php echo $nurse->id; ?></td>

                                        <td><?php echo $nurse->name; ?></td>

                                        <td><?php echo $nurse->phone; ?></td>

                                        <td>

                                            <?php if ($nurse->status == 'Available') { ?>

                                                <span class="label label-success">
                                                    Available
                                                </span>

                                            <?php } elseif ($nurse->status == 'On Placement') { ?>

                                                <span class="label label-info">
                                                    On Placement
                                                </span>

                                            <?php } else { ?>

                                                <span class="label label-danger">
                                                    Discontinued
                                                </span>

                                            <?php } ?>

                                        </td>

                                        <td>

                                            <a href="<?php echo base_url('bedside_nurse/assign/' . $nurse->id); ?>"
                                                class="btn btn-success btn-xs">

                                                <i class="fa fa-user"></i> Assign

                                            </a>

                                            <a href="<?php echo base_url('bedside_nurse/edit/' . $nurse->id); ?>"
                                                class="btn btn-info btn-xs">

                                                <i class="fa fa-edit"></i>

                                            </a>

                                            <a href="<?php echo base_url('bedside_nurse/delete/' . $nurse->id); ?>"
                                                class="btn btn-danger btn-xs"
                                                onclick="return confirm('Are you sure you want to delete this record?')">

                                                <i class="fa fa-trash"></i>

                                            </a>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No records found
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