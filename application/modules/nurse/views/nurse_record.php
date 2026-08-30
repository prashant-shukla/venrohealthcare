<?php
// ---- helpers for this view ----
$initials = '';
if (!empty($nurse->name)) {
    $parts = preg_split('/\s+/', trim($nurse->name));
    foreach ($parts as $p) { if ($p !== '' && strlen($initials) < 2) $initials .= strtoupper($p[0]); }
}

$today = date('Y-m-d');
$exp = $nurse->license_expiry_date;
$three = date('Y-m-d', strtotime('+3 months'));
if (empty($exp) || $exp == '0000-00-00') { $lic_state = 'none';   $lic_label = 'Not set'; }
elseif ($exp < $today)                    { $lic_state = 'exp';    $lic_label = 'Expired'; }
elseif ($exp <= $three)                   { $lic_state = 'soon';   $lic_label = 'Expiring soon'; }
else                                       { $lic_state = 'ok';     $lic_label = 'Valid'; }

$status = $nurse->status;
$is_active = !(isset($nurse->is_active) && $nurse->is_active == 0);

$assign_count = !empty($assignments) ? count($assignments) : 0;
$active_assign = 0;
if (!empty($assignments)) { foreach ($assignments as $a) { if (!isset($a->is_active) || $a->is_active == 1) $active_assign++; } }
$pay_total = 0; $pay_count = !empty($payments) ? count($payments) : 0;
if (!empty($payments)) { foreach ($payments as $p) { $pay_total += $p->amount; } }
$cur = isset($this->currency) ? $this->currency : '';
?>

<style>
.nr-wrap { padding: 6px 4px 30px; }
.nr-wrap a { text-decoration: none; }
.nr-back { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:13px; margin-bottom:14px; }
.nr-back:hover { color:#2c7be5; }

/* Hero */
.nr-hero { position:relative; background:linear-gradient(135deg,#2c7be5 0%,#1b5fbe 100%); border-radius:14px;
  padding:26px 28px; color:#fff; box-shadow:0 8px 24px rgba(44,123,229,.25); overflow:hidden; }
.nr-hero:after { content:""; position:absolute; right:-40px; top:-40px; width:180px; height:180px;
  background:rgba(255,255,255,.08); border-radius:50%; }
.nr-hero:before { content:""; position:absolute; right:60px; bottom:-70px; width:150px; height:150px;
  background:rgba(255,255,255,.06); border-radius:50%; }
.nr-hero-row { display:flex; align-items:center; gap:22px; flex-wrap:wrap; position:relative; z-index:1; }
.nr-avatar { width:88px; height:88px; border-radius:50%; background:rgba(255,255,255,.18); border:3px solid rgba(255,255,255,.5);
  display:flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; color:#fff;
  overflow:hidden; flex:0 0 auto; }
.nr-avatar img { width:100%; height:100%; object-fit:cover; }
.nr-id-block { flex:1 1 auto; min-width:220px; }
.nr-name { font-size:26px; font-weight:700; margin:0 0 6px; line-height:1.15; }
.nr-sub { font-size:13px; opacity:.9; display:flex; gap:18px; flex-wrap:wrap; }
.nr-sub span { display:inline-flex; align-items:center; gap:6px; }
.nr-pills { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
.nr-pill { font-size:12px; font-weight:600; padding:5px 12px; border-radius:20px; background:rgba(255,255,255,.16);
  display:inline-flex; align-items:center; gap:6px; }
.nr-pill i { font-size:11px; }
.nr-pill.ok    { background:#1f9d55; }
.nr-pill.soon  { background:#e8830c; }
.nr-pill.exp   { background:#e3342f; }
.nr-pill.none  { background:rgba(255,255,255,.16); }
.nr-pill.inact { background:#e3342f; }

.nr-note { font-size:12px; color:#94a3b8; margin:14px 2px 22px; display:flex; align-items:center; gap:7px; }

/* Stat tiles */
.nr-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }
@media (max-width:900px){ .nr-stats { grid-template-columns:repeat(2,1fr); } }
.nr-stat { background:#fff; border:1px solid #eef1f5; border-radius:12px; padding:16px 18px;
  box-shadow:0 1px 3px rgba(16,24,40,.06); display:flex; align-items:center; gap:14px; }
.nr-stat-ic { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:18px; flex:0 0 auto; }
.nr-stat-ic.blue  { background:#e8f1fe; color:#2c7be5; }
.nr-stat-ic.green { background:#e6f6ee; color:#1f9d55; }
.nr-stat-ic.amber { background:#fdf1e3; color:#e8830c; }
.nr-stat-ic.grey  { background:#eef1f5; color:#64748b; }
.nr-stat-v { font-size:20px; font-weight:700; color:#1e293b; line-height:1.1; }
.nr-stat-l { font-size:12px; color:#94a3b8; margin-top:2px; }

/* Cards */
.nr-card { background:#fff; border:1px solid #eef1f5; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.06);
  margin-bottom:22px; overflow:hidden; }
.nr-card-h { padding:15px 20px; border-bottom:1px solid #f1f4f8; font-size:14px; font-weight:700; color:#1e293b;
  display:flex; align-items:center; gap:9px; }
.nr-card-h i { color:#2c7be5; }
.nr-card-b { padding:20px; }

/* Definition grid */
.nr-dl { display:grid; grid-template-columns:1fr 1fr; gap:0 30px; }
@media (max-width:700px){ .nr-dl { grid-template-columns:1fr; } }
.nr-dl-row { display:flex; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:1px dashed #eef1f5; font-size:13px; }
.nr-dl-row .k { color:#94a3b8; }
.nr-dl-row .v { color:#1e293b; font-weight:600; text-align:right; }

/* Cert buttons */
.nr-cert { display:inline-flex; align-items:center; gap:9px; padding:12px 16px; border-radius:10px; border:1px solid #e6ebf2;
  color:#1e293b; font-size:13px; font-weight:600; margin:0 10px 10px 0; transition:.15s; background:#fbfcfe; }
.nr-cert:hover { border-color:#2c7be5; color:#2c7be5; box-shadow:0 2px 8px rgba(44,123,229,.12); }
.nr-cert i { font-size:18px; color:#2c7be5; }

/* Tables */
.nr-table { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
.nr-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#94a3b8;
  padding:10px 14px; border-bottom:1px solid #eef1f5; font-weight:700; }
.nr-table td { padding:12px 14px; border-bottom:1px solid #f4f6fa; color:#334155; }
.nr-table tr:last-child td { border-bottom:none; }
.nr-table tr:hover td { background:#fafbfe; }
.nr-badge { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; display:inline-block; }
.nr-badge.green { background:#e6f6ee; color:#1f9d55; }
.nr-badge.grey  { background:#eef1f5; color:#64748b; }
.nr-badge.blue  { background:#e8f1fe; color:#2c7be5; }

/* Timeline */
.nr-tl { position:relative; margin:0; padding:4px 0 0 4px; }
.nr-tl-item { position:relative; padding:0 0 20px 26px; border-left:2px solid #eef1f5; }
.nr-tl-item:last-child { border-left-color:transparent; padding-bottom:0; }
.nr-tl-dot { position:absolute; left:-7px; top:2px; width:12px; height:12px; border-radius:50%; background:#2c7be5; border:2px solid #fff; box-shadow:0 0 0 2px #e8f1fe; }
.nr-tl-act { font-size:13px; font-weight:700; color:#1e293b; }
.nr-tl-meta { font-size:12px; color:#94a3b8; margin-top:2px; }
.nr-tl-chg { font-size:12.5px; color:#475569; margin-top:5px; }
.nr-tl-chg .arw { color:#2c7be5; margin:0 4px; }
.nr-tl-reason { font-size:12px; color:#64748b; font-style:italic; margin-top:3px; }

.nr-empty { text-align:center; color:#94a3b8; font-size:13px; padding:26px 10px; }
.nr-empty i { display:block; font-size:26px; margin-bottom:8px; opacity:.5; }
</style>

<section id="main-content">
  <section class="wrapper site-min-height">
    <div class="nr-wrap">

      <a class="nr-back" href="<?php echo base_url('nurse'); ?>"><i class="fa fa-arrow-left"></i> Back to Nurses</a>

      <!-- ===== HERO ===== -->
      <div class="nr-hero">
        <div class="nr-hero-row">
          <div class="nr-avatar">
            <?php if (!empty($nurse->img_url)) { ?>
              <img src="<?php echo base_url($nurse->img_url); ?>" alt="">
            <?php } else { echo html_escape($initials ?: 'N'); } ?>
          </div>
          <div class="nr-id-block">
            <h1 class="nr-name"><?php echo html_escape($nurse->name); ?></h1>
            <div class="nr-sub">
              <?php if (!empty($nurse->email)) { ?><span><i class="fa fa-envelope"></i> <?php echo html_escape($nurse->email); ?></span><?php } ?>
              <?php if (!empty($nurse->phone)) { ?><span><i class="fa fa-phone"></i> <?php echo html_escape($nurse->phone); ?></span><?php } ?>
              <span><i class="fa fa-id-badge"></i> ID #<?php echo (int)$nurse->id; ?></span>
            </div>
            <div class="nr-pills">
              <?php if ($is_active) { ?>
                <span class="nr-pill ok"><i class="fa fa-circle"></i> <?php echo html_escape($status ?: 'Active'); ?></span>
              <?php } else { ?>
                <span class="nr-pill inact"><i class="fa fa-ban"></i> Deactivated / Archived</span>
              <?php } ?>
              <span class="nr-pill <?php echo $lic_state; ?>">
                <i class="fa fa-certificate"></i> Licence: <?php echo $lic_label; ?>
                <?php if ($lic_state !== 'none') echo ' &middot; ' . date('d M Y', strtotime($exp)); ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="nr-note">
        <i class="fa fa-info-circle"></i>
        This record remains available even when the nurse is no longer active, discontinued, or off placement.
      </div>

      <!-- ===== STAT TILES ===== -->
      <div class="nr-stats">
        <div class="nr-stat">
          <div class="nr-stat-ic blue"><i class="fa fa-user-md"></i></div>
          <div><div class="nr-stat-v"><?php echo $active_assign; ?></div><div class="nr-stat-l">Active assignments</div></div>
        </div>
        <div class="nr-stat">
          <div class="nr-stat-ic grey"><i class="fa fa-history"></i></div>
          <div><div class="nr-stat-v"><?php echo $assign_count; ?></div><div class="nr-stat-l">Total assignments</div></div>
        </div>
        <div class="nr-stat">
          <div class="nr-stat-ic green"><i class="fa fa-money"></i></div>
          <div><div class="nr-stat-v"><?php echo $cur; ?> <?php echo number_format($pay_total, 0); ?></div><div class="nr-stat-l">Paid (<?php echo $pay_count; ?> records)</div></div>
        </div>
        <div class="nr-stat">
          <div class="nr-stat-ic <?php echo $lic_state=='ok'?'green':($lic_state=='exp'?'grey':'amber'); ?>"><i class="fa fa-certificate"></i></div>
          <div><div class="nr-stat-v"><?php echo $lic_label; ?></div><div class="nr-stat-l">Licence status</div></div>
        </div>
      </div>

      <div class="row">
        <!-- ===== PROFILE ===== -->
        <div class="col-md-7">
          <div class="nr-card">
            <div class="nr-card-h"><i class="fa fa-user"></i> Profile Details</div>
            <div class="nr-card-b">
              <div class="nr-dl">
                <div class="nr-dl-row"><span class="k">Full name</span><span class="v"><?php echo html_escape($nurse->name); ?></span></div>
                <div class="nr-dl-row"><span class="k">Age / Sex</span><span class="v"><?php echo html_escape($nurse->age); ?> / <?php echo html_escape($nurse->sex); ?></span></div>
                <div class="nr-dl-row"><span class="k">Email</span><span class="v"><?php echo html_escape($nurse->email); ?></span></div>
                <div class="nr-dl-row"><span class="k">Phone</span><span class="v"><?php echo html_escape($nurse->phone); ?></span></div>
                <div class="nr-dl-row"><span class="k">Address</span><span class="v"><?php echo html_escape($nurse->address); ?></span></div>
                <div class="nr-dl-row"><span class="k">Availability</span><span class="v"><?php echo html_escape($nurse->availability); ?></span></div>
                <div class="nr-dl-row"><span class="k">Available days</span><span class="v"><?php echo !empty($nurse->available_days) ? html_escape($nurse->available_days) : '—'; ?></span></div>
                <div class="nr-dl-row"><span class="k">Current status</span><span class="v"><?php echo html_escape($status); ?></span></div>
              </div>
            </div>
          </div>
        </div>

        <!-- ===== CERTIFICATES ===== -->
        <div class="col-md-5">
          <div class="nr-card">
            <div class="nr-card-h"><i class="fa fa-certificate"></i> Certificates / Licences</div>
            <div class="nr-card-b">
              <?php if (!empty($nurse->nurse_license_pdf) || !empty($nurse->nurse_profile_pdf)) { ?>
                <?php if (!empty($nurse->nurse_license_pdf)) { ?>
                  <a class="nr-cert" href="<?php echo base_url($nurse->nurse_license_pdf); ?>" target="_blank"><i class="fa fa-certificate"></i> View Licence</a>
                <?php } ?>
                <?php if (!empty($nurse->nurse_profile_pdf)) { ?>
                  <a class="nr-cert" href="<?php echo base_url($nurse->nurse_profile_pdf); ?>" target="_blank"><i class="fa fa-file-pdf-o"></i> Profile Document</a>
                <?php } ?>
                <div style="clear:both"></div>
                <div style="margin-top:8px; font-size:12px; color:#94a3b8;">
                  Licence expiry:
                  <strong style="color:<?php echo $lic_state=='exp'?'#e3342f':($lic_state=='soon'?'#e8830c':($lic_state=='ok'?'#1f9d55':'#94a3b8')); ?>">
                    <?php echo $lic_state=='none' ? 'Not set' : date('d M Y', strtotime($exp)) . ' (' . $lic_label . ')'; ?>
                  </strong>
                </div>
              <?php } else { ?>
                <div class="nr-empty"><i class="fa fa-folder-open-o"></i> No certificates on file.</div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ===== ASSIGNMENTS ===== -->
      <div class="nr-card">
        <div class="nr-card-h"><i class="fa fa-user-md"></i> Patient Assignments (History)</div>
        <div class="nr-card-b" style="padding:0;">
          <?php if (!empty($assignments)) { ?>
            <table class="nr-table">
              <thead><tr><th>Patient</th><th>Associated Doctor</th><th>Role</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($assignments as $a) { ?>
                  <tr>
                    <td style="font-weight:600;color:#1e293b;"><?php echo html_escape($a->patient_name); ?></td>
                    <td>
                      <?php if (!empty($a->doctor_name)) { ?>
                        <i class="fa fa-user-md" style="color:#5b63d3;"></i> <?php echo html_escape($a->doctor_name); ?>
                      <?php } else { ?>
                        <span style="color:#94a3b8;">— none —</span>
                      <?php } ?>
                    </td>
                    <td><?php echo isset($a->assignment_role) ? html_escape($a->assignment_role) : '—'; ?></td>
                    <td><?php echo !empty($a->start_date) ? date('d M Y', strtotime($a->start_date)) : '—'; ?></td>
                    <td><?php echo !empty($a->end_date) ? date('d M Y', strtotime($a->end_date)) : 'Ongoing'; ?></td>
                    <td>
                      <?php if (isset($a->is_active) && $a->is_active == 0) { ?>
                        <span class="nr-badge grey">Removed</span>
                      <?php } else { ?>
                        <span class="nr-badge green">Active</span>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <?php } else { ?>
            <div class="nr-empty"><i class="fa fa-user-md"></i> No assignments recorded.</div>
          <?php } ?>
        </div>
      </div>

      <div class="row">
        <!-- ===== PAYMENTS ===== -->
        <div class="col-md-6">
          <div class="nr-card">
            <div class="nr-card-h"><i class="fa fa-money"></i> Professional Fee / Payments</div>
            <div class="nr-card-b" style="padding:0;">
              <?php if (!empty($payments)) { ?>
                <table class="nr-table">
                  <thead><tr><th>Date</th><th>Amount</th><th>Note</th></tr></thead>
                  <tbody>
                    <?php foreach ($payments as $p) { ?>
                      <tr>
                        <td><?php echo !empty($p->payment_date) ? date('d M Y', strtotime($p->payment_date)) : '—'; ?></td>
                        <td style="font-weight:700;color:#1f9d55;"><?php echo $cur; ?> <?php echo number_format($p->amount, 2); ?></td>
                        <td><?php echo html_escape($p->note); ?></td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              <?php } else { ?>
                <div class="nr-empty"><i class="fa fa-money"></i> No payment records.</div>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- ===== STATUS HISTORY (timeline) ===== -->
        <div class="col-md-6">
          <div class="nr-card">
            <div class="nr-card-h"><i class="fa fa-history"></i> Status History &amp; Activity</div>
            <div class="nr-card-b">
              <?php if (!empty($audit)) { ?>
                <div class="nr-tl">
                  <?php foreach ($audit as $ev) { ?>
                    <div class="nr-tl-item">
                      <span class="nr-tl-dot"></span>
                      <div class="nr-tl-act"><?php echo html_escape($ev->action); ?></div>
                      <div class="nr-tl-meta"><?php echo date('d M Y, H:i', strtotime($ev->created_at)); ?>
                        <?php echo !empty($ev->performed_by_name) ? ' &middot; by ' . html_escape($ev->performed_by_name) : ''; ?></div>
                      <?php if ($ev->old_value !== null || $ev->new_value !== null) { ?>
                        <div class="nr-tl-chg"><?php echo html_escape((string)$ev->old_value ?: '—'); ?>
                          <span class="arw">&rarr;</span><?php echo html_escape((string)$ev->new_value ?: '—'); ?></div>
                      <?php } ?>
                      <?php if (!empty($ev->reason)) { ?>
                        <div class="nr-tl-reason">“<?php echo html_escape($ev->reason); ?>”</div>
                      <?php } ?>
                    </div>
                  <?php } ?>
                </div>
              <?php } else { ?>
                <div class="nr-empty"><i class="fa fa-history"></i> No recorded activity yet.</div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</section>
