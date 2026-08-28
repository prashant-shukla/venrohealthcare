<!--sidebar end-->
<!--main content start-->
<section id="main-content">
    <section class="wrapper site-min-height">
        <!-- page start-->
        <section class="panel">
            <header class="panel-heading">
                <?php
                if (!empty($nurse->id))
                    echo lang('edit_nurse');
                else
                    echo lang('add_nurse');
                ?>
            </header>
         
            <div class="panel-body col-md-7">
                <div class="adv-table editable-table ">
                    <div class="clearfix">

                        <div class="col-lg-12">
                            <section class="panel">
                                <div class="panel-body">
                                    <div class="col-lg-12">
                                        <div class="col-lg-3"></div>
                                        <div class="col-lg-6">
                                            <?php echo validation_errors(); ?>
                                            <?php echo $this->session->flashdata('feedback'); ?>
                                        </div>
                                        <div class="col-lg-3"></div>
                                    </div>
                                   
                                    <form role="form" action="nurse/addNew" method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('name'); ?></label>
                                            <input type="text" class="form-control" name="name" id="exampleInputEmail1" value='<?php
                                                                                                                                if (!empty($setval)) {
                                                                                                                                    echo set_value('name');
                                                                                                                                }
                                                                                                                                if (!empty($nurse->name)) {
                                                                                                                                    echo $nurse->name;
                                                                                                                                }
                                                                                                                                ?>'>
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('email'); ?></label>
                                            <input type="text" class="form-control" name="email" id="exampleInputEmail1" value='<?php
                                                                                                                                if (!empty($setval)) {
                                                                                                                                    echo set_value('email');
                                                                                                                                }
                                                                                                                                if (!empty($nurse->email)) {
                                                                                                                                    echo $nurse->email;
                                                                                                                                }
                                                                                                                                ?>' placeholder="">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('password'); ?></label>
                                            <input type="password" class="form-control" name="password" id="exampleInputEmail1" placeholder="********">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('address'); ?></label>
                                            <input type="text" class="form-control" name="address" id="exampleInputEmail1" value='<?php
                                                                                                                                    if (!empty($setval)) {
                                                                                                                                        echo set_value('address');
                                                                                                                                    }
                                                                                                                                    if (!empty($nurse->address)) {
                                                                                                                                        echo $nurse->address;
                                                                                                                                    }
                                                                                                                                    ?>' placeholder="">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('phone'); ?></label>
                                            <input type="text" class="form-control" name="phone" id="exampleInputEmail1" value='<?php
                                                                                                                                if (!empty($setval)) {
                                                                                                                                    echo set_value('phone');
                                                                                                                                }
                                                                                                                                if (!empty($nurse->phone)) {
                                                                                                                                    echo $nurse->phone;
                                                                                                                                }
                                                                                                                                ?>' placeholder="">
                                        </div>
                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('image'); ?></label>
                                            <input type="file" name="img_url">
                                        </div>




                                        <div class="form-group">
                                            <label for="exampleInputEmail1"><?php echo lang('age'); ?></label>
                                            <input type="number" name="age" class="form-control" id="exampleInputEmail1"
                                              value='<?php
                                                                if (!empty($setval)) {
                                                                      echo set_value('age');
                                                                     }
                                                                     if (!empty($nurse->age)) {
                                                                        echo $nurse->age;
                                                                       }
                                                                    ?>'>
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


                                        <input type="hidden" name="id" value='<?php
                                                                                if (!empty($nurse->id)) {
                                                                                    echo $nurse->id;
                                                                                }
                                                                                ?>'>
                                        <button type="submit" name="submit" class="btn btn-info"><?php echo lang('submit'); ?></button>
                                    </form>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- page end-->
    </section>
</section>
<!--main content end-->
<!--footer start-->