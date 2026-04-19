<?php
/**
 * Helpdesk Password Reset Page
 * 
 * Two modes:
 *  (A) Token mode  — user arrives via the email link (?token=...)
 *  (B) Forced mode — logged-in user with force_password_change = 1 is redirected here
 * 
 * The user must set a new password before browsing or purchasing.
 * Password is NEVER shown; only a hash is stored via PDO.
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../App/DAO/helpdeskDAO.php';

$dao   = new HelpdeskDAO();
$error = '';
$info  = '';

// ── Determine Mode ──────────────────────────────────────────────────────────

$mode     = 'token';   // 'token' or 'forced'
$tokenRow = null;
$rawToken = trim($_GET['token'] ?? '');

if ($rawToken !== '') {
    // Token mode: validate the token
    $tokenRow = $dao->verifyResetToken($rawToken);
    if (!$tokenRow) {
        $error = 'This password reset link is invalid or has expired. Please contact our Help Desk.';
        $rawToken = '';
    }
} elseif (isset($_SESSION['user_id'])) {
    // Forced mode: logged-in customer with force_password_change flag
    $mode = 'forced';
} else {
    // No token and not logged in — redirect to login
    header('Location: /DRIP-N-STYLE/Public/LoginPage.php');
    exit;
}

// ── Handle POST ─────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $newPass     = $_POST['new_password']     ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (strlen($newPass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'Passwords do not match.';
    } elseif (!preg_match('/[A-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
        $error = 'Password must contain at least one uppercase letter and one number.';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $success = false;

        if ($mode === 'token' && $tokenRow) {
            $success = $dao->completePasswordReset((int)$tokenRow['user_id'], $hashed);
        } elseif ($mode === 'forced') {
            $userId = (int)$_SESSION['user_id'];
            $success = $dao->completePasswordReset($userId, $hashed);
            if ($success) {
                // Clear forced flag from session too
                unset($_SESSION['force_password_change']);
            }
        }

        if ($success) {
            $info = 'Your password has been updated successfully. You may now log in.';
            // Destroy session to force fresh login
            $_SESSION = [];
            session_destroy();
        } else {
            $error = 'Password update failed. Please try again or contact support.';
        }
    }
}

$pageTitle = 'Set New Password — DRIP-N-STYLE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { margin:0; background:#f5f0eb; font-family:Arial,sans-serif; display:flex;
           min-height:100vh; align-items:center; justify-content:center; }
    .card { background:#fff; border:1px solid #e8e0d8; border-radius:10px; padding:44px 48px;
            max-width:480px; width:100%; box-shadow:0 4px 24px rgba(0,0,0,.08); }
    .brand { font-family:Georgia,serif; font-size:1.2rem; font-weight:700; letter-spacing:6px;
             color:#b8934a; text-transform:uppercase; margin-bottom:6px; }
    .divider { height:1px; background:linear-gradient(90deg,#c9a96e,transparent); width:160px; margin-bottom:28px; }
    h2 { font-family:Georgia,serif; font-size:1.5rem; color:#2d2520; font-weight:400; margin:0 0 4px; }
    .subtitle { font-size:.85rem; color:#888; margin-bottom:28px; }
    label { font-size:.88rem; font-weight:600; color:#5c4f44; display:block; margin-bottom:5px; }
    input[type=password] { width:100%; border:1px solid #d8d0c8; border-radius:6px; padding:11px 14px;
                           font-size:.92rem; color:#2d2520; outline:none; transition:border .2s; }
    input[type=password]:focus { border-color:#b8934a; box-shadow:0 0 0 3px rgba(184,147,74,.15); }
    .form-group { margin-bottom:18px; }
    .btn-submit { width:100%; background:#b8934a; color:#fff; border:none; border-radius:6px;
                  padding:13px; font-size:.95rem; font-weight:700; letter-spacing:1px;
                  cursor:pointer; transition:background .2s; margin-top:8px; }
    .btn-submit:hover { background:#a07838; }
    .alert { border-radius:6px; padding:12px 16px; font-size:.85rem; margin-bottom:20px; }
    .alert-danger  { background:#fff0f0; border:1px solid #f5c6c6; color:#842029; }
    .alert-success { background:#f0faf0; border:1px solid #b8ddb8; color:#1a5c2a; }
    .rules { font-size:.78rem; color:#999; margin-top:6px; line-height:1.7; }
    .forced-notice { background:#fff8e6; border:1px solid #f0d080; border-radius:6px;
                     padding:12px 16px; font-size:.83rem; color:#7a5800; margin-bottom:22px; }
    .login-link { text-align:center; margin-top:24px; font-size:.85rem; }
    .login-link a { color:#b8934a; font-weight:600; text-decoration:none; }
  </style>
</head>
<body>
<div class="card">
  <div class="brand">DRIP-N-STYLE</div>
  <div class="divider"></div>

  <?php if (!empty($info)): ?>
    <h2>All Done! ✅</h2>
    <div class="alert alert-success"><?= htmlspecialchars($info) ?></div>
    <div class="login-link"><a href="/DRIP-N-STYLE/Public/LoginPage.php">← Back to Login</a></div>

  <?php else: ?>
    <h2>Set New Password</h2>
    <p class="subtitle">
      <?php if ($mode === 'forced'): ?>
        Your account has been restored by our Help Desk. You must set a new password before continuing.
      <?php else: ?>
        Create a new secure password for your account.
      <?php endif; ?>
    </p>

    <?php if ($mode === 'forced'): ?>
    <div class="forced-notice">
      🔐 <strong>Security Requirement:</strong> Our Help Desk has flagged your account for a mandatory password reset.
      You cannot browse or purchase until a new password is set.
    </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($rawToken) || $mode === 'forced'): ?>
    <form method="POST" action="">
      <?php if (!empty($rawToken)): ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($rawToken, ENT_QUOTES) ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required autocomplete="new-password">
        <div class="rules">
          Must be at least 8 characters, include one uppercase letter and one number.
        </div>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
      </div>

      <button type="submit" class="btn-submit">UPDATE PASSWORD</button>
    </form>
    <?php endif; ?>

    <?php if ($mode === 'token' && empty($rawToken)): ?>
      <!-- Token was invalid — no form shown -->
      <div class="login-link"><a href="/DRIP-N-STYLE/Public/LoginPage.php">← Back to Login</a></div>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>
