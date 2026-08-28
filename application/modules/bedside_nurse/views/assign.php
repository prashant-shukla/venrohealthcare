<section id="main-content">
<section class="wrapper site-min-height">

<section class="panel">

<header class="panel-heading">
Assign Nurse To Patient
</header>

<div class="panel-body col-md-6">

<form method="post" action="<?php echo base_url('bedside_nurse/saveAssign'); ?>">

<input type="hidden" name="nurse_id" value="<?php echo $bedside->id; ?>">

<div class="form-group">
<label>Select Patient</label>

<select name="patient_id" class="form-control">

<?php foreach($patients as $p){ ?>

<option value="<?php echo $p->id ?>">
<?php echo $p->name ?>
</option>

<?php } ?>

</select>

</div>


<div class="form-group">
<label>Start Date</label>

<input type="date" name="start_date" class="form-control">

</div>


<div class="form-group">
<label>End Date</label>

<input type="date" name="end_date" class="form-control">

</div>


<br>

<button type="submit" class="btn btn-success">
Assign Nurse
</button>

</form>

</div>

</section>

</section>
</section>