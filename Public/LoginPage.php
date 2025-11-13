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

  <link href="../Public/assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<div class="auth-container">
  <!-- Left Image (fixed width) -->
  <div class="auth-left" onclick="window.location.href='index.php'">
    <img src="assets/images/dripnstylelogo.png" alt="Drip N' Style">
  </div>

  <!-- Right Login Form (flexible width) -->
  <div class="auth-right">
    <h3>Welcome Back!</h3>
    <p>Start Your Drip with a Style</p>
    <form method="POST" action="/Websites/DRIP-N-STYLE/App/Controllers/AuthController.php?action=login">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>

      <div class="mb-3 position-relative">
        <label>Password</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
          <span class="input-group-text" id="togglePassword">
            <i class="fa-regular fa-eye"></i>
          </span>
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

      <div class="d-flex justify-content-between mb-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
      </div>

      <button type="submit" class="btn btn-warning">Login</button>

      <div class="text-link">
        <span>Don't have an account?</span>
        <a href="RegisterPage.php">Sign Up</a>
      </div>
    </form>
  </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Forgot Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="forgot-password.php">
          <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control" name="email" placeholder="Your email" required>
          </div>
          <button type="submit" class="btn btn-warning w-100">Send Reset Link</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  // Smooth transition
  document.querySelectorAll('a[href]').forEach(link => {
    link.addEventListener('click', function(e) {
      if (this.getAttribute('href').endsWith('.php')) {
        e.preventDefault();
        document.body.classList.add('page-transition');
        setTimeout(() => { window.location.href = this.getAttribute('href'); }, 300);
      }
    });
  });

  // Hold-to-show password
  const toggle = document.getElementById('togglePassword');
  const input = document.getElementById('password');
  const icon = toggle.querySelector('i');
  toggle.addEventListener('mousedown', () => { input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); });
  toggle.addEventListener('mouseup', () => { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); });
  toggle.addEventListener('mouseleave', () => { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); });
</script>
<script src="../Public/assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>
