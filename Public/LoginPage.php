<?php 
ini_set('session.cookie_path', '/');
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::redirectIfLoggedIn();

// Store timeout alert if exists
$timeoutAlert = null;
if (isset($_SESSION['timeout_alert'])) {
    $timeoutAlert = $_SESSION['timeout_alert'];
    unset($_SESSION['timeout_alert']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Login</title>

  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/login.css?v=11">
</head>
<body>

<div class="auth-container">

  <!-- Left / Top Image -->
  <div class="auth-left" onclick="window.location.href='index.php'">
      <img src="assets/images/dripnStylelogologinRegister.png" alt="Drip N Style Logo">
  </div>

  <!-- Right Form Panel -->
  <div class="auth-right">

    <h3>Welcome Back</h3>
    <p>Start Your Drip with a Style</p>

    <form method="POST" action="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=login">
      <!-- EMAIL -->
      <div class="form-group-custom">
          <span class="icon-left"><i class="fa-regular fa-envelope"></i></span>
          <input type="email" name="email" class="input-custom" placeholder="Enter your email" required>
      </div>

      <!-- PASSWORD -->
      <div class="form-group-custom position-relative">
          <span class="icon-left"><i class="fa-solid fa-lock"></i></span>

          <input type="password" name="password" id="password" class="input-custom" placeholder="Enter your password" required>

          <span class="icon-right" id="togglePassword">
              <i class="fa-regular fa-eye"></i>
          </span>
      </div>

      <?php if (isset($_SESSION['error'])): ?>
          <div class="card border-warning bg-warning-subtle mb-3">
              <div class="card-body p-2 text-center text-dark">
                  <?= htmlspecialchars($_SESSION['error']) ?>
              </div>
          </div>
          <?php unset($_SESSION['error']); ?>
      <?php endif; ?>

      <div class="d-flex justify-content-between mb-3">
          <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" class="text-decoration-none text-dark fw-bold">Forgot Password?</a>
      </div>

      <button type="submit" class="btn btn-warning auth-btn">Login</button>

      <div class="text-link">
          <span>Don't have an account?</span>
          <a href="RegisterPage.php" class="text-decoration-none text-dark fw-bold">Sign Up</a>
      </div>

    </form>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Forgot Password</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="forgot-password.php">
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <button type="submit" class="btn btn-warning w-100">Send Reset Link</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Session timeout alert
<?php if ($timeoutAlert): ?>
window.addEventListener('DOMContentLoaded', function() {
    alert(<?= json_encode($timeoutAlert) ?>);
});
<?php endif; ?>

const toggle = document.getElementById('togglePassword');
const input = document.getElementById('password');
const icon = toggle.querySelector('i');

toggle.addEventListener('click', () => {
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
});
</script>

<script src="assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>

</body>
</html>