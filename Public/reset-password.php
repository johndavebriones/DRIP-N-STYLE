<?php
session_start();

// Include database
require __DIR__ . '/../App/Config/database_connect.php';

// Create a Database instance and get $conn
$db = new Database();
$conn = $db->connect();

$token = $_GET['token'] ?? '';

if(!$token) {
    $_SESSION['error'] = "Invalid password reset link!";
    header("Location: LoginPage.php");
    exit;
}

// Check if token exists and is not expired
$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    $_SESSION['error'] = "This reset link is invalid or has expired!";
    header("Location: LoginPage.php");
    exit;
}

// If POST request, update password
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        $stmt->execute();

        $_SESSION['success'] = "Your password has been updated! Please login.";
        header("Location: LoginPage.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="login-card">
  <div class="login-form">
    <h3 class="text-center mb-3">Reset Your Password</h3>

    <?php if(isset($_SESSION['error'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter new password" required>
      </div>
      <div class="mb-3">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="showPassword" onclick="togglePassword()">
        <label class="form-check-label" for="showPassword">Show Password</label>
      </div>
      <button type="submit" class="btn btn-warning w-100">Update Password</button>
    </form>
  </div>
</div>
<script src="assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script>
  function togglePassword() {
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    if (newPass.type === "password") {
      newPass.type = "text";
      confirmPass.type = "text";
    } else {
      newPass.type = "password";
      confirmPass.type = "password";
    }
  }
</script>
</body>
</html>
