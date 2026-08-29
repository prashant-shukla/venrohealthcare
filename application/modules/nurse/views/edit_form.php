<?php
$nurse = isset($nurse) ? $nurse : null;
$days = !empty($nurse->available_days) ? explode(',', $nurse->available_days) : [];
?>

<form role="form" id="editNurseForm" class="clearfix" action="<?php echo base_url('nurse/addNew'); ?>" method="post" enctype="multipart/form-data">

    <div class="form-group">
        <label for="name"><?php echo lang('name'); ?></label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($nurse->name) ? $nurse->name : ''; ?>">
    </div>

    <div class="form-group">
        <label for="email"><?php echo lang('email'); ?></label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($nurse->email) ? $nurse->email : ''; ?>">
    </div>

    <div class="form-group">
        <label for="password"><?php echo lang('password'); ?></label>
        <input type="password" class="form-control" id="password" name="password" placeholder="********">
    </div>

    <div class="form-group">
        <label for="address"><?php echo lang('address'); ?></label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($nurse->address) ? $nurse->address : ''; ?>">
    </div>

    <div class="form-group">
        <label for="phone"><?php echo lang('phone'); ?></label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($nurse->phone) ? $nurse->phone : ''; ?>">
    </div>

    <div class="form-group">
        <label for="img_url"><?php echo lang('image'); ?></label>
        <input type="file" id="img_url" name="img_url" class="form-control">

        <?php if (!empty($nurse->img_url)) { ?>
            <br>
            <img src="<?php echo base_url($nurse->img_url); ?>" width="80">
        <?php } ?>
    </div>

    <div class="form-group">
        <label for="age"><?php echo lang('age'); ?></label>
        <input type="number" id="age" name="age" class="form-control" value="<?php echo isset($nurse->age) ? $nurse->age : ''; ?>">
    </div>

    <div class="form-group">
        <label for="sex"><?php echo lang('sex'); ?></label>
        <select name="sex" id="sex" class="form-control">
            <option value="Male" <?php if (isset($nurse->sex) && $nurse->sex == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if (isset($nurse->sex) && $nurse->sex == 'Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if (isset($nurse->sex) && $nurse->sex == 'Other') echo 'selected'; ?>>Other</option>
        </select>
    </div>

    <div class="form-group">
        <label><?php echo ('availability'); ?></label>

        <select name="availability" id="edit_availability" class="form-control" onchange="toggleEditAvailability()">
            <option value="Full Time" <?php if (isset($nurse->availability) && $nurse->availability == 'Full Time') echo 'selected'; ?>>Full Time</option>
            <option value="Specific Days" <?php if (isset($nurse->availability) && $nurse->availability == 'Specific Days') echo 'selected'; ?>>Specific Days</option>
        </select>
    </div>


    <div class="form-group" id="editAvailableDaysBox">

        <label><?php echo ('available days'); ?></label>

        <div class="row">

            <div class="col-md-4">
                <input type="checkbox" id="edit_monday" name="available_days[]" value="Monday" <?php if (in_array('Monday', $days)) echo 'checked'; ?>>
                <label for="edit_monday">Monday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_tuesday" name="available_days[]" value="Tuesday" <?php if (in_array('Tuesday', $days)) echo 'checked'; ?>>
                <label for="edit_tuesday">Tuesday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_wednesday" name="available_days[]" value="Wednesday" <?php if (in_array('Wednesday', $days)) echo 'checked'; ?>>
                <label for="edit_wednesday">Wednesday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_thursday" name="available_days[]" value="Thursday" <?php if (in_array('Thursday', $days)) echo 'checked'; ?>>
                <label for="edit_thursday">Thursday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_friday" name="available_days[]" value="Friday" <?php if (in_array('Friday', $days)) echo 'checked'; ?>>
                <label for="edit_friday">Friday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_saturday" name="available_days[]" value="Saturday" <?php if (in_array('Saturday', $days)) echo 'checked'; ?>>
                <label for="edit_saturday">Saturday</label>
            </div>

            <div class="col-md-4">
                <input type="checkbox" id="edit_sunday" name="available_days[]" value="Sunday" <?php if (in_array('Sunday', $days)) echo 'checked'; ?>>
                <label for="edit_sunday">Sunday</label>
            </div>

        </div>

    </div>


    <div class="form-group">
        <label for="license_expiry_date"><?php echo ('license expiry date'); ?></label>
        <input type="date" id="license_expiry_date" name="license_expiry_date" class="form-control" value="<?php echo isset($nurse->license_expiry_date) ? $nurse->license_expiry_date : ''; ?>">
    </div>

    <div class="form-group">
        <label for="nurse_profile_pdf"><?php echo ('nurse profile pdf'); ?></label>
        <input type="file" id="nurse_profile_pdf" name="nurse_profile_pdf" class="form-control">

        <?php if (!empty($nurse->nurse_profile_pdf)) { ?>
            <br>
            <a href="<?php echo base_url($nurse->nurse_profile_pdf); ?>" target="_blank">View Current PDF</a>
        <?php } ?>
    </div>

    <div class="form-group">
        <label for="nurse_license_pdf"><?php echo ('nurse license pdf'); ?></label>
        <input type="file" id="nurse_license_pdf" name="nurse_license_pdf" class="form-control">

        <?php if (!empty($nurse->nurse_license_pdf)) { ?>
            <br>
            <a href="<?php echo base_url($nurse->nurse_license_pdf); ?>" target="_blank">View Current PDF</a>
        <?php } ?>
    </div>

<div class="form-group">
<label for="status"><?php echo ('status'); ?></label>

<select name="status" id="form_status" class="form-control">

<option value="Available" <?php if(isset($nurse->status) && $nurse->status=='Available') echo 'selected'; ?>>
Available
</option>

<option value="On Placement" <?php if(isset($nurse->status) && $nurse->status=='On Placement') echo 'selected'; ?>>
On Placement
</option>

<option value="Discontinued" <?php if(isset($nurse->status) && $nurse->status=='Discontinued') echo 'selected'; ?>>
Discontinued
</option>

</select>
</div>


<div class="form-group" id="discontinuedReasonBox_section"
style="<?php if(isset($nurse->status) && $nurse->status!='Discontinued') echo 'display:none'; ?>">

<label for="discontinued_reason"><?php echo ('discontinued reason'); ?></label>

<textarea id="discontinued_reason" name="discontinued_reason" class="form-control"><?php echo isset($nurse->discontinued_reason) ? html_escape($nurse->discontinued_reason) : ''; ?></textarea>

</div>

    <input type="hidden" name="id" value="<?php echo isset($nurse->id) ? $nurse->id : ''; ?>">

    <div class="form-group col-md-12">
        <button type="submit" class="btn btn-info pull-right"><?php echo ('submit'); ?></button>
    </div>

</form>

<script>
    function toggleEditAvailability() {

        var availability = document.getElementById("edit_availability").value;
        var box = document.getElementById("editAvailableDaysBox");

        if (availability === "Specific Days") {

            box.style.display = "block";

        } else {

            box.style.display = "none";

        }

    }


    // page load par bhi check kare
    toggleEditAvailability();

document.getElementById("form_status").addEventListener("change", function () {

    var status = this.value;
    var box = document.getElementById("discontinuedReasonBox_section");

    console.log(status);

    if(status === "Discontinued"){
        box.style.display = "block";
    } else {
        box.style.display = "none";
    }
});
</script>