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
  <title>Drip N' Style | Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
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

      <p class="form-title">Welcome <span>Back</span></p>
      <p class="form-sub">Start your drip with a style</p>
      <div class="divider"></div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-warn"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <form method="POST" action="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=login">

        <label class="field-label" for="email">Email address</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
          </span>
          <input class="form-input" type="email" id="email" name="email"
            placeholder="Enter your email"
            value="<?= isset($_SESSION['login_email']) ? htmlspecialchars($_SESSION['login_email']) : '' ?>" required>
        </div>
        <?php if (isset($_SESSION['login_email'])) unset($_SESSION['login_email']); ?>

        <label class="field-label" for="password">Password</label>
        <div class="field">
          <span class="field-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input class="form-input" type="password" id="password" name="password"
            placeholder="Enter your password" required>
          <button class="eye-btn" type="button" id="togglePassword" tabindex="-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>

        <div class="form-row">
          <a href="forgot-password.php" class="forgot-btn">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn">Login</button>
      </form>

      <div class="signup-link">
        Don't have an account? <a href="RegisterPage.php">Sign up</a>
      </div>
    </div>
  </div>
</div>


<script>
<?php if (!empty($timeoutAlert)): ?>
window.addEventListener('DOMContentLoaded', function() {
  alert(<?= json_encode($timeoutAlert) ?>);
});
<?php endif; ?>

const toggle = document.getElementById('togglePassword');
const passInput = document.getElementById('password');
toggle.addEventListener('click', () => {
  const isText = passInput.type === 'text';
  passInput.type = isText ? 'password' : 'text';
  toggle.style.color = isText ? '#b0a090' : '#b8934a';
});
</script>
</body>
</html>