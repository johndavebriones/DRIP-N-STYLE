<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Login</title>

  <link href="../Public/assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
  <style>
    body {
      animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .page-transition {
      animation: fadeOut 0.3s ease-out forwards;
    }
    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-20px);
      }
    }
  </style>
</head>
<body>

  <div class="login-card">
    <!-- Left Image -->
    <div class="login-image" onclick="window.location.href='index.php'" style="cursor:pointer;"></div>

    <!-- Right Login Form -->
    <div class="login-form">
      <h3 class="text-center mb-1">Welcome Back!</h3>
      <p class="text-center mb-4 opacity-75">Start Your Drip with a Style</p>
      <form method="POST" action="../app/controllers/AuthController.php?action=login">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="mb-3 position-relative">
          <label>Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="card border-warning bg-warning-subtle mb-3">
            <div class="card-body p-2 text-center text-dark">
              ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
          </div>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-3">
          <a href="#" class="text-decoration-none text-dark" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
            Forgot Password?
          </a>
        </div>

        <button type="submit" class="btn btn-warning w-100">Login</button>
        
        <div class="text-center mt-3">
          <span class="opacity-75">Don't have an account?</span>
          <a href="RegisterPage.php" class="text-decoration-none text-dark fw-bold">Sign Up</a>
        </div>
      </form>
    </div>
  </div>
  <!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="forgot-password.php">
          <div class="mb-3">
            <label for="forgotEmail" class="form-label">Enter your email</label>
            <input type="email" class="form-control" id="forgotEmail" name="email" placeholder="Your email" required>
          </div>
          <button type="submit" class="btn btn-warning w-100">Send Reset Link</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
   // Page Transition Effect
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
</script>
<script src="../Public/assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>