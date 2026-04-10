<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../Config/database_connect.php';
require_once __DIR__ . '/../DAO/userDAO.php';
require_once __DIR__ . '/../Models/userModel.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) require_once $vendorPath;

header('Content-Type: application/json');

function json_out($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function sendRegOTPEmail($toEmail, $toName, $otp) {
    $config = require __DIR__ . '/../Config/email_auth.php';

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return ['ok' => false, 'error' => 'PHPMailer not installed. Run: composer require phpmailer/phpmailer'];
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host        = $config['smtp']['host'];
        $mail->SMTPAuth    = $config['smtp']['auth'];
        $mail->Username    = $config['smtp']['username'];
        $mail->Password    = $config['smtp']['password'];
        $mail->SMTPSecure  = $config['smtp']['encryption'];
        $mail->Port        = $config['smtp']['port'];
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->Timeout       = 15;
        $mail->SMTPKeepAlive = true;

        $mail->setFrom($config['from']['email'], $config['from']['name']);
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - DRIP-N-STYLE';
        $mail->Body    = getRegOTPEmailBody($toName, $otp);

        $mail->send();
        $mail->smtpClose();
        return ['ok' => true];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

function getRegOTPEmailBody($name, $otp) {
    $firstName = explode(' ', trim($name))[0];
    return '
    <!DOCTYPE html><html><body style="margin:0;padding:20px;background:#f5f0eb;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0eb;padding:40px 20px;">
    <tr><td align="center">
    <table width="520" cellpadding="0" cellspacing="0" style="background:#faf8f5;border:1px solid #e8e0d8;border-radius:3px;overflow:hidden;">
        <tr><td style="background:linear-gradient(135deg,#b8934a 0%,#d4a84b 50%,#c9a96e 100%);padding:3px 0;"></td></tr>
        <tr><td style="padding:40px 48px 30px;border-bottom:1px solid #ede8e2;">
            <div style="font-family:Georgia,serif;font-size:21px;font-weight:700;letter-spacing:6px;color:#b8934a;text-transform:uppercase;">DRIP-N-STYLE</div>
        </td></tr>
        <tr><td style="padding:44px 48px 36px;">
            <div style="font-family:Georgia,serif;font-size:25px;color:#2d2520;margin-bottom:4px;">Welcome,</div>
            <div style="font-family:Georgia,serif;font-size:25px;color:#b8934a;font-weight:700;margin-bottom:26px;">' . htmlspecialchars($firstName) . '!</div>
            <div style="height:1px;background:#ede8e2;margin-bottom:26px;"></div>
            <p style="font-size:14px;color:#6b5e54;line-height:1.85;margin:0 0 24px;">
                Use the code below to verify your email and complete your
                <span style="color:#b8934a;font-weight:500;">DRIP-N-STYLE</span> registration.
                This code expires in <strong>10 minutes</strong>.
            </p>
            <div style="background:#f2ece4;border:1px solid #e0d5c8;border-radius:4px;text-align:center;padding:28px;margin-bottom:28px;">
                <div style="font-size:11px;letter-spacing:3px;color:#9a8a7c;text-transform:uppercase;margin-bottom:10px;">Verification Code</div>
                <div style="font-family:Georgia,serif;font-size:42px;font-weight:700;letter-spacing:14px;color:#b8934a;">' . $otp . '</div>
            </div>
            <p style="font-size:12px;color:#b0a090;line-height:1.8;margin:0;">
                If you did not request this, someone may have used your email to register. You can safely ignore this email.
            </p>
        </td></tr>
        <tr><td style="background:#f2ece4;padding:26px 48px;border-top:1px solid #e4dbd0;">
            <p style="font-size:11px;color:#b0a090;margin:0;letter-spacing:2px;text-transform:uppercase;">DRIP-N-STYLE Team</p>
            <p style="font-size:11px;color:#c0b5a8;margin:4px 0 0;">This is an automated message. Please do not reply.</p>
        </td></tr>
        <tr><td style="background:linear-gradient(135deg,#b8934a 0%,#d4a84b 50%,#c9a96e 100%);padding:2px 0;"></td></tr>
    </table></td></tr></table>
    </body></html>';
}

$action  = $_GET['action'] ?? '';
$userDAO = new UserDAO();

// ── Cooldown helper ───────────────────────────────────────────────────────
function checkCooldown($cooldownSeconds = 60) {
    $sentAt  = $_SESSION['reg_otp_sent_at'] ?? 0;
    $elapsed = time() - $sentAt;
    if ($elapsed < $cooldownSeconds) {
        $wait = $cooldownSeconds - $elapsed;
        json_out(false, "Please wait {$wait} second(s) before requesting a new code.");
    }
}

switch ($action) {

    // ── Validate form + send OTP ──────────────────────────────────────────
    case 'send_otp':
        $name            = trim($_POST['name'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $dob             = trim($_POST['dob'] ?? '');
        $parentalConsent = $_POST['parental_consent'] ?? '';

        // ── Validation ────────────────────────────────────────────────────
        if (empty($name) || empty($email) || empty($password) || empty($dob)) {
            json_out(false, 'Please fill in all required fields.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_out(false, 'Please enter a valid email address.');
        }
        if ($password !== $confirmPassword) {
            json_out(false, 'Passwords do not match.');
        }
        if (strlen($password) < 8 || strlen($password) > 12) {
            json_out(false, 'Password must be between 8 and 12 characters.');
        }
        if ($userDAO->findByEmail($email)) {
            json_out(false, 'That email address is already registered.');
        }

        // DOB + age check
        try {
            $birth        = new DateTime($dob);
            $formattedDob = $birth->format('Y-m-d');
        } catch (Exception $e) {
            json_out(false, 'Invalid date of birth.');
        }

        $age = (new DateTime())->diff($birth)->y;

        if ($age < 13) {
            json_out(false, 'You must be at least 13 years old to register.');
        }
        if ($age < 18 && $parentalConsent !== 'on' && $parentalConsent !== '1') {
            json_out(false, 'Parental consent is required for users under 18.');
        }

        // ── Rate limit ────────────────────────────────────────────────────
        checkCooldown(60);

        // ── Generate OTP + store pending user in session ──────────────────
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['reg_pending'] = [
            'name'             => $name,
            'email'            => $email,
            'password'         => password_hash($password, PASSWORD_DEFAULT),
            'dob'              => $formattedDob,
            'parental_consent' => ($age < 18),
        ];
        $_SESSION['reg_otp']        = $otp;
        $_SESSION['reg_otp_expiry'] = time() + 600; // 10 min
        $_SESSION['reg_otp_sent_at'] = time();
        $_SESSION['reg_step']        = 'otp';

        $result = sendRegOTPEmail($email, $name, $otp);
        if (!$result['ok']) {
            // Clear session so user can retry
            unset($_SESSION['reg_step'], $_SESSION['reg_otp'], $_SESSION['reg_otp_expiry'], $_SESSION['reg_otp_sent_at']);
            json_out(false, 'SMTP error: ' . ($result['error'] ?? 'Unknown error'));
        }

        json_out(true, 'Verification code sent to ' . $email);

    // ── Resend OTP ────────────────────────────────────────────────────────
    case 'resend_otp':
        if (($_SESSION['reg_step'] ?? '') !== 'otp' || empty($_SESSION['reg_pending'])) {
            json_out(false, 'Session expired. Please fill the form again.');
        }

        checkCooldown(60);

        $pending = $_SESSION['reg_pending'];
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $_SESSION['reg_otp']         = $otp;
        $_SESSION['reg_otp_expiry']  = time() + 600;
        $_SESSION['reg_otp_sent_at'] = time();

        $result = sendRegOTPEmail($pending['email'], $pending['name'], $otp);
        if (!$result['ok']) {
            json_out(false, 'SMTP error: ' . ($result['error'] ?? 'Unknown error'));
        }

        json_out(true, 'Code resent successfully.');

    // ── Verify OTP + create account ───────────────────────────────────────
    case 'verify_otp':
        if (($_SESSION['reg_step'] ?? '') !== 'otp' || empty($_SESSION['reg_pending'])) {
            json_out(false, 'Session expired. Please fill the form again.');
        }

        $otp = trim($_POST['otp'] ?? '');

        if (strlen($otp) !== 6 || !ctype_digit($otp)) {
            json_out(false, 'Please enter the full 6-digit code.');
        }
        if (time() > ($_SESSION['reg_otp_expiry'] ?? 0)) {
            unset($_SESSION['reg_otp'], $_SESSION['reg_otp_expiry']);
            json_out(false, 'Code has expired. Please request a new one.');
        }
        if ($_SESSION['reg_otp'] !== $otp) {
            json_out(false, 'Incorrect code. Please try again.');
        }

        // ── Create account ────────────────────────────────────────────────
        $pending = $_SESSION['reg_pending'];

        // Double-check email still not taken (race condition guard)
        if ($userDAO->findByEmail($pending['email'])) {
            json_out(false, 'That email was registered by someone else. Please use a different email.');
        }

        $user                 = new UserModel();
        $user->name           = $pending['name'];
        $user->email          = $pending['email'];
        $user->password       = $pending['password']; // already hashed
        $user->role           = 'customer';
        $user->status         = 'active';
        $user->birthdate      = $pending['dob'];
        $user->contact_number = null;

        if (!$userDAO->registerUser($user)) {
            json_out(false, 'Registration failed. Please try again.');
        }

        // Clean up session
        unset(
            $_SESSION['reg_pending'],
            $_SESSION['reg_otp'],
            $_SESSION['reg_otp_expiry'],
            $_SESSION['reg_otp_sent_at'],
            $_SESSION['reg_step']
        );

        $_SESSION['reg_success'] = true;
        json_out(true, 'Account created successfully!');

    default:
        json_out(false, 'Invalid action.');
}