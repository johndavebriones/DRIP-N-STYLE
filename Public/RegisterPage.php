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

  <link href="../Public/assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/register.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <div class="auth-container">
    <!-- Left Side (Image or Branding) -->
    <div class="auth-left" onclick="window.location.href='index.php'" style="cursor:pointer;">
      <img src="assets/images/dripnstylelogo.png" alt="Drip N' Style Logo">
    </div>

    <!-- Right Side (Form) -->
    <div class="auth-right">
      <h3>Create Account</h3>
      <p>Join the Drip N' Style family</p>

      <form method="POST" action="/Websites/DRIP-N-STYLE/App/Controllers/AuthController.php?action=register">
        <div class="mb-3">
          <label>Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
        </div>

        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
          <label>Password</label>
          <div class="password-card">
            <input type="password" id="password" name="password" class="password-input" placeholder="Enter your password" required>
            <span id="showPassIcon1" class="toggle-password"><i class="fa-regular fa-eye"></i></span>
          </div>
        </div>

        <div class="mb-3">
          <label>Confirm Password</label>
          <div class="password-card">
            <input type="password" id="confirm_password" name="confirm_password" class="password-input" placeholder="Confirm your password" required>
            <span id="showPassIcon2" class="toggle-password"><i class="fa-regular fa-eye"></i></span>
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

        <button type="submit" class="btn btn-warning w-100">Sign Up</button>

        <div class="text-center mt-3">
          <span class="opacity-75">Already have an account?</span>
          <a href="LoginPage.php" class="text-decoration-none text-dark fw-bold">Login</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Smooth transition effect
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

  const pass1 = document.getElementById('password');
  const pass2 = document.getElementById('confirm_password');

  function bindHoldToShow(iconId) {
    const icon = document.getElementById(iconId);
    const eye = icon.querySelector("i");

    icon.addEventListener("mousedown", () => {
      pass1.type = "text";
      pass2.type = "text";
      eye.classList.replace("fa-eye", "fa-eye-slash");
    });

    icon.addEventListener("mouseup", () => {
      pass1.type = "password";
      pass2.type = "password";
      eye.classList.replace("fa-eye-slash", "fa-eye");
    });

    icon.addEventListener("mouseleave", () => {
      pass1.type = "password";
      pass2.type = "password";
      eye.classList.replace("fa-eye-slash", "fa-eye");
    });
  }
  bindHoldToShow("showPassIcon1");
  bindHoldToShow("showPassIcon2");
  </script>

  <script src="../Public/assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>
