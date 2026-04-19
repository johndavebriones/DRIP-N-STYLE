<?php
ini_set('session.cookie_path', '/');
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::redirectIfLoggedIn();

// Handle ?reset=1 — go back to form step
if (isset($_GET['reset'])) {
    unset($_SESSION['reg_step'], $_SESSION['reg_pending'], $_SESSION['reg_otp'],
          $_SESSION['reg_otp_expiry'], $_SESSION['reg_otp_sent_at']);
    header('Location: RegisterPage.php');
    exit;
}

// Redirect to login on success
if (!empty($_SESSION['reg_success'])) {
    unset($_SESSION['reg_success']);
    $_SESSION['success'] = 'Account created! You can now log in.';
    header('Location: LoginPage.php');
    exit;
}

$step    = $_SESSION['reg_step'] ?? 'form';
$pending = $_SESSION['reg_pending'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | <?= $step === 'otp' ? 'Verify Email' : 'Register' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/register.css">
  <style>
    /* ── Password Strength ── */
    .strength-bar-wrap { display:flex; gap:5px; margin-top:7px; margin-bottom:2px; }
    .strength-seg { flex:1; height:3px; border-radius:99px; background:#e8dfd4; transition:background .35s; }
    .strength-label { font-size:11px; font-weight:500; letter-spacing:.04em; margin-bottom:10px; min-height:15px; transition:color .3s; }
    .str-0 .strength-seg { background:#e8dfd4; }
    .str-1 .strength-seg:nth-child(1) { background:#d85a30; }
    .str-1 .strength-seg:nth-child(n+2) { background:#e8dfd4; }
    .str-2 .strength-seg:nth-child(1), .str-2 .strength-seg:nth-child(2) { background:#e09c2a; }
    .str-2 .strength-seg:nth-child(n+3) { background:#e8dfd4; }
    .str-3 .strength-seg:nth-child(1), .str-3 .strength-seg:nth-child(2), .str-3 .strength-seg:nth-child(3) { background:#b8934a; }
    .str-3 .strength-seg:nth-child(4) { background:#e8dfd4; }
    .str-4 .strength-seg { background:#3a8a50; }

    /* ── DOB ── */
    .dob-inputs { display:flex; gap:8px; }
    .dob-inputs input { flex:1; text-align:center; }
    .field-sub { font-size:11px; color:#a89880; line-height:1.5; margin-top:6px; margin-bottom:4px; }
    .age-msg { font-size:12px; font-weight:500; min-height:16px; margin-top:4px; margin-bottom:8px; transition:color .3s; }
    .age-msg.error { color:#d85a30; }
    .age-msg.minor { color:#b8934a; }

    /* ── Parental Consent ── */
    #parental-wrap { display:none; align-items:flex-start; gap:10px; background:#fdf7ef; border:1px solid #e8c97a; border-radius:8px; padding:12px 14px; margin-bottom:14px; animation:fadeSlideIn .3s ease; }
    #parental-wrap.visible { display:flex; }
    @keyframes fadeSlideIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
    .consent-check { width:16px; height:16px; flex-shrink:0; accent-color:#b8934a; margin-top:2px; cursor:pointer; }
    .consent-label { font-size:12px; color:#6b5a45; line-height:1.55; cursor:pointer; }
    .consent-label strong { color:#b8934a; font-weight:600; }
    .match-msg { font-size:12px; min-height:16px; margin-top:-10px; margin-bottom:10px; }

    /* ── OTP Step ── */
    .otp-desc { font-size:13px; color:#6b5e54; line-height:1.7; margin:0 0 18px; }
    .otp-email-badge {
      background:#f2ece4; border:1px solid #e0d5c8; border-radius:3px;
      padding:10px 14px; font-size:12px; color:#5c4f44; margin-bottom:18px;
      display:flex; align-items:center; gap:8px;
    }
    .otp-email-badge span { color:#b8934a; font-weight:500; flex:1; word-break:break-all; }
    .fp-resend { font-size:12px; color:#8a7060; margin:8px 0 14px; display:flex; align-items:center; gap:6px; }
    .resend-btn { background:none; border:none; color:#b8934a; font-size:12px; cursor:pointer; padding:0; text-decoration:underline; }
    .resend-btn:disabled { color:#b0a090; cursor:default; text-decoration:none; }
    .cooldown-bar-wrap { height:3px; background:#e8e0d8; border-radius:2px; margin-bottom:14px; overflow:hidden; display:none; }
    .cooldown-bar { height:100%; background:linear-gradient(90deg,#b8934a,#d4a84b); border-radius:2px; transition:width 1s linear; }
    .back-link { display:block; text-align:center; font-size:12px; color:#9a8a7c; margin-top:16px; text-decoration:none; cursor:pointer; }
    .back-link:hover { color:#b8934a; }

    /* ── JS error ── */
    .js-error { background:#fff3f3; border-left:3px solid #e57373; color:#c62828; font-size:12px; padding:9px 12px; margin-bottom:14px; border-radius:2px; display:none; }

    /* ── Step dots ── */
    .step-dots { display:flex; gap:6px; margin-bottom:22px; }
    .step-dot { height:3px; flex:1; border-radius:2px; background:#e8e0d8; transition:background .3s; }
    .step-dot.active { background:#b8934a; }
  </style>
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

      <?php if ($step === 'form'): ?>
      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- STEP 1: Registration Form                                        -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <p class="form-title">Create <span>Account</span></p>
      <p class="form-sub">Join the Drip N' Style family</p>
      <div class="divider"></div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-warn"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <div class="js-error" id="js-error"></div>

      <div id="form-step">
        <!-- Full Name -->
        <label class="field-label" for="name">Full name</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </span>
          <input class="form-input" type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>

        <!-- Email -->
        <label class="field-label" for="email">Email address</label>
        <div class="field" id="email-field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
          </span>
          <input class="form-input" type="text" id="email" name="email" placeholder="Enter your email"
            oninput="validateEmailInline()" onblur="validateEmailInline(true)" required>
        </div>
        <div class="match-msg" id="email-msg"></div>

        <!-- Password -->
        <label class="field-label" for="password">Password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="password" name="password"
            placeholder="8–12 characters" maxlength="12"
            oninput="checkMatch(); checkStrength(this.value)" required>
          <button class="eye-btn" type="button" onclick="toggleField('password',this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="strength-bar-wrap str-0" id="strength-bar">
          <div class="strength-seg"></div><div class="strength-seg"></div>
          <div class="strength-seg"></div><div class="strength-seg"></div>
        </div>
        <div class="strength-label" id="strength-label" style="color:#b0a090;"></div>

        <!-- Confirm Password -->
        <label class="field-label" for="confirm_password">Confirm password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="confirm_password" name="confirm_password"
            placeholder="Confirm your password" maxlength="12" oninput="checkMatch()" required>
          <button class="eye-btn" type="button" onclick="toggleField('confirm_password',this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="match-msg" id="match-msg"></div>

        <!-- Date of Birth -->
        <label class="field-label" for="dob_month">Date of birth</label>
        <div class="dob-inputs">
          <input class="form-input" type="text" id="dob_month" placeholder="MM" maxlength="2" inputmode="numeric"
            oninput="handleDobInput(this,'dob_day',1,12)" required>
          <input class="form-input" type="text" id="dob_day" placeholder="DD" maxlength="2" inputmode="numeric"
            oninput="handleDobInput(this,'dob_year',1,31)" required>
          <input class="form-input" type="text" id="dob_year" placeholder="YYYY" maxlength="4" inputmode="numeric"
            oninput="handleDobYear(this)" required>
        </div>
        <p class="field-sub">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#b8934a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          We ask for this to comply with local privacy laws.
        </p>
        <div class="age-msg" id="age-msg"></div>

        <!-- Parental Consent -->
        <div id="parental-wrap">
          <input class="consent-check" type="checkbox" id="parental_consent" name="parental_consent">
          <label class="consent-label" for="parental_consent">
            I confirm that I have obtained <strong>parental or guardian consent</strong> to create this account, as I am under 18 years of age.
          </label>
        </div>

        <input type="hidden" id="dob" name="dob">

        <button type="button" class="submit-btn" id="btn-signup" onclick="submitForm()">Sign Up</button>
      </div>

      <div class="login-link">Already have an account? <a href="LoginPage.php">Login</a></div>

      <?php else: ?>
      <!-- ════════════════════════════════════════════════════════════════ -->
      <!-- STEP 2: OTP Verification                                         -->
      <!-- ════════════════════════════════════════════════════════════════ -->
      <p class="form-title">Verify <span>Email</span></p>
      <p class="form-sub">One last step to join the family</p>
      <div class="divider"></div>

      <div class="step-dots">
        <div class="step-dot active"></div>
        <div class="step-dot active"></div>
      </div>

      <div class="js-error" id="js-error"></div>

      <p class="otp-desc">We sent a 6-digit verification code to:</p>
      <div class="otp-email-badge">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b8934a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
        <span><?= htmlspecialchars($pending['email'] ?? '') ?></span>
      </div>

      <!-- Cooldown bar -->
      <div class="cooldown-bar-wrap" id="cooldown-wrap">
        <div class="cooldown-bar" id="cooldown-bar" style="width:100%;"></div>
      </div>

      <label class="field-label" for="reg-otp">Verification code</label>
      <div class="field" id="otp-field-wrap">
        <span class="field-icon">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </span>
        <input class="form-input" type="text" id="reg-otp" placeholder="000000"
          maxlength="6" inputmode="numeric" autofocus
          oninput="this.value=this.value.replace(/\D/g,'')"
          onkeydown="if(event.key==='Enter') verifyOTP()">
      </div>

      <div class="fp-resend" id="resend-wrap">
        Didn't receive it?
        <button type="button" class="resend-btn" id="resend-btn" onclick="resendOTP()">Resend code</button>
        <span id="resend-timer" style="color:#b0a090;font-size:12px;"></span>
      </div>

      <button type="button" class="submit-btn" id="btn-verify" onclick="verifyOTP()">Verify &amp; Create Account</button>

      <!-- Success state (hidden until OTP verified) -->
      <div id="otp-success" style="display:none; text-align:center; padding:10px 0 6px;">
        <p style="font-family:'Playfair Display',Georgia,serif; font-size:20px; color:#2d2520; margin:0 0 6px; font-weight:700;">Account Created!</p>
        <p style="font-size:13px; color:#6b5e54; margin:0 0 22px; line-height:1.6;">
          Welcome to the Drip N' Style family.<br>You can now log in with your new account.
        </p>
        <a href="LoginPage.php" style="
          display:inline-block;
          background:linear-gradient(135deg,#b8934a,#d4a84b);
          color:#fff;
          font-size:13px;
          font-weight:600;
          letter-spacing:.06em;
          text-transform:uppercase;
          text-decoration:none;
          padding:13px 32px;
          border-radius:2px;
          transition:opacity .2s;
        " onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
          Go to Login &rarr;
        </a>
      </div>

      <a class="back-link" id="back-link" href="RegisterPage.php?reset=1">&#8592; Use a different email</a>

      <script>
      const CONTROLLER = '/DRIP-N-STYLE/App/Controllers/RegisterController.php';
      const OTP_COOLDOWN = 60;

      function showError(msg) {
        const el = document.getElementById('js-error');
        el.textContent = msg; el.style.display = 'block';
        el.scrollIntoView({ behavior:'smooth', block:'nearest' });
      }

      function startCooldown(seconds) {
        const btn   = document.getElementById('resend-btn');
        const timer = document.getElementById('resend-timer');
        const wrap  = document.getElementById('cooldown-wrap');
        const bar   = document.getElementById('cooldown-bar');
        btn.disabled = true; btn.style.display = 'none';
        if (wrap) { wrap.style.display = 'block'; bar.style.width = '100%'; }
        let remaining = seconds;
        const tick = setInterval(() => {
          remaining--;
          if (timer) timer.textContent = `Resend in ${remaining}s`;
          if (bar) bar.style.width = ((remaining / seconds) * 100) + '%';
          if (remaining <= 0) {
            clearInterval(tick);
            btn.disabled = false; btn.style.display = 'inline';
            if (timer) timer.textContent = '';
            if (wrap) wrap.style.display = 'none';
          }
        }, 1000);
      }

      async function resendOTP() {
        document.getElementById('js-error').style.display = 'none';
        startCooldown(OTP_COOLDOWN);
        const res  = await fetch(CONTROLLER + '?action=resend_otp', { method:'POST' });
        const data = await res.json();
        if (!data.success) showError(data.message || 'Could not resend. Try again.');
      }

      async function verifyOTP() {
        document.getElementById('js-error').style.display = 'none';
        const otp = document.getElementById('reg-otp').value.trim();
        const btn = document.getElementById('btn-verify');
        if (otp.length < 6) return showError('Please enter the full 6-digit code.');

        btn.disabled = true; btn.textContent = 'Verifying...';
        const res  = await fetch(CONTROLLER + '?action=verify_otp', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'otp=' + encodeURIComponent(otp)
        });
        const data = await res.json();
        if (data.success) {
          // Hide OTP input & buttons, show success message with login redirect
          document.getElementById('otp-field-wrap').style.display = 'none';
          document.getElementById('resend-wrap').style.display    = 'none';
          document.getElementById('btn-verify').style.display     = 'none';
          document.getElementById('back-link').style.display      = 'none';
          document.getElementById('js-error').style.display       = 'none';
          document.getElementById('otp-success').style.display    = 'block';
        } else {
          showError(data.message || 'Invalid code.');
          btn.disabled = false; btn.textContent = 'Verify & Create Account';
        }
      }

      // Resume cooldown if page reloaded
      (function() {
        const sentAt  = <?= intval($_SESSION['reg_otp_sent_at'] ?? 0) ?>;
        const elapsed = Math.floor(Date.now() / 1000) - sentAt;
        const remaining = OTP_COOLDOWN - elapsed;
        if (remaining > 0) startCooldown(remaining);
      })();
      </script>

      <?php endif; ?>

    </div><!-- /.form-card -->
  </div>
</div>

<?php if ($step === 'form'): ?>
<script>
const CONTROLLER = '/DRIP-N-STYLE/App/Controllers/RegisterController.php';

function showError(msg) {
  const el = document.getElementById('js-error');
  el.textContent = msg; el.style.display = 'block';
  el.scrollIntoView({ behavior:'smooth', block:'nearest' });
}
function clearError() {
  const el = document.getElementById('js-error');
  el.textContent = ''; el.style.display = 'none';
}

function toggleField(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.style.color = isText ? '#b0a090' : '#b8934a';
}

function checkMatch() {
  const p1  = document.getElementById('password').value;
  const p2  = document.getElementById('confirm_password').value;
  const msg = document.getElementById('match-msg');
  if (!p2) { msg.textContent = ''; return; }
  if (p1 === p2) { msg.style.color = '#3a8a50'; msg.textContent = '✓ Passwords match'; }
  else           { msg.style.color = '#d85a30'; msg.textContent = '✗ Passwords do not match'; }
}

const STRENGTH_META = [
  { label:'',       color:'#b0a090' },
  { label:'Weak',   color:'#d85a30' },
  { label:'Fair',   color:'#e09c2a' },
  { label:'Good',   color:'#b8934a' },
  { label:'Strong', color:'#3a8a50' },
];

function getPasswordScore(val) {
  let score = 0;
  if (val.length >= 8 && val.length <= 12)           score++;
  if (/[A-Z]/.test(val) && /[a-z]/.test(val))       score++;
  if (/[0-9]/.test(val))                             score++;
  if (/[^A-Za-z0-9]/.test(val))                      score++;
  return score;
}

function checkStrength(val) {
  const bar   = document.getElementById('strength-bar');
  const label = document.getElementById('strength-label');
  const score = getPasswordScore(val);
  bar.className = 'strength-bar-wrap str-' + (val.length ? score : 0);
  const meta = STRENGTH_META[val.length ? score : 0];
  label.textContent = val.length ? meta.label : '';
  label.style.color = meta.color;
}

function validateEmailInline(force) {
  const email = document.getElementById('email').value.trim();
  const msg   = document.getElementById('email-msg');
  const field = document.getElementById('email-field');
  if (!email && !force) { msg.textContent = ''; return; }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (email && !emailRegex.test(email)) {
    msg.style.color = '#d85a30';
    msg.textContent = '✗ Invalid email format (e.g. name@example.com)';
    field.style.borderColor = '#d85a30';
  } else if (email) {
    msg.style.color = '#3a8a50';
    msg.textContent = '✓ Email looks good';
    field.style.borderColor = '';
  } else {
    msg.textContent = '';
    field.style.borderColor = '';
  }
}

function handleDobInput(el, nextId, min, max) {
  el.value = el.value.replace(/\D/g,'');
  if (el.value.length === 2) {
    const v = parseInt(el.value,10);
    if (v < min) el.value = String(min).padStart(2,'0');
    if (v > max) el.value = String(max).padStart(2,'0');
    document.getElementById(nextId).focus();
  }
  evaluateAge();
}
function handleDobYear(el) {
  el.value = el.value.replace(/\D/g,'');
  if (el.value.length === 4) evaluateAge();
  else clearAgeState();
}

function evaluateAge() {
  const mm   = document.getElementById('dob_month').value.trim();
  const dd   = document.getElementById('dob_day').value.trim();
  const yyyy = document.getElementById('dob_year').value.trim();
  const msg  = document.getElementById('age-msg');
  const pw   = document.getElementById('parental-wrap');
  const dob  = document.getElementById('dob');
  const btn  = document.getElementById('btn-signup');
  if (mm.length<1||dd.length<1||yyyy.length<4) return;
  const birth = new Date(`${yyyy}-${mm.padStart(2,'0')}-${dd.padStart(2,'0')}`);
  if (isNaN(birth.getTime())) { clearAgeState(); return; }
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  const mDiff = today.getMonth() - birth.getMonth();
  if (mDiff<0||(mDiff===0&&today.getDate()<birth.getDate())) age--;
  dob.value = `${yyyy}-${mm.padStart(2,'0')}-${dd.padStart(2,'0')}`;
  if (age < 13) {
    msg.textContent = '✗ You must be at least 13 years old to register.';
    msg.className = 'age-msg error';
    pw.classList.remove('visible');
    btn.disabled = true; btn.style.opacity='0.5'; btn.style.cursor='not-allowed';
  } else if (age < 18) {
    msg.textContent = `You are ${age} years old — parental consent is required.`;
    msg.className = 'age-msg minor';
    pw.classList.add('visible');
    btn.disabled = false; btn.style.opacity=''; btn.style.cursor='';
  } else {
    msg.textContent = ''; msg.className = 'age-msg';
    pw.classList.remove('visible');
    document.getElementById('parental_consent').checked = false;
    btn.disabled = false; btn.style.opacity=''; btn.style.cursor='';
  }
}
function clearAgeState() {
  document.getElementById('age-msg').textContent = '';
  document.getElementById('age-msg').className = 'age-msg';
  document.getElementById('parental-wrap').classList.remove('visible');
}

async function submitForm() {
  clearError();

  const name    = document.getElementById('name').value.trim();
  const email   = document.getElementById('email').value.trim();
  const pass    = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;
  const dob     = document.getElementById('dob').value;
  const pw      = document.getElementById('parental-wrap');
  const cb      = document.getElementById('parental_consent');

  if (!name || !email || !pass || !dob) return showError('Please fill in all required fields.');

  // Email format validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    validateEmailInline(true);
    return showError('Please enter a valid email address (e.g. name@example.com).');
  }

  if (pass.length < 8 || pass.length > 12) return showError('Password must be between 8 and 12 characters.');
  if (pass !== confirm) return showError('Passwords do not match.');

  // Weak password check — must be at least "Fair" (score >= 2)
  const pwScore = getPasswordScore(pass);
  if (pwScore < 2) {
    document.getElementById('password').focus();
    return showError('Your password is too weak. Use a mix of uppercase, lowercase, numbers, or symbols.');
  }
  if (pw.classList.contains('visible') && !cb.checked) {
    pw.style.borderColor = '#d85a30'; pw.style.background = '#fff5f2';
    setTimeout(() => { pw.style.borderColor=''; pw.style.background=''; }, 1800);
    return showError('Please check the parental consent box.');
  }

  const btn = document.getElementById('btn-signup');
  btn.disabled = true; btn.textContent = 'Sending code...';

  const body = new URLSearchParams({
    name, email, password: pass, confirm_password: confirm, dob,
    parental_consent: cb.checked ? 'on' : ''
  });

  try {
    const res  = await fetch(CONTROLLER + '?action=send_otp', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString()
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload(); // server set reg_step = otp
    } else {
      showError(data.message || 'Something went wrong.');
      btn.disabled = false; btn.textContent = 'Sign Up';
    }
  } catch (err) {
    showError('Network error. Please try again.');
    btn.disabled = false; btn.textContent = 'Sign Up';
  }
}
</script>
<?php endif; ?>
</body>
</html>