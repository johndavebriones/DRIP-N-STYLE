<?php 
ini_set('session.cookie_path', '/');
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/register.css">
  <style>
    /* ── Password Strength ── */
    .strength-bar-wrap {
      display: flex;
      gap: 5px;
      margin-top: 7px;
      margin-bottom: 2px;
    }
    .strength-seg {
      flex: 1;
      height: 3px;
      border-radius: 99px;
      background: #e8dfd4;
      transition: background 0.35s ease;
    }
    .strength-label {
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.04em;
      margin-bottom: 10px;
      min-height: 15px;
      transition: color 0.3s;
    }
    .str-0 .strength-seg { background: #e8dfd4; }

    .str-1 .strength-seg:nth-child(1) { background: #d85a30; }
    .str-1 .strength-seg:nth-child(n+2) { background: #e8dfd4; }

    .str-2 .strength-seg:nth-child(1),
    .str-2 .strength-seg:nth-child(2) { background: #e09c2a; }
    .str-2 .strength-seg:nth-child(n+3) { background: #e8dfd4; }

    .str-3 .strength-seg:nth-child(1),
    .str-3 .strength-seg:nth-child(2),
    .str-3 .strength-seg:nth-child(3) { background: #b8934a; }
    .str-3 .strength-seg:nth-child(4) { background: #e8dfd4; }

    .str-4 .strength-seg { background: #3a8a50; }

    /* ── Date of Birth ── */
    .dob-inputs {
      display: flex;
      gap: 8px;
    }
    .dob-inputs input {
      flex: 1;
      text-align: center;
    }
    .field-sub {
      font-family: 'DM Sans', sans-serif;
      font-size: 11px;
      color: #a89880;
      line-height: 1.5;
      margin-top: 6px;
      margin-bottom: 4px;
    }
    .field-sub svg {
      display: inline;
      vertical-align: middle;
      margin-right: 3px;
    }

    /* ── Age messages ── */
    .age-msg {
      font-family: 'DM Sans', sans-serif;
      font-size: 12px;
      font-weight: 500;
      min-height: 16px;
      margin-top: 4px;
      margin-bottom: 8px;
      transition: color 0.3s;
    }
    .age-msg.error  { color: #d85a30; }
    .age-msg.minor  { color: #b8934a; }

    /* ── Parental Consent ── */
    #parental-wrap {
      display: none;
      align-items: flex-start;
      gap: 10px;
      background: #fdf7ef;
      border: 1px solid #e8c97a;
      border-radius: 8px;
      padding: 12px 14px;
      margin-bottom: 14px;
      animation: fadeSlideIn 0.3s ease;
    }
    #parental-wrap.visible { display: flex; }
    @keyframes fadeSlideIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .consent-check {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
      accent-color: #b8934a;
      margin-top: 2px;
      cursor: pointer;
    }
    .consent-label {
      font-family: 'DM Sans', sans-serif;
      font-size: 12px;
      color: #6b5a45;
      line-height: 1.55;
      cursor: pointer;
    }
    .consent-label strong {
      color: #b8934a;
      font-weight: 600;
    }
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

      <p class="form-title">Create <span>Account</span></p>
      <p class="form-sub">Join the Drip N' Style family</p>
      <div class="divider"></div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-warn"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form method="POST" action="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=register" id="register-form">

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
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
          </span>
          <input class="form-input" type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <!-- Password -->
        <label class="field-label" for="password">Password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="password" name="password"
            placeholder="Enter your password"
            oninput="checkMatch(); checkStrength(this.value)"
            required>
          <button class="eye-btn" type="button" onclick="toggleField('password', this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <!-- Strength Bar -->
        <div class="strength-bar-wrap str-0" id="strength-bar">
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
          <div class="strength-seg"></div>
        </div>
        <div class="strength-label" id="strength-label" style="color:#b0a090;"></div>

        <!-- Confirm Password -->
        <label class="field-label" for="confirm_password">Confirm password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="confirm_password" name="confirm_password"
            placeholder="Confirm your password" oninput="checkMatch()" required>
          <button class="eye-btn" type="button" onclick="toggleField('confirm_password', this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
        <div class="match-msg" id="match-msg"></div>

        <!-- Date of Birth -->
        <label class="field-label" for="dob_month">Date of Birth</label>
        <div class="dob-inputs">
          <input class="form-input" type="text" id="dob_month" name="dob_month"
            placeholder="MM" maxlength="2" inputmode="numeric"
            oninput="handleDobInput(this, 'dob_day', 1, 12)" required>
          <input class="form-input" type="text" id="dob_day" name="dob_day"
            placeholder="DD" maxlength="2" inputmode="numeric"
            oninput="handleDobInput(this, 'dob_year', 1, 31)" required>
          <input class="form-input" type="text" id="dob_year" name="dob_year"
            placeholder="YYYY" maxlength="4" inputmode="numeric"
            oninput="handleDobYear(this)" required>
        </div>
        <p class="field-sub">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#b8934a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          We ask for this to ensure we comply with local privacy laws and to send you a special gift on your birthday!
        </p>
        <div class="age-msg" id="age-msg"></div>

        <!-- Parental Consent (13–17 only) -->
        <div id="parental-wrap">
          <input class="consent-check" type="checkbox" id="parental_consent" name="parental_consent">
          <label class="consent-label" for="parental_consent">
            I confirm that I have obtained <strong>parental or guardian consent</strong> to create this account, as I am under 18 years of age.
          </label>
        </div>

        <!-- Hidden combined DOB for server -->
        <input type="hidden" id="dob" name="dob">

        <button type="submit" class="submit-btn" id="submit-btn">Sign Up</button>
      </form>

      <div class="login-link">
        Already have an account? <a href="LoginPage.php">Login</a>
      </div>
    </div>
  </div>
</div>

<script>
  /* ── Toggle password visibility ── */
  function toggleField(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.style.color = isText ? '#b0a090' : '#b8934a';
  }

  /* ── Password match ── */
  function checkMatch() {
    const p1  = document.getElementById('password').value;
    const p2  = document.getElementById('confirm_password').value;
    const msg = document.getElementById('match-msg');
    if (!p2) { msg.textContent = ''; return; }
    if (p1 === p2) {
      msg.style.color = '#3a8a50';
      msg.textContent = '✓ Passwords match';
    } else {
      msg.style.color = '#d85a30';
      msg.textContent = '✗ Passwords do not match';
    }
  }

  /* ── Password strength ── */
  const STRENGTH_META = [
    { label: '',        color: '#b0a090' },
    { label: 'Weak',   color: '#d85a30' },
    { label: 'Fair',   color: '#e09c2a' },
    { label: 'Good',   color: '#b8934a' },
    { label: 'Strong', color: '#3a8a50' },
  ];

  function checkStrength(val) {
    const bar   = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 8)                          score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val))  score++;
    if (/[0-9]/.test(val))                        score++;
    if (/[^A-Za-z0-9]/.test(val))                score++;

    // Reset classes
    bar.className = 'strength-bar-wrap str-' + (val.length ? score : 0);

    const meta = STRENGTH_META[val.length ? score : 0];
    label.textContent = val.length ? meta.label : '';
    label.style.color = meta.color;
  }

  /* ── DOB auto-advance ── */
  function handleDobInput(el, nextId, min, max) {
    el.value = el.value.replace(/\D/g, '');
    if (el.value.length === 2) {
      const v = parseInt(el.value, 10);
      if (v < min) el.value = String(min).padStart(2, '0');
      if (v > max) el.value = String(max).padStart(2, '0');
      document.getElementById(nextId).focus();
    }
    evaluateAge();
  }

  function handleDobYear(el) {
    el.value = el.value.replace(/\D/g, '');
    if (el.value.length === 4) evaluateAge();
    else clearAgeState();
  }

  /* ── Age evaluation ── */
  function evaluateAge() {
    const mm   = document.getElementById('dob_month').value.trim();
    const dd   = document.getElementById('dob_day').value.trim();
    const yyyy = document.getElementById('dob_year').value.trim();
    const msg  = document.getElementById('age-msg');
    const pw   = document.getElementById('parental-wrap');
    const dob  = document.getElementById('dob');
    const btn  = document.getElementById('submit-btn');

    if (mm.length < 1 || dd.length < 1 || yyyy.length < 4) return;

    const birth   = new Date(`${yyyy}-${mm.padStart(2,'0')}-${dd.padStart(2,'0')}`);
    if (isNaN(birth.getTime())) { clearAgeState(); return; }

    const today   = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const mDiff   = today.getMonth() - birth.getMonth();
    if (mDiff < 0 || (mDiff === 0 && today.getDate() < birth.getDate())) age--;

    // Store combined DOB for server
    dob.value = `${yyyy}-${mm.padStart(2,'0')}-${dd.padStart(2,'0')}`;

    if (age < 13) {
      msg.textContent = '✗ You must be at least 13 years old to create an account.';
      msg.className   = 'age-msg error';
      pw.classList.remove('visible');
      btn.disabled    = true;
      btn.style.opacity = '0.5';
      btn.style.cursor  = 'not-allowed';
    } else if (age < 18) {
      msg.textContent = `You are ${age} years old — parental consent is required.`;
      msg.className   = 'age-msg minor';
      pw.classList.add('visible');
      btn.disabled    = false;
      btn.style.opacity = '';
      btn.style.cursor  = '';
    } else {
      msg.textContent = '';
      msg.className   = 'age-msg';
      pw.classList.remove('visible');
      document.getElementById('parental_consent').checked = false;
      btn.disabled    = false;
      btn.style.opacity = '';
      btn.style.cursor  = '';
    }
  }

  function clearAgeState() {
    document.getElementById('age-msg').textContent = '';
    document.getElementById('age-msg').className   = 'age-msg';
    document.getElementById('parental-wrap').classList.remove('visible');
  }

  /* ── Form submit guard ── */
  document.getElementById('register-form').addEventListener('submit', function(e) {
    const pw = document.getElementById('parental-wrap');
    const cb = document.getElementById('parental_consent');
    if (pw.classList.contains('visible') && !cb.checked) {
      e.preventDefault();
      cb.focus();
      pw.style.borderColor = '#d85a30';
      pw.style.background  = '#fff5f2';
      setTimeout(() => {
        pw.style.borderColor = '';
        pw.style.background  = '';
      }, 1800);
    }
    const p1 = document.getElementById('password').value;
    const p2 = document.getElementById('confirm_password').value;
    if (p1 !== p2) {
      e.preventDefault();
      document.getElementById('confirm_password').focus();
    }
  });
</script>
</body>
</html>