<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Login / Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <div class="auth-wrapper" id="authWrapper">
    <!-- LOGIN -->
    <div class="form-container login-container">
      <h3>Welcome Back 👋</h3>
      <p>Login to your Drip N’ Style account</p>
      <form method="POST" action="../app/controllers/AuthController.php?action=login">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn btn-warning w-100">Login</button>
      </form>
    </div>

    <!-- REGISTER -->
    <div class="form-container register-container">
      <h3>Join the Drip 💫</h3>
      <p>Create your account to start shopping</p>
      <form method="POST" action="../app/controllers/AuthController.php?action=register">
        <input type="text" name="name" placeholder="Full Name" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit" class="btn btn-warning w-100">Register</button>
      </form>
    </div>

    <!-- YELLOW SLIDING PANEL -->
    <div class="yellow-panel" id="yellowPanel">
      <h4 id="panelTitle">Don’t have an account?</h4>
      <p id="panelText">Sign up and start your Drip journey!</p>
      <button id="switchBtn">Register</button>
    </div>
  </div>

  <!-- Popup Modal -->
<?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
  <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow">
        <div class="modal-header <?= isset($_SESSION['error']) ? 'bg-success' : 'bg-danger' ?>">
          <h5 class="modal-title text-white" id="feedbackModalLabel">
            <?= isset($_SESSION['error']) ? 'Oops!' : 'Success' ?>
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0 text-dark fw-semibold">
            <?= htmlspecialchars($_SESSION['error'] ?? $_SESSION['success']) ?>
          </p>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
      modal.show();
    });
  </script>
  <?php unset($_SESSION['error'], $_SESSION['success']); ?>
<?php endif; ?>

  <script src="assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
  <script src="assets/js/switch-modal.js"></script>
</body>
</html>
