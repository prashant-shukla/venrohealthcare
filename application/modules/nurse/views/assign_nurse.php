<?php $cur = $this->currency; ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
.as-wrap { padding:6px 4px 30px; }
.as-wrap a { text-decoration:none; }
.as-back { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:13px; margin-bottom:14px; }
.as-back:hover { color:#2c7be5; }
.as-err { background:#fde8e8; border:1px solid #f5b5b5; color:#c0392b; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:9px; }
.as-err.as-ok { background:#e6f6ee; border-color:#b7e4c7; color:#1b7742; }
.as-err.as-warn { background:#fdf1e3; border-color:#f6d9b8; color:#a85d06; }

/* Hero */
.as-hero { position:relative; background:linear-gradient(135deg,#2c7be5 0%,#1b5fbe 100%); border-radius:14px; padding:22px 26px; color:#fff;
  box-shadow:0 8px 24px rgba(44,123,229,.22); overflow:hidden; display:flex; align-items:center; gap:18px; }
.as-hero:after { content:""; position:absolute; right:-40px; top:-50px; width:170px; height:170px; background:rgba(255,255,255,.08); border-radius:50%; }
.as-hero .ava { width:60px; height:60px; border-radius:50%; background:rgba(255,255,255,.18); border:2px solid rgba(255,255,255,.5);
  display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; flex:0 0 auto; z-index:1; }
.as-hero .t { position:relative; z-index:1; }
.as-hero .lbl { font-size:12px; text-transform:uppercase; letter-spacing:.5px; opacity:.85; }
.as-hero .nm { font-size:22px; font-weight:700; margin-top:3px; }

/* Cards */
.as-card { background:#fff; border:1px solid #eef1f5; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.06); margin:22px 0; overflow:hidden; }
.as-card-h { padding:15px 20px; border-bottom:1px solid #f1f4f8; font-size:14px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:9px; }
.as-card-h i { color:#2c7be5; }
.as-card-b { padding:20px; }
.as-sec { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8; margin:6px 0 12px; padding-top:6px; border-top:1px dashed #eef1f5; }
.as-sec:first-child { border-top:none; padding-top:0; }

.as-card-b label { font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; }
.as-card-b .form-control { border-radius:8px; border:1px solid #e2e8f0; box-shadow:none; font-size:13px; height:auto; padding:9px 12px; }
.as-card-b .form-control:focus { border-color:#2c7be5; box-shadow:0 0 0 3px rgba(44,123,229,.12); }
.as-submit { display:inline-flex; align-items:center; gap:8px; padding:11px 22px; border-radius:9px; font-size:14px; font-weight:600; border:none; color:#fff; background:#2c7be5; cursor:pointer; transition:.15s; }
.as-submit:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(44,123,229,.28); }

/* select2 tweak */
.select2-container--default .select2-selection--single { height:38px; border:1px solid #e2e8f0; border-radius:8px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:38px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height:36px; }

/* Table */
.as-table { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
.as-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8; padding:11px 14px; border-bottom:2px solid #eef1f5; font-weight:700; }
.as-table td { padding:12px 14px; border-bottom:1px solid #f4f6fa; color:#334155; vertical-align:middle; }
.as-table tr:hover td { background:#fafbfe; }
.as-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-block; }
.as-badge.primary { background:#e6f6ee; color:#1f9d55; }
.as-badge.add { background:#e8f1fe; color:#2c7be5; }
.as-doc { display:inline-flex; align-items:center; gap:6px; }
.as-doc i { color:#5b63d3; }
.as-doc .none { color:#94a3b8; }
.as-btn { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; padding:6px 12px; border-radius:7px; border:none; color:#fff; cursor:pointer; margin:0 3px 3px 0; text-decoration:none; }
.as-btn.bill { background:#2c7be5; } .as-btn.bill:hover { background:#1b5fbe; }
.as-btn.rem  { background:#ef5350; } .as-btn.rem:hover { background:#d63a37; }
.as-btn.edit { background:#64748b; } .as-btn.edit:hover { background:#475569; }
.as-btn.fb   { background:#e8830c; } .as-btn.fb:hover { background:#c96f08; }
.as-cur { color:#0f9d58; font-weight:700; }
.as-empty { text-align:center; color:#94a3b8; font-size:13px; padding:26px; }
</style>

<section id="main-content">
  <section class="wrapper site-min-height">
    <div class="as-wrap">

      <a class="as-back" href="<?php echo base_url('nurse'); ?>"><i class="fa fa-arrow-left"></i> Back to Nurses</a>

      <?php if ($this->session->flashdata('error')) { ?>
        <div class="as-err"><i class="fa fa-exclamation-circle"></i> <?php echo html_escape($this->session->flashdata('error')); ?></div>
      <?php } ?>
      <?php if ($this->session->flashdata('success')) { ?>
        <div class="as-err as-ok"><i class="fa fa-check-circle"></i> <?php echo html_escape($this->session->flashdata('success')); ?></div>
      <?php } ?>
      <?php if ($this->session->flashdata('warning')) { ?>
        <div class="as-err as-warn"><i class="fa fa-exclamation-triangle"></i> <?php echo html_escape($this->session->flashdata('warning')); ?></div>
      <?php } ?>

      <!-- ===== HERO ===== -->
      <div class="as-hero">
        <div class="ava">
          <?php
          $ini = '';
          foreach (preg_split('/\s+/', trim($nurse->name)) as $pp) { if ($pp !== '' && strlen($ini) < 2) $ini .= strtoupper($pp[0]); }
          echo html_escape($ini ?: 'N');
          ?>
        </div>
        <div class="t">
          <div class="lbl"><i class="fa fa-user-plus"></i> Assign Nurse to Patient</div>
          <div class="nm"><?php echo html_escape($nurse->name); ?></div>
        </div>
      </div>

      <!-- ===== ASSIGN FORM ===== -->
      <div class="as-card">
        <div class="as-card-h"><i class="fa fa-user-plus"></i> New Assignment</div>
        <div class="as-card-b">
          <form method="post" action="<?php echo base_url('nurse/saveAssign'); ?>">
            <input type="hidden" name="nurse_id" value="<?php echo $nurse->id; ?>">

            <div class="as-sec">Patient &amp; Role</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="patient_id">Select Patient</label>
                  <select name="patient_id" id="patient_id" class="form-control">
                    <option value="">Select Patient</option>
                    <?php foreach ($patients as $p) {
                        $color = ''; $flag = '';
                        if (in_array($p->id, $assigned_patient_ids)) { $color = 'orange'; $flag = '🟡'; }
                        elseif ($p->status == 'Discharged' || $p->status == 'Deceased') { $color = 'green'; $flag = '🟢'; }
                        else { $color = 'red'; $flag = '🔴'; }
                    ?>
                      <option value="<?php echo $p->id; ?>" style="color:<?php echo $color; ?>; font-weight:bold;">
                        <?php echo $flag . ' ' . html_escape($p->name); ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="assignment_role">Assignment Role</label>
                  <select name="assignment_role" id="assignment_role" class="form-control">
                    <option value="Primary">Primary (Day) Nurse</option>
                    <option value="Additional">Additional / Alternate Nurse</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="as-sec">Billing</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="payment_term">Billing Type</label>
                  <select name="payment_term" id="payment_term" class="form-control">
                    <option value="Day">Daily</option>
                    <option value="Week">Weekly</option>
                    <option value="Month">Monthly</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6" id="dayBox">
                <div class="form-group">
                  <label>Per Day amount (<?php echo $cur; ?>)</label>
                  <input type="number" name="per_day_fee" id="per_day_fee" class="form-control">
                </div>
              </div>
              <div class="col-md-6" id="weekBox">
                <div class="form-group">
                  <label>Per Week amount (<?php echo $cur; ?>)</label>
                  <input type="number" name="per_week_fee" id="per_week_fee" class="form-control">
                </div>
              </div>
              <div class="col-md-6" id="monthBox">
                <div class="form-group">
                  <label>Per Month amount (<?php echo $cur; ?>)</label>
                  <input type="number" name="per_month_fee" id="per_month_fee" class="form-control">
                </div>
              </div>
            </div>

            <div class="as-sec">Service Period</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="start_date">Start Date</label>
                  <input type="date" name="start_date" id="start_date" class="form-control">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="end_date">End Date</label>
                  <input type="date" name="end_date" id="end_date" class="form-control">
                </div>
              </div>
            </div>

            <div class="as-sec">Transport / Commute (Optional)</div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="transport_frequency">Commute Frequency</label>
                  <select name="transport_frequency" id="transport_frequency" class="form-control">
                    <option value="">-- Select --</option>
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Flexible">Flexible Commute</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="transport_charge">Transport Charge (<?php echo $cur; ?>) <small id="transport_charge_hint" class="text-muted"></small></label>
                  <input type="number" step="0.01" name="transport_charge" id="transport_charge" class="form-control">
                </div>
              </div>
              <div class="col-md-12" id="transportNoteBox" style="display:none;">
                <div class="form-group">
                  <label for="transport_note">Agreed Arrangement (Flexible Commute)</label>
                  <textarea name="transport_note" id="transport_note" class="form-control" rows="2"
                    placeholder="Describe the agreed transport/commute arrangement"></textarea>
                </div>
              </div>
            </div>

            <button type="submit" class="as-submit"><i class="fa fa-check"></i> Assign Nurse</button>
          </form>
        </div>
      </div>

      <!-- ===== HISTORY ===== -->
      <div class="as-card">
        <div class="as-card-h"><i class="fa fa-history"></i> Assignment History</div>
        <div class="as-card-b" style="padding:0;">
          <?php if (!empty($history)) { ?>
            <table class="as-table">
              <thead>
                <tr>
                  <th>Patient</th>
                  <th>Associated Doctor</th>
                  <th>Role</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Term</th>
                  <th>Fee</th>
                  <th>Transport</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $unit_label = array('Daily' => '/ day', 'Weekly' => '/ week', 'Monthly' => '/ month', 'Flexible' => 'agreed');
                foreach ($history as $row) {
                  $cp = $row->current;
                  $row_freq = $cp ? $cp->billing_frequency : null;
                  $row_rate = $cp ? (float) $cp->rate : (float) max($row->per_day_fee, $row->per_week_fee, $row->per_month_fee);
                  $row_tcharge = $cp ? (float) $cp->transport_charge : (float) $row->transport_charge;
                  $row_tfreq = $cp ? $cp->transport_frequency : $row->transport_frequency;
                ?>
                  <tr>
                    <td style="font-weight:600;color:#1e293b;"><?php echo html_escape($row->patient_name); ?></td>
                    <td>
                      <span class="as-doc">
                        <?php if (!empty($row->doctor_name)) { ?>
                          <i class="fa fa-user-md"></i> <?php echo html_escape($row->doctor_name); ?>
                        <?php } else { ?>
                          <span class="none">— none —</span>
                        <?php } ?>
                      </span>
                    </td>
                    <td>
                      <?php if (isset($row->assignment_role) && $row->assignment_role == 'Additional') { ?>
                        <span class="as-badge add">Additional</span>
                      <?php } else { ?>
                        <span class="as-badge primary">Primary</span>
                      <?php } ?>
                    </td>
                    <td><?php echo !empty($row->start_date) ? date('d M Y', strtotime($row->start_date)) : '—'; ?></td>
                    <td><?php echo !empty($row->end_date) ? date('d M Y', strtotime($row->end_date)) : 'Ongoing'; ?></td>
                    <td><?php echo $row_freq ? $row_freq : html_escape($row->payment_term); ?></td>
                    <td class="as-cur"><?php echo $row_rate > 0 ? $cur . ' ' . number_format($row_rate, 2) : '—'; ?></td>
                    <td>
                      <?php if ($row_tcharge > 0) { ?>
                        <?php echo $cur . ' ' . number_format($row_tcharge, 2); ?>
                        <small style="color:#94a3b8;"><?php echo isset($unit_label[$row_tfreq]) ? $unit_label[$row_tfreq] : ''; ?></small>
                      <?php } else { ?>
                        <span style="color:#94a3b8;">—</span>
                      <?php } ?>
                    </td>
                    <td>
                      <a href="<?php echo base_url('nurse/billing/' . $row->id); ?>" class="as-btn bill"><i class="fa fa-file-text-o"></i> Billing</a>
                      <a href="<?php echo base_url('nurse/editAssignment/' . $row->id . '?return=assign'); ?>" class="as-btn edit"><i class="fa fa-edit"></i> Edit</a>
                      <a href="<?php echo base_url('feedback?nurse_id=' . $row->nurse_id . '&patient_id=' . $row->patient_id . '&assignment_id=' . $row->id); ?>" class="as-btn fb"><i class="fa fa-comments"></i> Feedback</a>
                      <a href="javascript:void(0);" class="as-btn rem" onclick="removeAssignment(<?php echo $row->id; ?>)"><i class="fa fa-trash"></i> Remove</a>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="as-empty">No assignment history found.</div>
          <?php } ?>
        </div>
      </div>

    </div>
  </section>
</section>

<script>
    // Soft-remove an assignment: confirm + capture reason (audit trail)
    function removeAssignment(id) {
        if (!confirm('Remove this assignment? The historical record will be preserved.')) { return; }
        var reason = '';
        while (reason.trim() === '') {
            reason = prompt('Reason for removal (required, recorded in the audit trail):', '');
            if (reason === null) { return; }
        }
        window.location.href = '<?php echo base_url('nurse/deleteAssignments/'); ?>' + id + '?reason=' + encodeURIComponent(reason);
    }
</script>

<script>
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
    startDate.addEventListener('change', function() {
        endDate.min = this.value;
        if (endDate.value < this.value) { endDate.value = ''; }
    });
</script>

<script>
    function togglePaymentFields() {
        var term = document.getElementById("payment_term").value;
        document.getElementById("dayBox").style.display = "none";
        document.getElementById("weekBox").style.display = "none";
        document.getElementById("monthBox").style.display = "none";
        if (term === "Day") { document.getElementById("dayBox").style.display = "block"; }
        else if (term === "Week") { document.getElementById("weekBox").style.display = "block"; }
        else { document.getElementById("monthBox").style.display = "block"; }
    }
    document.getElementById("payment_term").addEventListener("change", togglePaymentFields);
    window.onload = togglePaymentFields;
</script>

<script>
    $(document).ready(function() {
        $('#patient_id').select2({ placeholder: "Search Patient", allowClear: true, width: '100%' });
    });
</script>

<script>
    function toggleTransportFields() {
        var freq = document.getElementById("transport_frequency").value;
        var noteBox = document.getElementById("transportNoteBox");
        var hint = document.getElementById("transport_charge_hint");
        if (freq === "Flexible") {
            noteBox.style.display = "block";
            hint.textContent = "(agreed amount)";
        } else {
            noteBox.style.display = "none";
            if (freq === "Daily") { hint.textContent = "(per day)"; }
            else if (freq === "Weekly") { hint.textContent = "(per week)"; }
            else if (freq === "Monthly") { hint.textContent = "(per month)"; }
            else { hint.textContent = ""; }
        }
    }
    document.getElementById("transport_frequency").addEventListener("change", toggleTransportFields);
    toggleTransportFields();
</script>
