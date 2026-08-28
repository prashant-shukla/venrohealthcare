<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Edit Bedside Nurse
            </header>

            <div class="panel-body col-md-6">

                <form method="post" action="<?php echo base_url('bedside_nurse/update'); ?>" enctype="multipart/form-data">

                    <input type="hidden" name="id" value="<?php echo $bedside->id; ?>">

                    <div class="form-group">
                        <label>Nurse Name</label>

                        <input type="text"
                            name="name"
                            value="<?php echo $bedside->name; ?>"
                            class="form-control">

                    </div>


                    <div class="form-group">
                        <label>Email</label>

                        <input type="email"
                            name="email"
                            value="<?php echo $bedside->email; ?>"
                            class="form-control">

                    </div>

                    <div class="form-group">
                        <label>Password</label>

                        <input type="password"
                            name="password"
                            class="form-control"
                            placeholder="Leave blank to keep old password">

                    </div>

                    <div class="form-group">
                        <label>Age</label>

                        <input type="number"
                            name="age"
                            value="<?php echo $bedside->age; ?>"
                            class="form-control">

                    </div>


                    <div class="form-group">
                        <label>Sex</label>

                        <select name="sex" class="form-control">

                            <option value="Male" <?php if ($bedside->sex == 'Male') echo "selected"; ?>>Male</option>

                            <option value="Female" <?php if ($bedside->sex == 'Female') echo "selected"; ?>>Female</option>

                            <option value="Other" <?php if ($bedside->sex == 'Other') echo "selected"; ?>>Other</option>

                        </select>

                    </div>


                    <div class="form-group">
                        <label>Phone</label>

                        <input type="text"
                            name="phone"
                            value="<?php echo $bedside->phone; ?>"
                            class="form-control">

                    </div>


                    <div class="form-group">
                        <label>Residence Address</label>

                        <input type="text"
                            name="residence"
                            value="<?php echo $bedside->residence; ?>"
                            class="form-control">

                    </div>


                    <div class="form-group">
                        <label>Availability</label>

                        <select name="availability" class="form-control">

                            <option value="Full Time"
                                <?php if ($bedside->availability == 'Full Time') echo "selected"; ?>>
                                Full Time
                            </option>

                            <option value="Specific Days"
                                <?php if ($bedside->availability == 'Specific Days') echo "selected"; ?>>
                                Specific Days
                            </option>

                        </select>

                    </div>


                    <?php
                    $days = [];
                    if (!empty($bedside->available_days)) {
                        $days = explode(',', $bedside->available_days);
                    }
                    ?>

                    <div class="form-group">
                        <label>Available Days (If Specific)</label>

                        <div class="row">

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Monday"
                                        <?php if (in_array('Monday', $days)) echo "checked"; ?>>
                                    Monday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Tuesday"
                                        <?php if (in_array('Tuesday', $days)) echo "checked"; ?>>
                                    Tuesday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Wednesday"
                                        <?php if (in_array('Wednesday', $days)) echo "checked"; ?>>
                                    Wednesday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Thursday"
                                        <?php if (in_array('Thursday', $days)) echo "checked"; ?>>
                                    Thursday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Friday"
                                        <?php if (in_array('Friday', $days)) echo "checked"; ?>>
                                    Friday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Saturday"
                                        <?php if (in_array('Saturday', $days)) echo "checked"; ?>>
                                    Saturday
                                </label>
                            </div>

                            <div class="col-md-4">
                                <label>
                                    <input type="checkbox" name="available_days[]" value="Sunday"
                                        <?php if (in_array('Sunday', $days)) echo "checked"; ?>>
                                    Sunday
                                </label>
                            </div>

                        </div>
                    </div>

                    <div class="form-group">
                        <label>License Expiry Date</label>

                        <input type="date"
                            name="license_expiry_date"
                            value="<?php echo $bedside->license_expiry_date; ?>"
                            class="form-control">

                    </div>




                    <div class="form-group">

                        <label>Profile photo</label>

                        <?php if (!empty($bedside->profile_photo)) { ?>

                            <a href="<?php echo base_url($bedside->profile_photo); ?>"
                                class="btn btn-info btn-xs"
                                target="_blank">

                                Profile photo

                            </a>

                        <?php } ?>

                        <input type="file"
                            name="profile_photo"
                            class="form-control">

                    </div>



                    <div class="form-group">

                        <label>Nurse Profile PDF</label>

                        <?php if (!empty($bedside->nurse_profile_pdf)) { ?>

                            <a href="<?php echo base_url($bedside->nurse_profile_pdf); ?>"
                                class="btn btn-info btn-xs"
                                target="_blank">

                                View Profile

                            </a>

                        <?php } ?>

                        <input type="file"
                            name="nurse_profile_pdf"
                            class="form-control">

                    </div>



                    <div class="form-group">

                        <label>Nurse License PDF</label>

                        <?php if (!empty($bedside->nurse_license_pdf)) { ?>

                            <div style="margin-bottom:8px;">

                                <a href="<?php echo base_url($bedside->nurse_license_pdf); ?>"
                                    target="_blank"
                                    class="btn btn-warning btn-xs">

                                    <i class="fa fa-file-pdf-o"></i> View License PDF

                                </a>

                            </div>

                        <?php } ?>

                        <input type="file"
                            name="nurse_license_pdf"
                            class="form-control">

                    </div>

                    <div class="form-group">
                        <label>Status</label>

                        <select name="status" class="form-control">

                            <option value="Available"
                                <?php if ($bedside->status == 'Available') echo "selected"; ?>>
                                Available
                            </option>

                            <option value="On Placement"
                                <?php if ($bedside->status == 'On Placement') echo "selected"; ?>>
                                On Placement
                            </option>

                            <option value="Discontinued"
                                <?php if ($bedside->status == 'Discontinued') echo "selected"; ?>>
                                Discontinued
                            </option>

                        </select>

                    </div>


                    <div class="form-group">
                        <label>Discontinued Reason</label>

                        <textarea name="discontinued_reason"
                            class="form-control"><?php echo $bedside->discontinued_reason; ?></textarea>

                    </div>


                    <br>

                    <button class="btn btn-success">
                        Update
                    </button>

                </form>

            </div>

        </section>

    </section>
</section>