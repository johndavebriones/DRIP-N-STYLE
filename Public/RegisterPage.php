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
</head>
<body>

<!-- Left Panel: image fills the panel, same as login -->
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

      <form method="POST" action="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=register">

        <label class="field-label" for="name">Full name</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </span>
          <input class="form-input" type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>

        <label class="field-label" for="email">Email address</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
          </span>
          <input class="form-input" type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <label class="field-label" for="password">Password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="password" name="password"
            placeholder="Enter your password" oninput="checkMatch()" required>
          <button class="eye-btn" type="button" onclick="toggleField('password', this)" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

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

        <button type="submit" class="submit-btn">Sign Up</button>
      </form>

      <div class="login-link">
        Already have an account? <a href="LoginPage.php">Login</a>
      </div>
    </div>
  </div>
</div>

<script>
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
    if (p1 === p2) {
      msg.style.color = '#3a8a50';
      msg.textContent = '✓ Passwords match';
    } else {
      msg.style.color = '#d85a30';
      msg.textContent = '✗ Passwords do not match';
    }
  }
</script>
</body>
</html>