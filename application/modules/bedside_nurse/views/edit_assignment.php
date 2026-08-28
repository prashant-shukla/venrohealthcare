<section id="main-content">
<section class="wrapper site-min-height">

<section class="panel">

<header class="panel-heading">
Edit Assignment
</header>

<div class="panel-body col-md-6">

<form method="post" action="<?php echo base_url('bedside_nurse/updateAssignment'); ?>">

<input type="hidden" name="id" value="<?php echo $assignment->id; ?>">

<div class="form-group">
<label>Patient</label>

<select name="patient_id" class="form-control">

<?php foreach($patients as $p){ ?>

<option value="<?php echo $p->id ?>"
<?php if($p->id==$assignment->patient_id) echo "selected"; ?>>

<?php echo $p->name ?>

</option>

<?php } ?>

</select>

</div>


<div class="form-group">
<label>Start Date</label>

<input type="date"
name="start_date"
value="<?php echo $assignment->start_date ?>"
class="form-control">
</div>


<div class="form-group">
<label>End Date</label>

<input type="date"
name="end_date"
value="<?php echo $assignment->end_date ?>"
class="form-control">
</div>


<div class="form-group">
<label>Status</label>

<select name="status" class="form-control">

<option value="Active"
<?php if($assignment->status=='Active') echo "selected"; ?>>

Active

</option>

<option value="Completed"
<?php if($assignment->status=='Completed') echo "selected"; ?>>

Completed

</option>

</select>

</div>

<button class="btn btn-success">

Update Assignment

</button>

</form>

</div>

</section>

</section>
</section>