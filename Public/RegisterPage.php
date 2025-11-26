<?php 
ini_set('session.cookie_path', '/');
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drip N' Style | Register</title>

  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

<div class="auth-container">

  <!-- LEFT: LOGO -->
  <div class="auth-left" onclick="window.location.href='index.php'">
    <img src="assets/images/dripnstyleRegisterLogo.png" alt="Drip N Style Logo">
  </div>

  <!-- RIGHT: FORM -->
  <div class="auth-right">

    <h3>Create Account</h3>
    <p>Join the Drip N' Style family</p>

    <form method="POST" action="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=register">

      <div class="form-group-custom">
        <label>Full Name</label>
        <span class="icon-left"><i class="fa-regular fa-user"></i></span>
        <input type="text" name="name" class="input-custom" placeholder="Enter your full name" required>
      </div>

      <div class="form-group-custom">
        <label>Email</label>
        <span class="icon-left"><i class="fa-regular fa-envelope"></i></span>
        <input type="email" name="email" class="input-custom" placeholder="Enter your email" required>
      </div>

      <div class="form-group-custom">
        <label>Password</label>
        <div class="password-card">
          <input type="password" id="password" name="password" class="input-custom password-input" placeholder="Enter your password" required>
          <span id="showPassIcon1" class="icon-right toggle-password"><i class="fa-regular fa-eye"></i></span>
        </div>
      </div>

      <div class="form-group-custom">
        <label>Confirm Password</label>
        <div class="password-card">
          <input type="password" id="confirm_password" name="confirm_password" class="input-custom password-input" placeholder="Confirm your password" required>
          <span id="showPassIcon2" class="icon-right toggle-password"><i class="fa-regular fa-eye"></i></span>
        </div>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="card border-warning bg-warning-subtle mb-3">
          <div class="card-body p-2 text-center text-dark">
            <?= htmlspecialchars($_SESSION['error']) ?>
          </div>
        </div>
        <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <button type="submit" class="auth-btn">Sign Up</button>

      <div class="text-center mt-3">
        <span class="opacity-75">Already have an account?</span>
        <a href="LoginPage.php" class="text-decoration-none text-dark fw-bold">Login</a>
      </div>

    </form>
  </div>

</div>

<script>
document.querySelectorAll('a[href]').forEach(link => {
  link.addEventListener('click', function(e) {
    if (this.getAttribute('href').endsWith('.php')) {
      e.preventDefault();
      document.body.classList.add('page-transition');
      setTimeout(() => {
        window.location.href = this.getAttribute('href');
      }, 300);
    }
  });
});

function bindHoldToShow(iconId, inputId) {
  const icon = document.getElementById(iconId);
  const input = document.getElementById(inputId);
  const eye = icon.querySelector("i");

  icon.addEventListener("mousedown", () => {
    input.type = "text";
    eye.classList.replace("fa-eye", "fa-eye-slash");
  });

  icon.addEventListener("mouseup", () => {
    input.type = "password";
    eye.classList.replace("fa-eye-slash", "fa-eye");
  });

  icon.addEventListener("mouseleave", () => {
    input.type = "password";
    eye.classList.replace("fa-eye-slash", "fa-eye");
  });
}

bindHoldToShow("showPassIcon1", "password");
bindHoldToShow("showPassIcon2", "confirm_password");
</script>

</body>
</html>
