<?php
session_start();
require __DIR__ . '/../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();

$token = $_GET['token'] ?? '';

if (!$token) {
    $_SESSION['error'] = "Invalid password reset link!";
    header("Location: LoginPage.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "This reset link is invalid or has expired!";
    header("Location: LoginPage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed, $token);
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
  <title>Reset Password — DRIP-N-STYLE</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background: #f5f0eb;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      font-family: 'DM Sans', Arial, sans-serif;
    }

    .card {
      background: #faf8f5;
      border: 1px solid #e8e0d8;
      border-radius: 3px;
      width: 100%;
      max-width: 440px;
      overflow: hidden;
    }

    .bar { height: 3px; background: linear-gradient(135deg, #b8934a 0%, #d4a84b 50%, #c9a96e 100%); }

    .card-header {
      padding: 36px 44px 28px;
      border-bottom: 1px solid #ede8e2;
    }

    .logo {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 6px;
      color: #b8934a;
      text-transform: uppercase;
      margin-bottom: 6px;
    }

    .logo-line {
      height: 1px;
      width: 130px;
      background: linear-gradient(90deg, #c9a96e 0%, transparent 100%);
    }

    .card-body { padding: 36px 44px 40px; }

    .title {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 22px;
      color: #2d2520;
      font-weight: 400;
      margin-bottom: 2px;
    }

    .title span { color: #b8934a; font-weight: 700; }

    .subtitle {
      font-size: 13px;
      color: #9a8a7c;
      font-weight: 300;
      line-height: 1.6;
      margin-bottom: 28px;
    }

    .divider { height: 1px; background: #ede8e2; margin-bottom: 28px; }

    .alert {
      background: #fdf2f0;
      border-left: 2px solid #d85a30;
      border-radius: 0 2px 2px 0;
      padding: 11px 14px;
      font-size: 12px;
      color: #a04020;
      margin-bottom: 22px;
      line-height: 1.6;
    }

    label.field-label {
      display: block;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #7a6a5c;
      margin-bottom: 8px;
    }

    .field { position: relative; margin-bottom: 20px; }

    .field input {
      width: 100%;
      background: #fff;
      border: 1px solid #ddd5ca;
      border-radius: 2px;
      padding: 12px 44px 12px 14px;
      font-family: 'DM Sans', Arial, sans-serif;
      font-size: 14px;
      color: #2d2520;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }

    .field input::placeholder { color: #c4b8ac; }

    .field input:focus {
      border-color: #c9a96e;
      box-shadow: 0 0 0 3px rgba(201,169,110,.12);
    }

    .eye-toggle {
      position: absolute;
      right: 13px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #b0a090;
      background: none;
      border: none;
      padding: 0;
      display: flex;
      align-items: center;
    }

    .strength-wrap { margin-top: 8px; margin-bottom: 24px; }

    .strength-header {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      color: #9a8a7c;
      margin-bottom: 5px;
    }

    .strength-track {
      height: 3px;
      background: #ede8e2;
      border-radius: 2px;
      overflow: hidden;
    }

    .strength-fill {
      height: 100%;
      border-radius: 2px;
      width: 0%;
      transition: width .3s, background .3s;
    }

    .match-msg {
      font-size: 12px;
      margin-top: -14px;
      margin-bottom: 20px;
      min-height: 18px;
    }

    .show-row {
      display: flex;
      align-items: center;
      gap: 9px;
      margin-bottom: 28px;
    }

    .show-row input[type="checkbox"] {
      width: 15px;
      height: 15px;
      accent-color: #b8934a;
      cursor: pointer;
    }

    .show-row label {
      font-size: 12px;
      color: #9a8a7c;
      cursor: pointer;
      user-select: none;
    }

    .btn-submit {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #b8934a, #d4a84b);
      border: none;
      border-radius: 2px;
      font-family: 'DM Sans', Arial, sans-serif;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #fff;
      cursor: pointer;
      transition: opacity .2s, transform .1s;
    }

    .btn-submit:hover { opacity: .9; }
    .btn-submit:active { transform: scale(.99); }

    .back-link {
      text-align: center;
      margin-top: 18px;
      font-size: 12px;
      color: #b0a090;
    }

    .back-link a { color: #b8934a; text-decoration: none; font-weight: 500; }

    .card-footer {
      background: #f2ece4;
      padding: 18px 44px;
      border-top: 1px solid #e4dbd0;
      font-size: 11px;
      color: #b0a090;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }
  </style>
</head>
<body>
<div class="card">
  <div class="bar"></div>

  <div class="card-header">
    <div class="logo">DRIP-N-STYLE</div>
    <div class="logo-line"></div>
  </div>

  <div class="card-body">
    <p class="title">Set New <span>Password</span></p>
    <p class="subtitle">Create a strong password for your account.</p>
    <div class="divider"></div>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="alert"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label class="field-label" for="new_password">New password</label>
      <div class="field">
        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" oninput="checkStrength()" required>
        <button type="button" class="eye-toggle" onclick="toggleField('new_password', this)" tabindex="-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>

      <div class="strength-wrap">
        <div class="strength-header">
          <span>Password strength</span>
          <span id="strength-text" style="color:#b0a090;">—</span>
        </div>
        <div class="strength-track">
          <div class="strength-fill" id="strength-fill"></div>
        </div>
      </div>

      <label class="field-label" for="confirm_password">Confirm password</label>
      <div class="field">
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" oninput="checkMatch()" required>
        <button type="button" class="eye-toggle" onclick="toggleField('confirm_password', this)" tabindex="-1">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <div class="match-msg" id="match-msg"></div>

      <div class="show-row">
        <input type="checkbox" id="showPassword" onchange="toggleAll(this)">
        <label for="showPassword">Show passwords</label>
      </div>

      <button type="submit" class="btn-submit">Update Password</button>
    </form>

    <div class="back-link">
      <a href="LoginPage.php">&#8592; Back to login</a>
    </div>
  </div>

  <div class="card-footer">DRIP-N-STYLE &nbsp;&middot;&nbsp; Secure account recovery</div>
  <div class="bar"></div>
</div>

<script>
  function toggleField(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    btn.style.color = isText ? '#b0a090' : '#b8934a';
  }

  function toggleAll(cb) {
    ['new_password', 'confirm_password'].forEach(id => {
      document.getElementById(id).type = cb.checked ? 'text' : 'password';
    });
  }

  function checkStrength() {
    const val = document.getElementById('new_password').value;
    const fill = document.getElementById('strength-fill');
    const text = document.getElementById('strength-text');
    let score = 0;
    if (val.length >= 8)           score++;
    if (/[A-Z]/.test(val))         score++;
    if (/[0-9]/.test(val))         score++;
    if (/[^A-Za-z0-9]/.test(val))  score++;
    const levels = [
      { w: '0%',   c: 'transparent', t: '—'      },
      { w: '25%',  c: '#d85a30',     t: 'Weak'   },
      { w: '50%',  c: '#d4a84b',     t: 'Fair'   },
      { w: '75%',  c: '#7aaa40',     t: 'Good'   },
      { w: '100%', c: '#3a8a50',     t: 'Strong' },
    ];
    const l = val.length === 0 ? levels[0] : (levels[score] || levels[1]);
    fill.style.width      = l.w;
    fill.style.background = l.c;
    text.textContent      = l.t;
    text.style.color      = l.c;
    checkMatch();
  }

  function checkMatch() {
    const p1  = document.getElementById('new_password').value;
    const p2  = document.getElementById('confirm_password').value;
    const msg = document.getElementById('match-msg');
    if (!p2) { msg.textContent = ''; return; }
    if (p1 === p2) {
      msg.style.color = '#3a8a50';
      msg.textContent = '✓ Passwords match';
    } else {
      msg.style.color = '#d85a30';
      msg.textContent = '✗ Passwords do not match';
    }
  }
</script>
</body>
</html>