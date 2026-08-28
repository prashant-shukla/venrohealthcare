<section id="main-content">
    <section class="wrapper site-min-height">

        <section class="panel">

            <header class="panel-heading">
                Add Bedside Nurse
            </header>

            <div class="panel-body">

                <div class="row">

                    <div class="col-md-6 col-md-offset-3">

                        <form method="post" action="<?php echo base_url('bedside_nurse/save'); ?>" enctype="multipart/form-data">

                            <div class="form-group">
                                <label>Nurse Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age" class="form-control">
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
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Residence Address</label>
                                <input type="text" name="residence" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Availability</label>

                                <select name="availability" class="form-control">
                                    <option value="Full Time">Full Time</option>
                                    <option value="Specific Days">Specific Days</option>
                                </select>

                            </div>

                            <div class="form-group">
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
                                <label>Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control">
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

                                <select name="status" class="form-control">
                                    <option value="Available">Available</option>
                                    <option value="On Placement">On Placement</option>
                                    <option value="Discontinued">Discontinued</option>
                                </select>

                            </div>

                            <div class="form-group">
                                <label>Discontinued Reason</label>
                                <textarea name="discontinued_reason" class="form-control"></textarea>
                            </div>

                            <button class="btn btn-success">
                                Submit
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </section>

    </section>
</section>