<?php
// $this->currency is provided via CI_Controller::__get() (HMVC), so read it
// directly rather than isset() which does not trigger the magic getter.
$cur = $this->currency;
$freq_unit = array('Daily' => 'day', 'Weekly' => 'week', 'Monthly' => 'month');
$cur_freq = !empty($current) ? $current->billing_frequency : null;
$cur_rate = !empty($current) ? $current->rate : null;
$bill_total = isset($breakdown['total']) ? $breakdown['total'] : 0;
$transport = !empty($assignment->transport_charge) ? $assignment->transport_charge : 0;
$grand = $bill_total + $transport;
$period_count = !empty($periods) ? count($periods) : 0;
?>

<style>
.br-wrap { padding:6px 4px 30px; }
.br-wrap a { text-decoration:none; }
.br-back { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:13px; margin-bottom:14px; }
.br-back:hover { color:#2c7be5; }

.br-flash { background:#e6f6ee; border:1px solid #b7e4c7; color:#1b7742; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:18px; display:flex; align-items:center; gap:9px; }
.br-flash i { color:#1f9d55; }

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
        </div>
      </div>

      <!-- ===== STAT TILES ===== -->
      <div class="br-stats">
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-calculator"></i> Billing (excl. transport)</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($bill_total, 2); ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-bus"></i> Transport</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($transport, 2); ?>
            <?php if (!empty($assignment->transport_frequency)) echo '<small>· ' . html_escape($assignment->transport_frequency) . '</small>'; ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-file-text"></i> Grand total</div>
          <div class="br-stat-v"><?php echo $cur; ?> <?php echo number_format($grand, 2); ?></div></div>
        <div class="br-stat"><div class="br-stat-l"><i class="fa fa-history"></i> Billing periods</div>
          <div class="br-stat-v"><?php echo $period_count; ?></div></div>
      </div>

      <div class="row">
        <!-- ===== CHANGE BILLING ===== -->
        <div class="col-md-6">
          <div class="br-card">
            <div class="br-card-h"><i class="fa fa-exchange"></i> Change Billing Arrangement</div>
            <div class="br-card-b">
              <p class="br-hint">A change does not overwrite the previous arrangement — the old rate/frequency
                stays in the billing history and still applies to its period.</p>
              <form method="post" action="<?php echo base_url('nurse/changeBilling'); ?>">
                <input type="hidden" name="assignment_id" value="<?php echo $assignment->id; ?>">
                <div class="br-field">
                  <label>New Billing Frequency</label>
                  <select name="billing_frequency" class="form-control" required>
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                  </select>
                </div>
                <div class="br-field">
                  <label>New Rate (<?php echo $cur; ?>)</label>
                  <input type="number" step="0.01" min="0" name="rate" class="form-control" required>
                </div>
                <div class="br-field">
                  <label>Effective From</label>
                  <input type="date" name="effective_from" class="form-control" required>
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
              <p class="br-hint">Extend the placement end date. The current billing arrangement keeps applying to the new period.</p>
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
              <thead><tr><th>Frequency</th><th>Rate</th><th>Effective From</th><th>Effective To</th><th>Changed By</th><th>Recorded</th><th>Note</th></tr></thead>
              <tbody>
                <?php foreach ($periods as $p) { ?>
                  <tr>
                    <td><span class="br-badge"><?php echo $p->billing_frequency; ?></span></td>
                    <td class="br-cur"><?php echo $cur; ?> <?php echo number_format($p->rate, 2); ?></td>
                    <td><?php echo date('d M Y', strtotime($p->effective_from)); ?></td>
                    <td><?php echo !empty($p->effective_to) ? date('d M Y', strtotime($p->effective_to)) : '<span class="br-ongoing">Ongoing</span>'; ?></td>
                    <td><?php echo html_escape($p->created_by_name); ?></td>
                    <td class="nl-muted" style="color:#94a3b8;"><?php echo !empty($p->created_at) ? date('d M Y H:i', strtotime($p->created_at)) : '—'; ?></td>
                    <td><?php echo html_escape($p->note); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="br-empty">No billing history.</div>
          <?php } ?>
        </div>
      </div>

      <!-- ===== CALCULATED BILLING ===== -->
      <div class="br-card">
        <div class="br-card-h"><i class="fa fa-calculator"></i> Calculated Billing <span style="font-weight:400;color:#94a3b8;font-size:12px;">(per applicable period)</span></div>
        <div class="br-card-b" style="padding:0;">
          <?php if (!empty($breakdown['rows'])) { ?>
            <table class="br-table">
              <thead><tr><th>Period</th><th>Frequency</th><th>Rate</th><th>Days</th><th style="text-align:right;">Charge</th></tr></thead>
              <tbody>
                <?php foreach ($breakdown['rows'] as $r) { ?>
                  <tr>
                    <td><?php echo date('d M Y', strtotime($r->seg_start)); ?> &ndash; <?php echo date('d M Y', strtotime($r->seg_end)); ?></td>
                    <td><span class="br-badge grey"><?php echo $r->billing_frequency; ?></span></td>
                    <td><?php echo $cur; ?> <?php echo number_format($r->rate, 2); ?></td>
                    <td><?php echo $r->days; ?></td>
                    <td style="text-align:right;" class="br-cur"><?php echo $cur; ?> <?php echo number_format($r->charge, 2); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr><td colspan="4" style="text-align:right;">Billing subtotal</td><td style="text-align:right;"><?php echo $cur; ?> <?php echo number_format($bill_total, 2); ?></td></tr>
                <?php if ($transport > 0) { ?>
                  <tr><td colspan="4" style="text-align:right;">Transport<?php echo !empty($assignment->transport_frequency) ? ' (' . html_escape($assignment->transport_frequency) . ')' : ''; ?></td><td style="text-align:right;"><?php echo $cur; ?> <?php echo number_format($transport, 2); ?></td></tr>
                  <tr><td colspan="4" style="text-align:right;font-size:15px;">Grand total</td><td style="text-align:right;font-size:15px;" class="br-cur"><?php echo $cur; ?> <?php echo number_format($grand, 2); ?></td></tr>
                <?php } ?>
              </tfoot>
            </table>
          <?php } else { ?>
            <div class="br-empty">No billing to calculate.</div>
          <?php } ?>
        </div>
        <div class="br-card-b" style="padding-top:0;">
          <p class="br-note"><i class="fa fa-info-circle"></i>
            Weekly/monthly charges are rounded up to whole billing periods (any part-week is charged as a full
            week, any part-month as a full month). Historical periods use the rate/frequency that applied at that time.</p>
        </div>
      </div>

    </div>
  </section>
</section>
