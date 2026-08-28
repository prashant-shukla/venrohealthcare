<!--sidebar end-->
<!--main content start-->
<section id="main-content">
    <section class="wrapper site-min-height">
        <!-- page start-->
        <section class="panel">
            <header class="panel-heading">
                <?php echo lang('nurse'); ?>
                <div class="col-md-4 no-print pull-right">
                    <a data-toggle="modal" href="#myModal">
                        <div class="btn-group pull-right">


                            <a href="<?php echo base_url('nurse/assignments'); ?>"
                                class="btn btn-info btn-xs" style="margin-right: 10px;">

                                <i class="fa fa-list"></i> Assigned Nurses

                            </a>

                            <a data-toggle="modal" href="#myModal"
                                class="btn btn-success btn-xs">

                                <i class="fa fa-plus-circle"></i> <?php echo lang('add_nurse'); ?>

                            </a>

                        </div>
                    </a>
                </div>
            </header>
            <div class="panel-body">
                <div class="adv-table editable-table ">
                    <div class="space15"></div>
                    <table class="table table-striped table-hover table-bordered" id="editable-sample">
                        <thead>
                            <tr>
                                <th><?php echo lang('image'); ?></th>
                                <th><?php echo lang('name'); ?></th>
                                <th><?php echo lang('email'); ?></th>
                                <th><?php echo lang('address'); ?></th>
                                <th><?php echo lang('phone'); ?></th>
                                 <th>License Expiry</th>
                                <th class="no-print"><?php echo lang('options'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <style>
                                .img_url {
                                    height: 20px;
                                    width: 20px;
                                    background-size: contain;
                                    max-height: 20px;
                                    border-radius: 100px;
                                }
                            </style>

                            <?php foreach ($nurses as $nurse) { ?>
                                <tr class="">
                                    <td style="width:10%;"><img style="width:95%;" src="<?php echo $nurse->img_url; ?>"></td>
                                    <td> <?php echo $nurse->name; ?></td>
                                    <td><?php echo $nurse->email; ?></td>
                                    <td class="center"><?php echo $nurse->address; ?></td>
                                    <td><?php echo $nurse->phone; ?></td>

        <!-- LICENSE EXPIRY ALERT -->

        <td>

            <?php

            $today = date('Y-m-d');

            $expiry_date = $nurse->license_expiry_date;

            $one_month_later = date(
                'Y-m-d',
                strtotime('+1 month')
            );

            // Expired
            if ($expiry_date < $today) {

                echo '<span style="color:red; font-weight:bold;">';

                echo date('d M Y', strtotime($expiry_date));

                echo ' (Licence Expired)';

                echo '</span>';

            }

            // Expiring Soon
            elseif (
                $expiry_date >= $today &&
                $expiry_date <= $one_month_later
            ) {

                echo '<span style="color:orange; font-weight:bold;">';

                echo date('d M Y', strtotime($expiry_date));

                echo ' (Expiring Soon)';

                echo '</span>';

            }

            // Valid
            else {

                echo '<span style="color:green; font-weight:bold;">';

                echo date('d M Y', strtotime($expiry_date));

                echo '</span>';
            }

            ?>

        </td>
                                    <td class="no-print">
                                        <a href="<?php echo base_url('nurse/assign/' . $nurse->id); ?>"
                                            class="btn btn-success btn-xs">
                                            <i class="fa fa-user"></i> Assign
                                        </a>
                                        <button type="button" class="btn btn-info btn-xs btn_width editbutton" title="<?php echo lang('edit'); ?>" data-toggle="modal" data-id="<?php echo $nurse->id; ?>"><i class="fa fa-edit"> </i></button>
                                        <a class="btn btn-info btn-xs btn_width delete_button" title="<?php echo lang('delete'); ?>" href="nurse/delete?id=<?php echo $nurse->id; ?>" onclick="return confirm('Are you sure you want to delete this item?');"><i class="fa fa-trash"></i> </a>

                                        <a href="<?php echo base_url('nurse/payment/' . $nurse->id); ?>"
                                            class="btn btn-warning btn-xs">
                                            <i class="fa fa-money"></i> Payment
                                        </a>
                                    </td>

                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!--footer start-->






<!-- Add Nurse Modal-->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"> <?php echo lang('add_nurse'); ?> </h4>
            </div>
            <div class="modal-body">
                <form role="form" action="nurse/addNew" class="clearfix" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('name'); ?></label>
                        <input type="text" class="form-control" name="name" id="exampleInputEmail1" value=''>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('email'); ?></label>
                        <input type="text" class="form-control" name="email" id="exampleInputEmail1" value='' placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('password'); ?></label>
                        <input type="password" class="form-control" name="password" id="exampleInputEmail1" placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('address'); ?></label>
                        <input type="text" class="form-control" name="address" id="exampleInputEmail1" value='' placeholder="">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('phone'); ?></label>
                        <input type="tel" class="form-control phone" name="phone" id="exampleInputEmail1" value='' placeholder="" pattern="[0-9]{10,12}" maxlength="12" minlength="10" required>
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo lang('image'); ?></label>
                        <input type="file" name="img_url">
                    </div>





                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" class="form-control" min="18" max="100" required>
                    </div>

                    <div class="form-group">
                        <label>Sex</label>

                        <select name="sex" class="form-control">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>

                    </div>


                    <div class="form-group">
                        <label>Availability</label>

                        <select name="availability" id="availability" class="form-control" onchange="toggleAvailability()">
                            <option value="Full Time">Full Time</option>
                            <option value="Specific Days">Specific Days</option>
                        </select>

                    </div>

                    <div class="form-group" id="availableDaysBox" style="display:none;">

                        <label>Available Days (If Specific)</label>

                        <div class="row">

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Monday"> Monday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Tuesday"> Tuesday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Wednesday"> Wednesday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Thursday"> Thursday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Friday"> Friday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Saturday"> Saturday</label>
                            </div>

                            <div class="col-md-4">
                                <label><input type="checkbox" name="available_days[]" value="Sunday"> Sunday</label>
                            </div>

                        </div>

                    </div>

                    <div class="form-group">
                        <label>License Expiry Date</label>
                        <input type="date" name="license_expiry_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Nurse Profile (PDF)</label>
                        <input type="file" name="nurse_profile_pdf" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Nurse License (PDF)</label>
                        <input type="file" name="nurse_license_pdf" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Status</label>

                        <select name="status" class="form-control" id="status" onchange="toggleReason()">
                            <option value="Available">Available</option>
                            <option value="On Placement" disabled>On Placement</option>
                            <option value="Discontinued">Discontinued</option>
                        </select>

                    </div>


                    <div class="form-group" id="discontinuedReasonBox" style="display:none;">

                        <label>Discontinued Reason</label>

                        <textarea name="discontinued_reason" class="form-control"></textarea>

                    </div>

                    <div class="form-group col-md-12">
                        <button type="submit" name="submit" class="btn btn-info pull-right row"><?php echo lang('submit'); ?></button>
                    </div>

                </form>

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- Add Accountant Modal-->







<!-- Edit Nurse Modal-->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"> <?php echo lang('edit_nurse'); ?> </h4>
            </div>
            <div class="modal-body" id="edit_model_body">
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
<!-- Edit Event Modal-->

<script src="common/js/codearistos.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $(".editbutton").click(function(e) {
            e.preventDefault(e);
            // Get the record's ID via attribute  
            var iid = $(this).attr('data-id');
            $('#editNurseForm').trigger("reset");
            $.ajax({
                url: 'nurse/editNurseByJason?id=' + iid,
                method: 'GET',
                data: '',
                dataType: 'json',
            }).success(function(response) {
                $('#edit_model_body').html(response.html);
                $('#myModal2').modal('show');
            });

        });
    });
</script>
<script>
    $(document).ready(function() {
        var table = $('#editable-sample').DataTable({
            responsive: true,

            dom: "<'row'<'col-sm-3'l><'col-sm-5 text-center'B><'col-sm-4'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pdfHtml5',
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [1, 2, 3, 4],
                    }
                },
            ],

            aLengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            iDisplayLength: -1,
            "order": [
                [0, "desc"]
            ],

            "language": {
                "lengthMenu": "_MENU_",
                search: "_INPUT_",
                "url": "common/assets/DataTables/languages/<?php echo $this->language; ?>.json"
            }
        });
        table.buttons().container().appendTo('.custom_buttons');
    });
</script>
<script>
    $(document).ready(function() {
        $(".flashmessage").delay(3000).fadeOut(100);
    });
</script>
<script>
    function toggleAvailability() {

        var availability = document.getElementById("availability").value;
        var daysBox = document.getElementById("availableDaysBox");

        if (availability === "Specific Days") {

            daysBox.style.display = "block";

        } else {

            daysBox.style.display = "none";

            var checkboxes = daysBox.querySelectorAll("input[type='checkbox']");
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });

        }

    }
</script>

<script>
    function toggleReason() {

        var status = document.getElementById("status").value;
        var reasonBox = document.getElementById("discontinuedReasonBox");

        if (status === "Discontinued") {

            reasonBox.style.display = "block";

        } else {

            reasonBox.style.display = "none";

        }

    }
</script>
<script>
    document.getElementsByClassName("phone")[0].addEventListener("input", function() {

        this.value = this.value.replace(/[^0-9]/g, '');

    });
</script>
<script>
    document.querySelector('input[name="age"]').addEventListener('input', function() {



        if (this.value > 100) {
            this.value = 100;
        }

    });
</script>
<script>

$(document).ready(function () {

    $(".editbutton").click(function (e) {

        e.preventDefault();

        var iid = $(this).attr('data-id');

        $.ajax({

            url: 'nurse/editNurseByJason?id=' + iid,

            method: 'GET',

            dataType: 'json',

            success: function (response) {

                $('#edit_model_body').html(response.html);

                $('#myModal2').modal('show');


                // IMPORTANT
                setTimeout(function () {

                    toggleDiscontinuedReason();

                }, 200);

            }

        });

    });

});



function toggleDiscontinuedReason() {

    var status = $('#status').val();

    if (status === 'Discontinued') {

        $('#discontinuedReasonBox').show();

        $('#discontinued_reason').prop('required', true);

    } else {

        $('#discontinuedReasonBox').hide();

        $('#discontinued_reason').prop('required', false);

        $('#discontinued_reason').val('');

    }

}


// CHANGE EVENT
$(document).on('change', '#status', function () {

    toggleDiscontinuedReason();

});

</script>