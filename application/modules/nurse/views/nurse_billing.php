<?php
// $this->currency is provided via CI_Controller::__get() (HMVC), so read it
// directly rather than isset() which does not trigger the magic getter.
$cur = $this->currency;
$freq_unit = array('Daily' => 'day', 'Weekly' => 'week', 'Monthly' => 'month');
$cur_freq = !empty($current) ? $current->billing_frequency : null;
$cur_rate = !empty($current) ? $current->rate : null;
$cur_tfreq = (!empty($current) && $current->transport_charge > 0) ? $current->transport_frequency : null;
$cur_tcharge = !empty($current) ? (float) $current->transport_charge : 0;
$bill_total = $breakdown['billing_total'];
$transport = $breakdown['transport_total'];
$grand = $breakdown['total'];
$accrued_total = $accrued['total'];
$period_count = 0;
foreach ((array) $periods as $p) { if (empty($p->is_superseded)) $period_count++; }
$t_unit = array('Daily' => '/ day', 'Weekly' => '/ week', 'Monthly' => '/ month', 'Flexible' => 'agreed amount');
?>

<style>
.br-wrap { padding:6px 4px 30px; }
.br-wrap a { text-decoration:none; }
.br-back { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:13px; margin-bottom:14px; }
.br-back:hover { color:#2c7be5; }

.br-flash { background:#e6f6ee; border:1px solid #b7e4c7; color:#1b7742; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:9px; }
.br-flash i { color:#1f9d55; }
.br-flash.err { background:#fde8e8; border-color:#f5b5b5; color:#c0392b; }
.br-flash.err i { color:#c0392b; }
.br-table tr.superseded td { color:#94a3b8; text-decoration:line-through; }
.br-table tr.superseded td.keep { text-decoration:none; }

/* Hero */
.br-hero { position:relative; background:linear-gradient(135deg,#0f9d58 0%,#0b8043 100%); border-radius:14px;
  padding:24px 28px; color:#fff; box-shadow:0 8px 24px rgba(15,157,88,.22); overflow:hidden; }
.br-hero:after { content:""; position:absolute; right:-40px; top:-50px; width:180px; height:180px; background:rgba(255,255,255,.08); border-radius:50%; }
.br-hero-top { font-size:12px; letter-spacing:.5px; text-transform:uppercase; opacity:.85; position:relative; z-index:1; }
.br-hero-h { font-size:23px; font-weight:700; margin:5px 0 0; position:relative; z-index:1; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.br-hero-h .arw { opacity:.7; font-size:16px; }
.br-hero-pills { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; position:relative; z-index:1; }
.br-hpill { font-size:12.5px; font-weight:600; padding:6px 13px; border-radius:20px; background:rgba(255,255,255,.16); display:inline-flex; align-items:center; gap:7px; }

/* Stat tiles */
.br-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin:22px 0; }
@media (max-width:900px){ .br-stats { grid-template-columns:repeat(2,1fr); } }
.br-stat { background:#fff; border:1px solid #eef1f5; border-radius:12px; padding:16px 18px; box-shadow:0 1px 3px rgba(16,24,40,.06); }
.br-stat-l { font-size:12px; color:#94a3b8; display:flex; align-items:center; gap:7px; }
.br-stat-v { font-size:21px; font-weight:700; color:#1e293b; margin-top:7px; line-height:1.1; }
.br-stat-v small { font-size:12px; color:#94a3b8; font-weight:600; }
.br-stat-l i { color:#0f9d58; }

/* Cards */
.br-card { background:#fff; border:1px solid #eef1f5; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.06); margin-bottom:22px; overflow:hidden; }
.br-card-h { padding:15px 20px; border-bottom:1px solid #f1f4f8; font-size:14px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:9px; }
.br-card-h i { color:#2c7be5; }
.br-card-b { padding:20px; }
.br-hint { font-size:12px; color:#94a3b8; margin:0 0 16px; }

/* Forms */
.br-field { margin-bottom:15px; }
.br-field label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; }
.br-field .form-control { border-radius:8px; border:1px solid #e2e8f0; box-shadow:none; font-size:13px; height:auto; padding:9px 12px; }
.br-field .form-control:focus { border-color:#2c7be5; box-shadow:0 0 0 3px rgba(44,123,229,.12); }
.br-submit { display:inline-flex; align-items:center; gap:7px; padding:10px 18px; border-radius:9px; font-size:13px; font-weight:600; border:none; color:#fff; cursor:pointer; transition:.15s; }
.br-submit:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.15); }
.br-submit.blue { background:#2c7be5; }
.br-submit.teal { background:#0f9d58; }

/* Tables */
.br-table { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
.br-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8; padding:11px 14px; border-bottom:2px solid #eef1f5; font-weight:700; }
.br-table td { padding:12px 14px; border-bottom:1px solid #f4f6fa; color:#334155; }
.br-table tr:hover td { background:#fafbfe; }
.br-table tfoot td { border-top:2px solid #eef1f5; border-bottom:none; font-weight:700; color:#1e293b; background:#fbfcfe; }
.br-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-block; background:#e8f1fe; color:#2c7be5; }
.br-badge.grey { background:#eef1f5; color:#64748b; }
.br-badge.green { background:#e6f6ee; color:#1f9d55; }
.br-cur { color:#0f9d58; font-weight:700; }
.br-ongoing { color:#0f9d58; font-weight:600; }
.br-empty { text-align:center; color:#94a3b8; font-size:13px; padding:26px; }
.br-note { font-size:12px; color:#94a3b8; margin:14px 4px 0; display:flex; gap:8px; }
.br-note i { color:#94a3b8; margin-top:2px; }
</style>

<section id="main-content">
  <section class="wrapper site-min-height">
    <div class="br-wrap">

      <a class="br-back" href="<?php echo base_url('nurse/assign/' . $assignment->nurse_id); ?>"><i class="fa fa-arrow-left"></i> Back to Assignments</a>

      <?php if ($this->session->flashdata('success')) { ?>
        <div class="br-flash"><i class="fa fa-check-circle"></i> <?php echo html_escape($this->session->flashdata('success')); ?></div>
      <?php } ?>
      <?php if ($this->session->flashdata('error')) { ?>
        <div class="br-flash err"><i class="fa fa-exclamation-circle"></i> <?php echo html_escape($this->session->flashdata('error')); ?></div>
      <?php } ?>

      <!-- ===== HERO ===== -->
      <div class="br-hero">
        <div class="br-hero-top"><i class="fa fa-file-text-o"></i> Billing Plan</div>
        <div class="br-hero-h">
          <?php echo html_escape($assignment->nurse_name); ?>
          <span class="arw"><i class="fa fa-long-arrow-right"></i></span>
          <?php echo html_escape($assignment->patient_name); ?>
        </div>
        <div class="br-hero-pills">
          <span class="br-hpill"><i class="fa fa-calendar"></i>
            <?php echo !empty($assignment->start_date) ? date('d M Y', strtotime($assignment->start_date)) : '—'; ?>
            &ndash;
            <?php echo !empty($assignment->end_date) ? date('d M Y', strtotime($assignment->end_date)) : 'Ongoing'; ?>
          </span>
          <?php if ($cur_freq) { ?>
            <span class="br-hpill"><i class="fa fa-repeat"></i> <?php echo $cur_freq; ?></span>
            <span class="br-hpill"><i class="fa fa-money"></i> <?php echo $cur . ' ' . number_format($cur_rate, 2); ?>
              / <?php echo isset($freq_unit[$cur_freq]) ? $freq_unit[$cur_freq] : ''; ?></span>
          <?php } ?>
          <?php if ($cur_tfreq) { ?>
            <span class="br-hpill"><i class="fa fa-bus"></i> <?php echo $cur . ' ' . number_format($cur_tcharge, 2) . ' ' . $t_unit[$cur_tfreq]; ?></span>
          <?php } ?>
        </div>
      </div>

      <!-- ===== STAT TILES ===== -->
      <div class="br-stats">
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-calculator"></i> Billing (excl. transport)</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($bill_total, 2); ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-bus"></i> Transport</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($transport, 2); ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-file-text"></i> Grand total (full placement)</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($grand, 2); ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-calendar-check"></i> Accrued till today</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($accrued_total, 2); ?>
            <small>· <?php echo $period_count; ?> period<?php echo $period_count == 1 ? '' : 's'; ?></small></div></div>
      </div>

      <div class="row">
        <!-- ===== CHANGE BILLING ===== -->
        <div class="col-md-6">
          <div class="br-card">
            <div class="br-card-h"><i class="fa fa-exchange"></i> Change Billing Arrangement</div>
            <div class="br-card-b">
              <p class="br-hint">A change does not overwrite the previous arrangement — the old rate/frequency/transport
                stays in the billing history and still applies to its period. To correct the current arrangement,
                use the date it started<?php echo !empty($current) ? ' (' . date('d M Y', strtotime($current->effective_from)) . ')' : ''; ?>;
                the old one is kept in history marked as superseded.</p>
              <form method="post" action="<?php echo base_url('nurse/changeBilling'); ?>">
                <input type="hidden" name="assignment_id" value="<?php echo $assignment->id; ?>">
                <div class="row">
                  <div class="col-sm-6 br-field">
                    <label>Billing Frequency</label>
                    <select name="billing_frequency" class="form-control" required>
                      <?php foreach (array('Daily', 'Weekly', 'Monthly') as $f) { ?>
                        <option value="<?php echo $f; ?>" <?php echo $cur_freq == $f ? 'selected' : ''; ?>><?php echo $f; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                  <div class="col-sm-6 br-field">
                    <label>Rate (<?php echo $cur; ?>)</label>
                    <input type="number" step="0.01" min="0" name="rate" class="form-control" value="<?php echo $cur_rate !== null ? (float) $cur_rate : ''; ?>" required>
                  </div>
                  <div class="col-sm-6 br-field">
                    <label>Transport Frequency</label>
                    <select name="transport_frequency" id="bill_tfreq" class="form-control">
                      <option value="">-- None --</option>
                      <?php foreach (array('Daily' => 'Daily', 'Weekly' => 'Weekly', 'Monthly' => 'Monthly', 'Flexible' => 'Flexible Commute') as $v => $l) { ?>
                        <option value="<?php echo $v; ?>" <?php echo $cur_tfreq == $v ? 'selected' : ''; ?>><?php echo $l; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                  <div class="col-sm-6 br-field">
                    <label>Transport Charge (<?php echo $cur; ?>)</label>
                    <input type="number" step="0.01" min="0" name="transport_charge" class="form-control" value="<?php echo $cur_tcharge > 0 ? $cur_tcharge : ''; ?>">
                  </div>
                  <div class="col-sm-12 br-field" id="bill_tnote" style="<?php echo $cur_tfreq == 'Flexible' ? '' : 'display:none;'; ?>">
                    <label>Agreed Arrangement (Flexible Commute)</label>
                    <textarea name="transport_note" class="form-control" rows="2"><?php echo !empty($current) ? html_escape($current->transport_note) : ''; ?></textarea>
                  </div>
                </div>
                <div class="br-field">
                  <label>Effective From</label>
                  <input type="date" name="effective_from" class="form-control" required
                    min="<?php echo !empty($current) ? $current->effective_from : $assignment->start_date; ?>"
                    max="<?php echo $assignment->end_date; ?>">
                </div>
                <div class="br-field">
                  <label>Reason / Note</label>
                  <textarea name="note" class="form-control" rows="2" placeholder="e.g. patient agreement changed to weekly"></textarea>
                </div>
                <button type="submit" class="br-submit blue"><i class="fa fa-check"></i> Apply Change</button>
              </form>
            </div>
          </div>
        </div>

        <!-- ===== EXTEND SERVICE ===== -->
        <div class="col-md-6">
          <div class="br-card">
            <div class="br-card-h"><i class="fa fa-calendar-plus-o"></i> Extend Service Duration</div>
            <div class="br-card-b">
              <p class="br-hint">Change the placement end date. The current billing arrangement keeps applying to the new period.
                The change is recorded in the audit trail.</p>
              <form method="post" action="<?php echo base_url('nurse/extendService'); ?>">
                <input type="hidden" name="assignment_id" value="<?php echo $assignment->id; ?>">
                <div class="br-field">
                  <label>Current End Date</label>
                  <input type="text" class="form-control" value="<?php echo !empty($assignment->end_date) ? date('d M Y', strtotime($assignment->end_date)) : 'Ongoing'; ?>" disabled>
                </div>
                <div class="br-field">
                  <label>New End Date</label>
                  <input type="date" name="end_date" class="form-control"
                    value="<?php echo $assignment->end_date; ?>" min="<?php echo $assignment->start_date; ?>" required>
                </div>
                <div class="br-field">
                  <label>Reason / Note</label>
                  <input type="text" name="note" class="form-control" placeholder="e.g. family extended the placement">
                </div>
                <button type="submit" class="br-submit teal"><i class="fa fa-calendar-plus-o"></i> Extend Placement</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== BILLING HISTORY ===== -->
      <div class="br-card">
        <div class="br-card-h"><i class="fa fa-history"></i> Effective-Dated Billing History</div>
        <div class="br-card-b" style="padding:0;">
          <?php if (!empty($periods)) { ?>
            <table class="br-table">
              <thead><tr><th>Frequency</th><th>Rate</th><th>Transport</th><th>Effective From</th><th>Effective To</th><th>Changed By</th><th>Recorded</th><th>Note</th></tr></thead>
              <tbody>
                <?php foreach ($periods as $p) { ?>
                  <tr class="<?php echo !empty($p->is_superseded) ? 'superseded' : ''; ?>">
                    <td class="keep"><span class="br-badge"><?php echo $p->billing_frequency; ?></span>
                      <?php if (!empty($p->is_superseded)) { ?><br><span class="br-badge grey" style="margin-top:4px;">Superseded</span><?php } ?></td>
                    <td class="br-cur"><?php echo $cur; ?> <?php echo number_format($p->rate, 2); ?></td>
                    <td>
                      <?php if ($p->transport_charge > 0) { ?>
                        <?php echo $cur . ' ' . number_format($p->transport_charge, 2); ?>
                        <small style="color:#94a3b8;"><?php echo isset($t_unit[$p->transport_frequency]) ? $t_unit[$p->transport_frequency] : ''; ?></small>
                      <?php } else { echo '—'; } ?>
                    </td>
                    <td><?php echo date('d M Y', strtotime($p->effective_from)); ?></td>
                    <td><?php echo !empty($p->effective_to) ? date('d M Y', strtotime($p->effective_to)) : '<span class="br-ongoing">Ongoing</span>'; ?></td>
                    <td><?php echo html_escape($p->created_by_name); ?></td>
                    <td style="color:#94a3b8;"><?php echo !empty($p->created_at) ? date('d M Y H:i', strtotime($p->created_at)) : '—'; ?></td>
                    <td class="keep"><?php echo html_escape($p->note); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="br-empty">No billing history.</div>
          <?php } ?>
        </div>
      </div>

      <!-- ===== CHANGE LOG ===== -->
      <div class="br-card">
        <div class="br-card-h"><i class="fa fa-clipboard-list"></i> Change Log <span style="font-weight:400;color:#94a3b8;font-size:12px;">(previous &rarr; new, who and when)</span></div>
        <div class="br-card-b" style="padding:0;">
          <?php if (!empty($audit)) { ?>
            <table class="br-table">
              <thead><tr><th>Date / Time</th><th>Action</th><th>Previous</th><th>New</th><th>Changed By</th><th>Reason</th></tr></thead>
              <tbody>
                <?php foreach ($audit as $ev) { ?>
                  <tr>
                    <td style="white-space:nowrap;"><?php echo date('d M Y H:i', strtotime($ev->created_at)); ?></td>
                    <td class="keep"><?php echo html_escape($ev->action); ?></td>
                    <td class="keep"><?php echo $ev->old_value !== null && $ev->old_value !== '' ? html_escape($ev->old_value) : '—'; ?></td>
                    <td class="keep"><?php echo $ev->new_value !== null && $ev->new_value !== '' ? html_escape($ev->new_value) : '—'; ?></td>
                    <td><?php echo html_escape($ev->performed_by_name); ?></td>
                    <td class="keep"><?php echo html_escape((string) $ev->reason); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="br-empty">No changes recorded yet.</div>
          <?php } ?>
        </div>
      </div>

      <!-- ===== CALCULATED BILLING ===== -->
      <div class="br-card">
        <div class="br-card-h"><i class="fa fa-calculator"></i> Calculated Billing <span style="font-weight:400;color:#94a3b8;font-size:12px;">(per applicable period)</span></div>
        <div class="br-card-b" style="padding:0;">
          <?php if (!empty($breakdown['rows'])) { ?>
            <table class="br-table">
              <thead><tr><th>Period</th><th>Frequency</th><th>Rate</th><th>Days</th><th style="text-align:right;">Charge</th><th style="text-align:right;">Transport</th><th style="text-align:right;">Total</th></tr></thead>
              <tbody>
                <?php foreach ($breakdown['rows'] as $r) { ?>
                  <tr>
                    <td><?php echo date('d M Y', strtotime($r->seg_start)); ?> &ndash; <?php echo date('d M Y', strtotime($r->seg_end)); ?></td>
                    <td><span class="br-badge grey"><?php echo $r->billing_frequency; ?></span></td>
                    <td><?php echo $cur; ?> <?php echo number_format($r->rate, 2); ?></td>
                    <td><?php echo $r->days; ?></td>
                    <td style="text-align:right;"><?php echo $cur; ?> <?php echo number_format($r->charge, 2); ?></td>
                    <td style="text-align:right;">
                      <?php echo $r->transport > 0 ? $cur . ' ' . number_format($r->transport, 2) : '—'; ?>
                      <?php if ($r->transport_frequency) { ?><br><small style="color:#94a3b8;"><?php echo number_format($r->transport_charge, 2) . ' ' . $t_unit[$r->transport_frequency]; ?></small><?php } ?>
                    </td>
                    <td style="text-align:right;" class="br-cur"><?php echo $cur; ?> <?php echo number_format($r->total, 2); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr><td colspan="4" style="text-align:right;">Totals</td>
                  <td style="text-align:right;"><?php echo $cur; ?> <?php echo number_format($bill_total, 2); ?></td>
                  <td style="text-align:right;"><?php echo $cur; ?> <?php echo number_format($transport, 2); ?></td>
                  <td style="text-align:right;font-size:15px;" class="br-cur"><?php echo $cur; ?> <?php echo number_format($grand, 2); ?></td></tr>
              </tfoot>
            </table>
          <?php } else { ?>
            <div class="br-empty">No billing to calculate.</div>
          <?php } ?>
        </div>
        <div class="br-card-b" style="padding-top:0;">
          <p class="br-note"><i class="fa fa-info-circle"></i>
            Charges are pro-rata: weekly = days &times; rate / 7; monthly = whole months from the period start, plus
            remaining days pro-rated over that month. Daily/weekly/monthly transport is charged the same way; flexible
            transport is charged once per period. Historical periods use the rate/frequency/transport that applied at that time.</p>
        </div>
      </div>

    </div>
  </section>
</section>

<script>
  document.getElementById('bill_tfreq').addEventListener('change', function () {
    document.getElementById('bill_tnote').style.display = this.value === 'Flexible' ? '' : 'none';
  });
</script>
