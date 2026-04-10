<?php
ini_set('session.cookie_path', '/');
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::redirectIfLoggedIn();

// Determine current step from session
$step = $_SESSION['fp_step'] ?? 'email';
$fpEmail = $_SESSION['fp_email'] ?? '';
$otpError = $_SESSION['fp_error'] ?? '';
unset($_SESSION['fp_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Forgot Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
  <link rel="stylesheet" href="assets/css/forgot-password.css">
</head>
<body>

<!-- Left Panel -->
<div class="auth-left" onclick="window.location.href='index.php'" title="Go to homepage">
  <img src="assets/images/dripnStylelogologinRegister.png" alt="Drip N Style">
</div>

<!-- Right Panel -->
<div class="auth-right">
  <div class="form-wrap">
    <div class="bar"></div>
    <div class="form-card">

      <p class="form-title">
        <?php if ($step === 'email'): ?>Forgot <span>Password</span>
        <?php elseif ($step === 'otp'): ?>Verify <span>Code</span>
        <?php elseif ($step === 'newpass'): ?>New <span>Password</span>
        <?php else: ?>Reset <span>Done</span>
        <?php endif; ?>
      </p>
      <p class="form-sub">
        <?php if ($step === 'email'): ?>Enter your registered email to receive a code
        <?php elseif ($step === 'otp'): ?>A 6-digit code was sent to <?= htmlspecialchars($fpEmail) ?>
        <?php elseif ($step === 'newpass'): ?>Create a strong new password
        <?php else: ?>Your password has been updated
        <?php endif; ?>
      </p>
      <div class="divider"></div>

      <!-- Step indicator dots -->
      <div class="step-indicator">
        <div class="step-dot <?= in_array($step, ['email','otp','newpass','success']) ? 'active' : '' ?>"></div>
        <div class="step-dot <?= in_array($step, ['otp','newpass','success']) ? 'active' : '' ?>"></div>
        <div class="step-dot <?= in_array($step, ['newpass','success']) ? 'active' : '' ?>"></div>
        <div class="step-dot <?= $step === 'success' ? 'active' : '' ?>"></div>
      </div>

      <!-- Error from PHP -->
      <?php if ($otpError): ?>
        <div class="fp-error"><?= htmlspecialchars($otpError) ?></div>
      <?php endif; ?>

      <!-- JS error placeholder -->
      <div class="fp-error" id="js-error" style="display:none;"></div>

      <!-- ── STEP 1: Email ── -->
      <?php if ($step === 'email'): ?>
      <form id="form-email" onsubmit="sendOTP(event)">
        <label class="field-label" for="fp-email">Email address</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
          </span>
          <input class="form-input" type="email" id="fp-email" name="email"
            placeholder="Enter your email" required autofocus>
        </div>
        <button type="submit" class="submit-btn" id="btn-send">Send Code</button>
      </form>
      <a class="back-link" href="LoginPage.php">&#8592; Back to Login</a>
      <?php endif; ?>

      <!-- ── STEP 2: OTP ── -->
      <?php if ($step === 'otp'): ?>

      <!-- Cooldown bar -->
      <div class="cooldown-bar-wrap" id="cooldown-wrap">
        <div class="cooldown-bar" id="cooldown-bar" style="width:100%;"></div>
      </div>

      <form id="form-otp" onsubmit="verifyOTP(event)">
        <label class="field-label" for="fp-otp">Verification code</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="text" id="fp-otp" name="otp"
            placeholder="000000" maxlength="6" inputmode="numeric" required autofocus
            oninput="this.value = this.value.replace(/\D/g,'')">
        </div>

        <div class="fp-resend">
          Didn't receive it?
          <button type="button" class="resend-btn" id="resend-btn" onclick="resendOTP()">Resend code</button>
          <span id="resend-timer" style="color:#b0a090;font-size:12px;"></span>
        </div>

        <button type="submit" class="submit-btn" id="btn-verify">Verify Code</button>
      </form>

      <a class="back-link" href="ForgotPasswordPage.php?reset=1">&#8592; Use a different email</a>

      <?php endif; ?>

      <!-- ── STEP 3: New Password ── -->
      <?php if ($step === 'newpass'): ?>
      <form id="form-pass" onsubmit="resetPassword(event)">
        <label class="field-label" for="fp-newpass">New password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="fp-newpass" placeholder="8–12 characters" maxlength="12" required oninput="checkStrength(this.value)">
          <button class="eye-btn" type="button" onclick="togglePass('fp-newpass',this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <!-- Password strength meter -->
        <div class="strength-wrap">
          <div class="strength-bars">
            <div class="sbar" id="sbar1"></div>
            <div class="sbar" id="sbar2"></div>
            <div class="sbar" id="sbar3"></div>
            <div class="sbar" id="sbar4"></div>
          </div>
          <span class="strength-label" id="strength-label"></span>
        </div>
        <ul class="strength-rules" id="strength-rules">
          <li id="rule-len">8–12 characters</li>
          <li id="rule-upper">One uppercase letter</li>
          <li id="rule-num">One number</li>
          <li id="rule-sym">One special character</li>
        </ul>

        <label class="field-label" for="fp-confirm">Confirm password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="fp-confirm" placeholder="Re-enter password" required>
          <button class="eye-btn" type="button" onclick="togglePass('fp-confirm',this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <button type="submit" class="submit-btn" id="btn-reset">Reset Password</button>
      </form>
      <?php endif; ?>

      <!-- ── STEP 4: Success ── -->
      <?php if ($step === 'success'): ?>
      <div class="fp-success-icon">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#b8934a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
        </svg>
      </div>
      <p class="fp-success-title">Password Reset!</p>
      <p class="fp-desc" style="text-align:center;margin-bottom:24px;">
        Your password has been updated successfully.
      </p>
      <a href="LoginPage.php" class="submit-btn" style="display:block;text-align:center;text-decoration:none;line-height:42px;padding:0;">
        Go to Login
      </a>
      <?php endif; ?>

    </div><!-- /.form-card -->
  </div>
</div>

<script>
const CONTROLLER = '/DRIP-N-STYLE/App/Controllers/ForgotPasswordController.php';
const OTP_COOLDOWN = 60; // seconds before resend is allowed
const OTP_TIMEOUT  = 10 * 60; // 10 min OTP lifetime shown as countdown

function showError(msg) {
  const el = document.getElementById('js-error');
  el.textContent = msg;
  el.style.display = 'block';
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearError() {
  const el = document.getElementById('js-error');
  el.textContent = '';
  el.style.display = 'none';
}

function togglePass(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.style.color = isText ? '#b0a090' : '#b8934a';
}

// ── Resend cooldown timer ─────────────────────────────────────────────────
function startCooldown(seconds) {
  const btn   = document.getElementById('resend-btn');
  const timer = document.getElementById('resend-timer');
  const wrap  = document.getElementById('cooldown-wrap');
  const bar   = document.getElementById('cooldown-bar');
  if (!btn) return;

  btn.disabled = true;
  btn.style.display = 'none';
  if (wrap) { wrap.style.display = 'block'; bar.style.width = '100%'; }

  let remaining = seconds;
  const tick = setInterval(() => {
    remaining--;
    if (timer) timer.textContent = `Resend in ${remaining}s`;
    if (bar) bar.style.width = ((remaining / seconds) * 100) + '%';

    if (remaining <= 0) {
      clearInterval(tick);
      btn.disabled = false;
      btn.style.display = 'inline';
      if (timer) timer.textContent = '';
      if (wrap) wrap.style.display = 'none';
    }
  }, 1000);
}

// ── Step 1: Send OTP ──────────────────────────────────────────────────────
async function sendOTP(e) {
  e.preventDefault();
  clearError();
  const email = document.getElementById('fp-email').value.trim();
  const btn   = document.getElementById('btn-send');

  btn.disabled = true;
  btn.textContent = 'Sending...';

  try {
    const res  = await fetch(CONTROLLER + '?action=send_otp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'email=' + encodeURIComponent(email)
    });
    const data = await res.json();

    if (data.success) {
      window.location.reload(); // Server already set session fp_step = otp
    } else {
      showError(data.message || 'Something went wrong.');
      btn.disabled = false;
      btn.textContent = 'Send Code';
    }
  } catch (err) {
    showError('Network error. Please try again.');
    btn.disabled = false;
    btn.textContent = 'Send Code';
  }
}

// ── Step 2: Resend ────────────────────────────────────────────────────────
async function resendOTP() {
  clearError();
  startCooldown(OTP_COOLDOWN);

  try {
    const res  = await fetch(CONTROLLER + '?action=resend_otp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    });
    const data = await res.json();
    if (!data.success) showError(data.message || 'Could not resend. Try again.');
  } catch (err) {
    showError('Network error. Please try again.');
  }
}

// ── Step 2: Verify OTP ────────────────────────────────────────────────────
async function verifyOTP(e) {
  e.preventDefault();
  clearError();
  const otp = document.getElementById('fp-otp').value.trim();
  const btn = document.getElementById('btn-verify');

  if (otp.length < 6) return showError('Please enter the full 6-digit code.');

  btn.disabled = true;
  btn.textContent = 'Verifying...';

  try {
    const res  = await fetch(CONTROLLER + '?action=verify_otp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'otp=' + encodeURIComponent(otp)
    });
    const data = await res.json();

    if (data.success) {
      window.location.reload();
    } else {
      showError(data.message || 'Invalid code.');
      btn.disabled = false;
      btn.textContent = 'Verify Code';
    }
  } catch (err) {
    showError('Network error. Please try again.');
    btn.disabled = false;
    btn.textContent = 'Verify Code';
  }
}

// ── Step 3: Reset Password ────────────────────────────────────────────────
function checkStrength(val) {
  const len    = val.length >= 8 && val.length <= 12;
  const upper  = /[A-Z]/.test(val);
  const num    = /[0-9]/.test(val);
  const sym    = /[^A-Za-z0-9]/.test(val);
  const passed = [len, upper, num, sym].filter(Boolean).length;

  // Rule indicators
  const rules = { 'rule-len': len, 'rule-upper': upper, 'rule-num': num, 'rule-sym': sym };
  for (const [id, ok] of Object.entries(rules)) {
    document.getElementById(id).className = ok ? 'pass' : '';
  }

  // Bars
  const bars   = ['sbar1','sbar2','sbar3','sbar4'];
  const levels = ['','weak','fair','good','strong'];
  const colors = { weak: '#e57373', fair: '#f4a261', good: '#b8934a', strong: '#4caf50' };
  const labels = { 0: '', 1: 'Weak', 2: 'Fair', 3: 'Good', 4: 'Strong' };

  bars.forEach((id, i) => {
    const el = document.getElementById(id);
    el.className = 'sbar ' + (i < passed ? levels[passed] : '');
  });

  const labelEl = document.getElementById('strength-label');
  labelEl.textContent = labels[passed] || '';
  labelEl.style.color = colors[levels[passed]] || '#9a8a7c';
}

async function resetPassword(e) {
  e.preventDefault();
  clearError();
  const pass    = document.getElementById('fp-newpass').value;
  const confirm = document.getElementById('fp-confirm').value;
  const btn     = document.getElementById('btn-reset');

  if (pass.length < 8 || pass.length > 12) return showError('Password must be between 8 and 12 characters.');
  if (!/[A-Z]/.test(pass))        return showError('Password must include at least one uppercase letter.');
  if (!/[0-9]/.test(pass))        return showError('Password must include at least one number.');
  if (!/[^A-Za-z0-9]/.test(pass)) return showError('Password must include at least one special character.');
  if (pass !== confirm)            return showError('Passwords do not match.');

  btn.disabled = true;
  btn.textContent = 'Saving...';

  try {
    const res  = await fetch(CONTROLLER + '?action=reset_password', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'password=' + encodeURIComponent(pass)
    });
    const data = await res.json();

    if (data.success) {
      window.location.reload();
    } else {
      showError(data.message || 'Could not reset password.');
      btn.disabled = false;
      btn.textContent = 'Reset Password';
    }
  } catch (err) {
    showError('Network error. Please try again.');
    btn.disabled = false;
    btn.textContent = 'Reset Password';
  }
}

// ── Auto-start cooldown if page reloaded on OTP step ─────────────────────
<?php if ($step === 'otp'): ?>
(function () {
  const sentAt  = <?= $_SESSION['fp_otp_sent_at'] ?? 0 ?>;
  const elapsed = Math.floor(Date.now() / 1000) - sentAt;
  const remaining = OTP_COOLDOWN - elapsed;
  if (remaining > 0) startCooldown(remaining);
})();
<?php endif; ?>
</script>

</body>
</html>