<?php
/**
 * Help Desk — Account Recovery Console
 * 
 * Admin-only page. Provides a three-step workflow:
 *  1. Customer lookup by email
 *  2. Identity verification (min. 2 of 4 factors)
 *  3. Status management: unlock OR reactivate
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../App/Config/auth.php';
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

$title = 'Help Desk — Account Recovery';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/sidebar.css">
  <style>
    /* ── Layout ── */
    .hd-wrap   { max-width: 860px; margin: 0 auto; padding: 28px 16px 60px; }
    .step-card { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:28px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,.06); }
    .step-card.locked  { opacity:.45; pointer-events:none; }
    .step-badge { display:inline-flex; align-items:center; justify-content:center;
                  width:32px; height:32px; border-radius:50%; font-weight:700; font-size:.85rem;
                  background:#b8934a; color:#fff; margin-right:10px; flex-shrink:0; }
    .step-title { font-size:1.1rem; font-weight:600; color:#2d2520; display:flex; align-items:center; margin-bottom:18px; }
    /* ── Factor checkboxes ── */
    .factor-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f0ece6; }
    .factor-row:last-child { border:none; }
    .factor-input { flex:1; display:none; }
    .factor-input.visible { display:block; }
    /* ── Status badge ── */
    .badge-locked     { background:#dc3545; }
    .badge-suspended  { background:#ffc107; color:#212529; }
    .badge-active     { background:#198754; }
    /* ── Audit log ── */
    .log-table th { font-size:.78rem; text-transform:uppercase; color:#888; background:#f9f6f2; }
    .log-table td { font-size:.82rem; vertical-align:middle; }
    /* ── Warning banner ── */
    .security-banner { background:#fff8e6; border:1px solid #f0d080; border-radius:8px; padding:12px 18px;
                        font-size:.83rem; color:#7a5800; margin-bottom:22px; }
  </style>
</head>
<body>

<!-- Topbar (mobile) -->
<div class="topbar d-lg-none bg-dark text-white px-3 py-2 shadow-sm">
  <button id="sidebarToggle" class="btn btn-warning me-3">☰</button>
  <span class="fw-bold">Drip N' Style</span>
</div>

<?php include __DIR__ . '/layout/partials/sidebar.php'; ?>
<div class="sidebar-backdrop"></div>

<div class="main-content">
  <div class="hd-wrap">

    <div class="d-flex align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-0" style="color:#2d2520;">🛡️ Help Desk — Account Recovery</h2>
        <p class="text-muted mb-0 mt-1" style="font-size:.9rem;">Restricted to Status Management actions only. All actions are audited.</p>
      </div>
    </div>

    <!-- Security notice -->
    <div class="security-banner">
      ⚠️ <strong>Security Notice:</strong> You are operating under the Principle of Least Privilege.
      Passwords are <strong>never visible</strong> to agents. All actions are recorded in the audit log.
      A minimum of <strong>two (2) identity factors</strong> must be verified before any account action is unlocked.
    </div>

    <!-- Global alert area -->
    <div id="globalAlert" class="d-none alert" role="alert"></div>

    <!-- ── STEP 1: Customer Lookup ── -->
    <div class="step-card" id="stepLookup">
      <div class="step-title"><span class="step-badge">1</span> Customer Lookup</div>
      <p class="text-muted small mb-3">Enter the customer's registered email address to begin.</p>
      <div class="input-group" style="max-width:480px;">
        <input type="email" id="customerEmail" class="form-control" placeholder="customer@email.com" autocomplete="off">
        <button class="btn btn-warning fw-semibold" id="btnLookup" onclick="doLookup()">Search Account</button>
      </div>
      <div id="lookupSpinner" class="d-none mt-2 text-muted small">🔍 Searching…</div>
    </div>

    <!-- ── STEP 2: Identity Verification ── (locked until lookup succeeds) -->
    <div class="step-card locked" id="stepVerify">
      <div class="step-title"><span class="step-badge">2</span> Identity Verification
        <span class="badge bg-danger ms-2" style="font-size:.72rem;">MIN. 2 FACTORS REQUIRED</span>
      </div>

      <!-- Customer summary (populated by JS) -->
      <div id="customerSummary" class="d-none mb-3 p-3 rounded" style="background:#f9f6f2;border:1px solid #ede5d8;">
        <table class="table table-sm table-borderless mb-0" style="font-size:.88rem;">
          <tbody>
            <tr><th width="150">Name</th>     <td id="cs-name">—</td></tr>
            <tr><th>Email</th>    <td id="cs-email">—</td></tr>
            <tr><th>Account Status</th> <td id="cs-status">—</td></tr>
            <tr><th>Lock Status</th>    <td id="cs-lock">—</td></tr>
            <tr><th>Member Since</th>   <td id="cs-since">—</td></tr>
          </tbody>
        </table>
      </div>

      <!-- Factor selection -->
      <p class="text-muted small mb-2">Select <strong>at least two</strong> factors and enter the customer-provided values:</p>
      <div id="factorList">
        <!-- Populated by JS -->
      </div>
      <button class="btn btn-primary mt-3 fw-semibold" id="btnVerify" onclick="doVerify()" disabled>
        🔒 Verify Identity
      </button>
      <div id="verifySpinner" class="d-none mt-2 text-muted small">🔎 Verifying…</div>
    </div>

    <!-- ── STEP 3: Account Actions ── (locked until verification succeeds) -->
    <div class="step-card locked" id="stepActions">
      <div class="step-title"><span class="step-badge">3</span> Status Management
        <span class="badge bg-success ms-2" style="font-size:.72rem;">RESTRICTED TO STATUS ONLY</span>
      </div>

      <!-- Verified factors display -->
      <div id="verifiedBanner" class="d-none alert alert-success py-2 mb-3" style="font-size:.85rem;">
        ✅ Identity verified via: <span id="verifiedFactorsList"></span>
      </div>

      <!-- Action buttons -->
      <div class="row g-3" id="actionPanel">
        <div class="col-md-6">
          <div class="card h-100 border-danger">
            <div class="card-body">
              <h6 class="card-title text-danger fw-bold">🔓 Unlock Account</h6>
              <p class="card-text small text-muted">For accounts locked due to failed login attempts. Resets the lockout timer and failed attempt counter.</p>
              <button class="btn btn-outline-danger btn-sm fw-semibold" id="btnUnlock" onclick="doAction('unlock')">Unlock Account</button>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card h-100 border-warning">
            <div class="card-body">
              <h6 class="card-title text-warning fw-bold">🔄 Reset Suspended Status</h6>
              <p class="card-text small text-muted">Restores a suspended account to active status. Only applicable when status is "Suspended".</p>
              <button class="btn btn-outline-warning btn-sm fw-semibold" id="btnReactivate" onclick="doAction('reactivate')">Reactivate Account</button>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3 p-2 rounded small text-muted" style="background:#f8f8f8;font-size:.78rem;">
        🛡️ Upon completing any action, the system will automatically:
        (1) set a <em>force_password_change</em> flag on the account,
        and (2) send a secure password-reset link to the customer's verified email address.
        The password is <strong>never</strong> shown to agents.
      </div>

      <div id="actionSpinner" class="d-none mt-3 text-muted small">⚙️ Processing…</div>

      <!-- Order history (for context only) -->
      <hr class="my-4">
      <h6 class="fw-semibold text-muted mb-2" style="font-size:.85rem;">📦 Recent Order History (Read-Only — For Verification Context)</h6>
      <div id="orderHistory" class="d-none">
        <table class="table table-sm log-table">
          <thead><tr><th>Order ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody id="orderTbody"></tbody>
        </table>
      </div>
    </div>

    <!-- ── Audit Log Panel ── -->
    <div class="step-card d-none" id="auditLogPanel">
      <div class="step-title"><span class="step-badge" style="background:#6c757d;">📋</span> Audit Log — This Account</div>
      <table class="table table-sm log-table">
        <thead>
          <tr>
            <th>Log ID</th><th>Agent</th><th>Action</th><th>Factors Verified</th><th>Timestamp</th>
          </tr>
        </thead>
        <tbody id="auditTbody"></tbody>
      </table>
      <p id="noLogsMsg" class="text-muted small d-none">No prior helpdesk actions recorded for this account.</p>
    </div>

  </div><!-- /.hd-wrap -->
</div><!-- /.main-content -->

<!-- Sidebar toggle script -->
<script>
const sidebar  = document.querySelector('.sidebar');
const backdrop = document.querySelector('.sidebar-backdrop');
const toggleBtn = document.getElementById('sidebarToggle');
if (toggleBtn) {
  toggleBtn.addEventListener('click', () => { sidebar.classList.toggle('active'); backdrop.classList.toggle('active'); });
  backdrop.addEventListener('click', () => { sidebar.classList.remove('active'); backdrop.classList.remove('active'); });
}
</script>

<!-- ── Help Desk JS ── -->
<script>
const CTRL = '/DRIP-N-STYLE/App/Controllers/HelpdeskController.php';

// ── Utility ──────────────────────────────────────────────────────────────────

function showAlert(msg, type = 'danger') {
  const el = document.getElementById('globalAlert');
  el.className = `alert alert-${type}`;
  el.textContent = msg;
  el.classList.remove('d-none');
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
function clearAlert() {
  const el = document.getElementById('globalAlert');
  el.classList.add('d-none');
  el.textContent = '';
}
function setLocked(id, locked) {
  const el = document.getElementById(id);
  if (locked) el.classList.add('locked'); else el.classList.remove('locked');
}
function spin(id, on) {
  document.getElementById(id).classList.toggle('d-none', !on);
}

// ── STEP 1: Lookup ────────────────────────────────────────────────────────────

async function doLookup() {
  clearAlert();
  const email = document.getElementById('customerEmail').value.trim();
  if (!email) { showAlert('Please enter a customer email.'); return; }

  document.getElementById('btnLookup').disabled = true;
  spin('lookupSpinner', true);

  const fd = new FormData();
  fd.append('action', 'lookup');
  fd.append('customer_email', email);

  try {
    const res  = await fetch(CTRL, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.success) { showAlert(json.message); return; }

    populateCustomer(json.data.customer);
    buildFactors(json.data.factors);
    buildOrders(json.data.orders);
    setLocked('stepVerify', false);
    setLocked('stepActions', true);
    document.getElementById('stepVerify').scrollIntoView({ behavior:'smooth' });
  } catch(e) {
    showAlert('A network error occurred. Please try again.');
  } finally {
    document.getElementById('btnLookup').disabled = false;
    spin('lookupSpinner', false);
  }
}

function populateCustomer(c) {
  document.getElementById('cs-name').textContent  = c.name;
  document.getElementById('cs-email').textContent = c.email;

  // Status badge
  const statusMap = { active: 'success', inactive: 'secondary', suspended: 'warning text-dark' };
  const sBadge = statusMap[c.status] ?? 'secondary';
  document.getElementById('cs-status').innerHTML = `<span class="badge bg-${sBadge}">${capitalize(c.status)}</span>`;

  // Lock badge
  const isLocked = c.locked_until && new Date(c.locked_until) > new Date();
  const lockHtml = isLocked
    ? `<span class="badge badge-locked bg-danger">Locked (until ${formatDate(c.locked_until)})</span> <small class="text-muted">[${c.failed_attempts} failed attempts]</small>`
    : `<span class="badge bg-success">Not Locked</span>`;
  document.getElementById('cs-lock').innerHTML = lockHtml;

  document.getElementById('cs-since').textContent = formatDate(c.date_created);
  document.getElementById('customerSummary').classList.remove('d-none');
}

// ── STEP 2: Factor Builder ────────────────────────────────────────────────────

const FACTOR_LABELS = {
  full_name:       { label: 'Registered Full Name',  placeholder: 'e.g. Maria Santos', type: 'text' },
  email_address:   { label: 'Email Address',          placeholder: 'customer@email.com', type: 'email' },
  last_order_id:   { label: 'Last Order ID',          placeholder: 'e.g. 42', type: 'number' },
  last_order_date: { label: 'Date of Last Purchase',  placeholder: '', type: 'date' },
};

function buildFactors(factors) {
  const container = document.getElementById('factorList');
  container.innerHTML = '';

  for (const [key, label] of Object.entries(factors)) {
    const cfg = FACTOR_LABELS[key] || { label, placeholder: '', type: 'text' };
    container.insertAdjacentHTML('beforeend', `
      <div class="factor-row">
        <input type="checkbox" class="form-check-input factor-cb" id="cb_${key}" value="${key}"
               onchange="onFactorToggle(this)">
        <label class="form-check-label fw-semibold" for="cb_${key}" style="min-width:220px;">${cfg.label}</label>
        <input type="${cfg.type}" class="form-control form-control-sm factor-input" id="fi_${key}"
               placeholder="${cfg.placeholder}" style="max-width:260px;">
      </div>
    `);
  }
}

function onFactorToggle(cb) {
  const input = document.getElementById('fi_' + cb.value);
  input.classList.toggle('visible', cb.checked);
  if (!cb.checked) input.value = '';
  updateVerifyBtn();
}

function updateVerifyBtn() {
  const checked = document.querySelectorAll('.factor-cb:checked').length;
  document.getElementById('btnVerify').disabled = checked < 2;
}

// ── STEP 2: Verify ────────────────────────────────────────────────────────────

async function doVerify() {
  clearAlert();
  const checks = [...document.querySelectorAll('.factor-cb:checked')];
  if (checks.length < 2) { showAlert('Select at least two factors.'); return; }

  document.getElementById('btnVerify').disabled = true;
  spin('verifySpinner', true);

  const fd = new FormData();
  fd.append('action', 'verify');
  checks.forEach(cb => {
    fd.append('factors[]', cb.value);
    fd.append(`factor_${cb.value}`, document.getElementById(`fi_${cb.value}`).value.trim());
  });

  try {
    const res  = await fetch(CTRL, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.success) { showAlert(json.message); return; }

    // Show verified banner
    const factorList = json.data.verified_factors.join(', ');
    document.getElementById('verifiedFactorsList').textContent = factorList;
    document.getElementById('verifiedBanner').classList.remove('d-none');

    setLocked('stepActions', false);
    document.getElementById('stepActions').scrollIntoView({ behavior:'smooth' });
    showAlert('✅ Identity verification successful! You may now perform account actions.', 'success');
  } catch(e) {
    showAlert('A network error occurred during verification.');
  } finally {
    document.getElementById('btnVerify').disabled = false;
    spin('verifySpinner', false);
  }
}

// ── STEP 3: Action ────────────────────────────────────────────────────────────

async function doAction(type) {
  clearAlert();
  const label = type === 'unlock' ? 'unlock this account' : 'reactivate this account';
  if (!confirm(`Are you sure you want to ${label}? This action will be logged.`)) return;

  document.getElementById('btnUnlock').disabled      = true;
  document.getElementById('btnReactivate').disabled  = true;
  spin('actionSpinner', true);

  const fd = new FormData();
  fd.append('action', type);

  try {
    const res  = await fetch(CTRL, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.success) { showAlert(json.message); return; }

    const emailNote = json.data.email_sent
      ? `A password-reset link has been sent to <strong>${json.data.customer_email}</strong>.`
      : `⚠️ Email delivery failed. Please inform the customer manually to use the Forgot Password flow.`;

    showAlert(`✅ Action complete: <strong>${json.data.action_type}</strong>. ${emailNote}`, 'success');

    // Reset UI back to Step 1
    setTimeout(() => { location.reload(); }, 4500);
  } catch(e) {
    showAlert('A network error occurred while applying the action.');
  } finally {
    spin('actionSpinner', false);
  }
}

// ── Orders ────────────────────────────────────────────────────────────────────

function buildOrders(orders) {
  const tbody = document.getElementById('orderTbody');
  tbody.innerHTML = '';
  if (!orders || orders.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="text-muted">No orders found.</td></tr>';
  } else {
    orders.forEach(o => {
      tbody.insertAdjacentHTML('beforeend', `
        <tr>
          <td>#${o.order_id}</td>
          <td>${formatDate(o.order_date)}</td>
          <td>₱${parseFloat(o.total_amount).toFixed(2)}</td>
          <td><span class="badge bg-secondary">${o.order_status}</span></td>
        </tr>
      `);
    });
  }
  document.getElementById('orderHistory').classList.remove('d-none');
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function formatDate(str) {
  if (!str) return '—';
  return new Date(str).toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' });
}
function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// Allow Enter key on email input
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('customerEmail').addEventListener('keydown', e => {
    if (e.key === 'Enter') doLookup();
  });
});
</script>

<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
</body>
</html>
