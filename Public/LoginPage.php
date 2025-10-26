<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Drip N' Style | Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(180deg,rgb(215, 174, 11),rgb(255, 255, 255));
    font-family: 'Poppins', sans-serif;
  }
    .login-card {
      display: flex;
      background: #fff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 800px;
      max-width: 100%;
    }
    .login-image {
      flex: 1;
      background: url('../public/assets/images/dripnstylelogo.png') center/cover no-repeat;
      background-color: #fef8e6;
    }

    .login-form {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-form h3 {
      font-weight: 700;
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
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
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
          <a href="#" class="text-decoration-none">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-warning w-100">Login</button>
      </form>
    </div>
  </div>

</body>
</html>
