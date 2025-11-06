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

  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/register.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .page-transition {
      animation: fadeOut 0.3s ease-out forwards;
    }
    @keyframes fadeOut {
      to { opacity: 0; transform: translateY(-20px); }
    }
  </style>

</head>
<body>

  <div class="register-card">
    <div class="register-form">
      <h3 class="text-center mb-1">Create Account</h3>
      <p class="text-center mb-4 opacity-75">Join Us and Start Your Drip</p>
      <form method="POST" action="/Websites/DRIP-N-STYLE/App/Controllers/AuthController.php?action=register">
        <div class="mb-3">
          <label>Name</label>
          <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>
        <div class="mb-3">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
        </div>
        <?php if (isset($_SESSION['error'])): ?>
          <div class="card border-warning bg-warning-subtle mb-3">
            <div class="card-body p-2 text-center text-dark">
              ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
          </div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <button type="submit" class="btn btn-warning w-100">Register</button>
        <div class="text-center mt-3">
          <span class="opacity-75">Already have an account?</span>
          <a href="LoginPage.php" class="text-decoration-none text-dark fw-bold">Login</a>
        </div>
      </form>
    </div>
  </div>

  <script src="../assets/vendor/bootstrap5/js/bootstrap.min.js"></script>

  <?php if (isset($_SESSION['success'])): ?>

  <script>
  Swal.fire({
    icon: 'success',
    title: 'Registration Successful!',
    text: '<?= addslashes($_SESSION['success']) ?>',
    confirmButtonColor: '#ffc107',
    confirmButtonText: 'Login Now'
  }).then(() => {
    window.location.href = "LoginPage.php";
  });
  </script>

  <?php unset($_SESSION['success']); endif; ?>

</body>
</html>
