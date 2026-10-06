<!--sidebar end-->
<!--main content start-->
<section id="main-content">
    <section class="wrapper site-min-height">
        <!-- page start-->
        <?php
        // ---- summary stats for the header tiles ----
        $nl_total = is_array($nurses) ? count($nurses) : 0;
        $nl_active = 0; $nl_onplace = 0; $nl_lic_alert = 0;
        $nl_today = date('Y-m-d');
        $nl_three = date('Y-m-d', strtotime('+3 months'));
        foreach ($nurses as $__n) {
            if (!isset($__n->is_active) || $__n->is_active == 1) $nl_active++;
            if (isset($__n->status) && $__n->status == 'On Placement') $nl_onplace++;
            $__e = $__n->license_expiry_date;
            if (!empty($__e) && $__e != '0000-00-00' && $__e <= $nl_three) $nl_lic_alert++;
        }
        ?>

        <style>
        .nl-wrap { padding:4px 2px 30px; }
        .nl-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
        .nl-title { font-size:22px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:10px; margin:0; }
        .nl-title .ic { width:38px; height:38px; border-radius:10px; background:#e8f1fe; color:#2c7be5; display:flex; align-items:center; justify-content:center; font-size:18px; }
        .nl-actions { display:flex; gap:9px; flex-wrap:wrap; }
        .nl-btn { display:inline-flex; align-items:center; gap:7px; padding:9px 15px; border-radius:9px; font-size:13px; font-weight:600;
            border:1px solid transparent; cursor:pointer; transition:.15s; text-decoration:none; }
        .nl-btn:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.12); text-decoration:none; }
        .nl-btn.primary { background:#2c7be5; color:#fff; }
        .nl-btn.ghost   { background:#fff; color:#475569; border-color:#e2e8f0; }
        .nl-btn.amber   { background:#fff; color:#e8830c; border-color:#f6d9b8; }

        .nl-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }
        @media (max-width:900px){ .nl-stats { grid-template-columns:repeat(2,1fr); } }
        .nl-stat { background:#fff; border:1px solid #eef1f5; border-radius:12px; padding:16px 18px;
            box-shadow:0 1px 3px rgba(16,24,40,.06); display:flex; align-items:center; gap:14px; }
        .nl-stat-ic { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:18px; flex:0 0 auto; }
        .nl-stat-ic.blue { background:#e8f1fe; color:#2c7be5; } .nl-stat-ic.green { background:#e6f6ee; color:#1f9d55; }
        .nl-stat-ic.violet { background:#eef0fe; color:#5b63d3; } .nl-stat-ic.amber { background:#fdf1e3; color:#e8830c; }
        .nl-stat-v { font-size:22px; font-weight:700; color:#1e293b; line-height:1; }
        .nl-stat-l { font-size:12px; color:#94a3b8; margin-top:3px; }

        /* Card + DataTable restyle */
        .nl-card { background:#fff; border:1px solid #eef1f5; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.06); padding:8px 16px 16px; }
        #editable-sample { border:none !important; margin-top:6px; }
        #editable-sample.table-bordered > thead > tr > th,
        #editable-sample.table-bordered > tbody > tr > td { border:none; border-bottom:1px solid #f1f4f8; }
        #editable-sample > thead > tr > th { text-transform:uppercase; font-size:11px; letter-spacing:.4px; color:#94a3b8;
            font-weight:700; padding:12px 14px; border-bottom:2px solid #eef1f5 !important; background:transparent; }
        #editable-sample > tbody > tr > td { padding:12px 14px; vertical-align:middle; font-size:13px; color:#334155; }
        #editable-sample.table-striped > tbody > tr:nth-of-type(odd) { background:#fff; }
        #editable-sample > tbody > tr:hover td { background:#fafbfe; }

        .nl-ava { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #eef1f5; background:#f1f5fb; }
        .nl-ava-txt { width:40px; height:40px; border-radius:50%; background:#e8f1fe; color:#2c7be5; font-weight:700; font-size:14px;
            display:flex; align-items:center; justify-content:center; border:2px solid #dbe9fd; }
        .nl-name { font-weight:600; color:#1e293b; }
        .nl-muted { color:#64748b; }

        .nl-pill { font-size:11.5px; font-weight:700; padding:4px 11px; border-radius:20px; display:inline-flex; align-items:center; gap:5px; white-space:nowrap; }
        .nl-pill.ok   { background:#e6f6ee; color:#1f9d55; }
        .nl-pill.soon { background:#fdf1e3; color:#d9770b; }
        .nl-pill.exp  { background:#fde8e8; color:#e3342f; }
        .nl-pill.none { background:#eef1f5; color:#94a3b8; }

        .nl-act .btn { border-radius:7px !important; margin:0 3px 4px 0; font-size:11.5px; padding:5px 9px; }
        .nl-doc { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:600; padding:4px 9px; border-radius:7px;
            border:1px solid #e6ebf2; color:#475569; margin:0 4px 4px 0; text-decoration:none; }
        .nl-doc:hover { border-color:#2c7be5; color:#2c7be5; text-decoration:none; }
        .nl-doc i { color:#2c7be5; }
        </style>

        <div class="nl-wrap">

        <div class="nl-head">
            <h1 class="nl-title"><span class="ic"><i class="fa fa-user-md"></i></span> <?php echo lang('nurse'); ?></h1>
            <div class="nl-actions no-print">
                <a href="<?php echo base_url('nurse/assignments'); ?>" class="nl-btn ghost"><i class="fa fa-list"></i> Assigned Nurses</a>
                <a href="<?php echo base_url('feedback'); ?>" class="nl-btn amber"><i class="fa fa-comments"></i> Feedback</a>
                <a data-toggle="modal" href="#myModal" class="nl-btn primary"><i class="fa fa-plus-circle"></i> <?php echo lang('add_nurse'); ?></a>
            </div>
        </div>

        <div class="nl-stats no-print">
            <div class="nl-stat"><div class="nl-stat-ic blue"><i class="fa fa-users"></i></div>
                <div><div class="nl-stat-v"><?php echo $nl_total; ?></div><div class="nl-stat-l">Total nurses</div></div></div>
            <div class="nl-stat"><div class="nl-stat-ic green"><i class="fa fa-check-circle"></i></div>
                <div><div class="nl-stat-v"><?php echo $nl_active; ?></div><div class="nl-stat-l">Active</div></div></div>
            <div class="nl-stat"><div class="nl-stat-ic violet"><i class="fa fa-user-md"></i></div>
                <div><div class="nl-stat-v"><?php echo $nl_onplace; ?></div><div class="nl-stat-l">On placement</div></div></div>
            <div class="nl-stat"><div class="nl-stat-ic amber"><i class="fa fa-certificate"></i></div>
                <div><div class="nl-stat-v"><?php echo $nl_lic_alert; ?></div><div class="nl-stat-l">Licence alerts</div></div></div>
        </div>

        <section class="panel nl-card" style="box-shadow:none;border:none;">
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
                                <th class="no-print">Certificates</th>
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

                            <?php foreach ($nurses as $nurse) {
                                // avatar initials fallback
                                $nl_init = '';
                                if (!empty($nurse->name)) {
                                    foreach (preg_split('/\s+/', trim($nurse->name)) as $pp) {
                                        if ($pp !== '' && strlen($nl_init) < 2) $nl_init .= strtoupper($pp[0]);
                                    }
                                }
                            ?>
                                <tr class="">
                                    <td style="width:56px;">
                                        <?php if (!empty($nurse->img_url)) { ?>
                                            <img class="nl-ava" src="<?php echo $nurse->img_url; ?>" alt="">
                                        <?php } else { ?>
                                            <span class="nl-ava-txt"><?php echo html_escape($nl_init ?: 'N'); ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="nl-name"><?php echo html_escape($nurse->name); ?></span>
                                        <?php if (isset($nurse->is_active) && $nurse->is_active == 0) { ?>
                                            <span class="nl-pill exp" style="margin-left:4px;"><i class="fa fa-ban"></i> Deactivated</span>
                                        <?php } ?>
                                    </td>
                                    <td class="nl-muted"><?php echo html_escape($nurse->email); ?></td>
                                    <td class="nl-muted"><?php echo html_escape($nurse->address); ?></td>
                                    <td class="nl-muted"><?php echo html_escape($nurse->phone); ?></td>

        <!-- LICENSE EXPIRY ALERT -->

        <td>
            <?php
            $today = date('Y-m-d');
            $expiry_date = $nurse->license_expiry_date;
            $three_months_later = date('Y-m-d', strtotime('+3 months'));

            if (empty($expiry_date) || $expiry_date == '0000-00-00') {
                echo '<span class="nl-pill none">Not set</span>';
            } elseif ($expiry_date < $today) {
                echo '<span class="nl-pill exp"><i class="fa fa-times-circle"></i> Expired</span>'
                   . '<div class="nl-muted" style="font-size:11px;margin-top:4px;">' . date('d M Y', strtotime($expiry_date)) . '</div>';
            } elseif ($expiry_date <= $three_months_later) {
                echo '<span class="nl-pill soon"><i class="fa fa-exclamation-triangle"></i> Expiring soon</span>'
                   . '<div class="nl-muted" style="font-size:11px;margin-top:4px;">' . date('d M Y', strtotime($expiry_date)) . '</div>';
            } else {
                echo '<span class="nl-pill ok"><i class="fa fa-check"></i> Valid</span>'
                   . '<div class="nl-muted" style="font-size:11px;margin-top:4px;">' . date('d M Y', strtotime($expiry_date)) . '</div>';
            }
            ?>
        </td>

        <!-- CERTIFICATES / LICENCE — viewable directly from the nurse record -->
        <td class="no-print">
            <?php if (!empty($nurse->nurse_license_pdf)) { ?>
                <a href="<?php echo base_url($nurse->nurse_license_pdf); ?>" target="_blank" class="nl-doc">
                    <i class="fa fa-certificate"></i> Licence
                </a>
            <?php } ?>
            <?php if (!empty($nurse->nurse_profile_pdf)) { ?>
                <a href="<?php echo base_url($nurse->nurse_profile_pdf); ?>" target="_blank" class="nl-doc">
                    <i class="fa fa-file-pdf"></i> Profile
                </a>
            <?php } ?>
            <?php if (empty($nurse->nurse_license_pdf) && empty($nurse->nurse_profile_pdf)) { ?>
                <span class="nl-muted">—</span>
            <?php } ?>
        </td>

                                    <td class="no-print nl-act">
                                        <a href="<?php echo base_url('nurse/record/' . $nurse->id); ?>"
                                            class="btn btn-default btn-xs" title="Full historical record">
                                            <i class="fa fa-folder-open"></i> Record
                                        </a>

                                        <?php if (isset($nurse->is_active) && $nurse->is_active == 0) { ?>
                                            <!-- Deactivated nurse: history preserved, allow restore -->
                                            <a class="btn btn-success btn-xs"
                                                href="<?php echo base_url('nurse/restore?id=' . $nurse->id); ?>"
                                                onclick="return confirm('Restore (reactivate) this nurse?');">
                                                <i class="fa fa-undo"></i> Restore
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php echo base_url('nurse/assign/' . $nurse->id); ?>"
                                                class="btn btn-success btn-xs">
                                                <i class="fa fa-user"></i> Assign
                                            </a>
                                            <button type="button" class="btn btn-info btn-xs btn_width editbutton" title="<?php echo lang('edit'); ?>" data-toggle="modal" data-id="<?php echo $nurse->id; ?>"><i class="fa fa-edit"> </i></button>
                                            <a class="btn btn-danger btn-xs btn_width" title="Deactivate"
                                                href="javascript:void(0);"
                                                onclick="deactivateNurse(<?php echo $nurse->id; ?>);"><i class="fa fa-trash"></i> </a>

                                            <a href="<?php echo base_url('nurse/payment/' . $nurse->id); ?>"
                                                class="btn btn-warning btn-xs">
                                                <i class="fa fa-money"></i> Payment
                                            </a>
                                            <a href="<?php echo base_url('nurse/unpaid/' . $nurse->id); ?>"
                                                class="btn btn-default btn-xs" title="Days / weeks not yet paid">
                                                <i class="fa fa-calendar-times"></i> Unpaid
                                            </a>
                                        <?php } ?>
                                    </td>

                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        </div><!-- /nl-wrap -->
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
                        <input type="file" name="nurse_profile_pdf" class="form-control pdf-only" accept="application/pdf">
                        <small class="text-muted">PDF only.</small>
                    </div>

                    <div class="form-group">
                        <label>Nurse License (PDF)</label>
                        <input type="file" name="nurse_license_pdf" class="form-control pdf-only" accept="application/pdf">
                        <small class="text-muted">PDF only.</small>
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

<script>
    // Deactivate (soft delete) a nurse: confirm + capture a reason,
    // then submit to the controller which preserves the historical record.
    function deactivateNurse(id) {
        if (!confirm('Deactivate this nurse? Their historical records will be preserved.')) {
            return;
        }
        var reason = '';
        while (reason.trim() === '') {
            reason = prompt('Reason for deactivation (required, recorded in the audit trail):', '');
            if (reason === null) {
                return; // cancelled
            }
        }
        window.location.href = 'nurse/delete?id=' + id + '&reason=' + encodeURIComponent(reason);
    }
</script>
<script src="common/js/codearistos.min.js"></script>
<script>
    // Instant client-side check: only allow PDF files for certificate/licence uploads.
    // Delegated so it also works on the AJAX-loaded edit form.
    $(document).on('change', 'input.pdf-only', function () {
        var f = this.files && this.files[0];
        if (!f) return;
        var okType = (f.type === 'application/pdf');
        var okExt = /\.pdf$/i.test(f.name);
        if (!okType && !okExt) {
            alert('Only PDF files are allowed for this field.\n"' + f.name + '" is not a PDF.');
            this.value = ''; // clear the invalid selection
        }
    });
</script>
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