<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../Config/database_connect.php';
require_once __DIR__ . '/../DAO/userDAO.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$vendorPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorPath)) {
    require_once $vendorPath;
}

header('Content-Type: application/json');

// ── Reset page state (used by ?reset=1 link) ─────────────────────────────
if (isset($_GET['reset'])) {
    unset(
        $_SESSION['fp_step'],
        $_SESSION['fp_email'],
        $_SESSION['fp_otp_sent_at'],
        $_SESSION['fp_verified_email']
    );
    header('Location: ../../Public/forgot-password.php');
    exit;
}

function json_out($success, $message = '', $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function sendOTPEmail($toEmail, $otp) {
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
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Code - DRIP-N-STYLE';
        $mail->Body    = getOTPEmailBody($otp);

        $mail->send();
        $mail->smtpClose();
        return ['ok' => true];
    } catch (Exception $e) {
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

function getOTPEmailBody($otp) {
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
            <div style="font-family:Georgia,serif;font-size:25px;color:#2d2520;margin-bottom:4px;">Password Reset</div>
            <div style="font-family:Georgia,serif;font-size:25px;color:#b8934a;font-weight:700;margin-bottom:26px;">Verification Code</div>
            <div style="height:1px;background:#ede8e2;margin-bottom:26px;"></div>
            <p style="font-size:14px;color:#6b5e54;line-height:1.85;margin:0 0 24px;">
                Use the code below to reset your <span style="color:#b8934a;font-weight:500;">DRIP-N-STYLE</span> password.
                This code expires in <strong>10 minutes</strong>.
            </p>
            <div style="background:#f2ece4;border:1px solid #e0d5c8;border-radius:4px;text-align:center;padding:28px;margin-bottom:28px;">
                <div style="font-family:Georgia,serif;font-size:42px;font-weight:700;letter-spacing:14px;color:#b8934a;">' . $otp . '</div>
            </div>
            <p style="font-size:12px;color:#b0a090;line-height:1.8;margin:0;">
                If you did not request this, you can safely ignore this email.
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

// ── Cooldown check helper ─────────────────────────────────────────────────
function checkCooldown($cooldownSeconds = 60) {
    $sentAt = $_SESSION['fp_otp_sent_at'] ?? 0;
    $elapsed = time() - $sentAt;
    if ($elapsed < $cooldownSeconds) {
        $wait = $cooldownSeconds - $elapsed;
        json_out(false, "Please wait {$wait} second(s) before requesting a new code.");
    }
}

switch ($action) {

    // ── Send OTP (first request) ──────────────────────────────────────────
    case 'send_otp':
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_out(false, 'Please enter a valid email address.');
        }

        $user = $userDAO->findByEmail($email);
        if (!$user) {
            json_out(false, 'No account found with that email address.');
        }

        // Rate limit: 60s cooldown per session
        checkCooldown(60);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if (!$userDAO->saveOTP($user['user_id'], $otp)) {
            json_out(false, 'Could not save OTP. Please try again.');
        }

        $result = sendOTPEmail($email, $otp);
        if (!$result['ok']) {
            json_out(false, 'SMTP error: ' . ($result['error'] ?? 'Unknown error'));
        }

        // Persist step and timestamp in session
        $_SESSION['fp_step']        = 'otp';
        $_SESSION['fp_email']       = $email;
        $_SESSION['fp_otp_sent_at'] = time();

        json_out(true, 'OTP sent successfully.');

    // ── Resend OTP ────────────────────────────────────────────────────────
    case 'resend_otp':
        if (($_SESSION['fp_step'] ?? '') !== 'otp' || empty($_SESSION['fp_email'])) {
            json_out(false, 'Session expired. Please start over.');
        }

        // Rate limit: 60s cooldown
        checkCooldown(60);

        $email = $_SESSION['fp_email'];
        $user  = $userDAO->findByEmail($email);
        if (!$user) json_out(false, 'Account not found.');

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        if (!$userDAO->saveOTP($user['user_id'], $otp)) {
            json_out(false, 'Could not save OTP. Please try again.');
        }

        $result = sendOTPEmail($email, $otp);
        if (!$result['ok']) {
            json_out(false, 'SMTP error: ' . ($result['error'] ?? 'Unknown error'));
        }

        $_SESSION['fp_otp_sent_at'] = time();
        json_out(true, 'Code resent successfully.');

    // ── Verify OTP ────────────────────────────────────────────────────────
    case 'verify_otp':
        if (($_SESSION['fp_step'] ?? '') !== 'otp' || empty($_SESSION['fp_email'])) {
            json_out(false, 'Session expired. Please start over.');
        }

        $email = $_SESSION['fp_email'];
        $otp   = trim($_POST['otp'] ?? '');

        if (strlen($otp) !== 6 || !ctype_digit($otp)) {
            json_out(false, 'Please enter the full 6-digit code.');
        }

        $result = $userDAO->verifyOTP($email, $otp);

        if (!$result['valid']) {
            $msg = $result['reason'] === 'expired'
                ? 'Code has expired. Please request a new one.'
                : 'Incorrect code. Please try again.';
            json_out(false, $msg);
        }

        // Advance step
        $_SESSION['fp_step']           = 'newpass';
        $_SESSION['fp_verified_email'] = $email;
        unset($_SESSION['fp_otp_sent_at']);

        json_out(true, 'Code verified.');

    // ── Reset Password ────────────────────────────────────────────────────
    case 'reset_password':
        if (
            ($_SESSION['fp_step'] ?? '') !== 'newpass' ||
            empty($_SESSION['fp_verified_email'])
        ) {
            json_out(false, 'Unauthorized. Please verify your code first.');
        }

        $email    = $_SESSION['fp_verified_email'];
        $password = $_POST['password'] ?? '';

        if (strlen($password) < 8 || strlen($password) > 12) {
            json_out(false, 'Password must be between 8 and 12 characters.');
        }

        $user = $userDAO->findByEmail($email);
        if (!$user) json_out(false, 'Account not found.');

        $updated = $userDAO->updatePassword($user['user_id'], password_hash($password, PASSWORD_DEFAULT));
        if (!$updated) json_out(false, 'Failed to update password. Please try again.');

        $userDAO->clearOTP($user['user_id']);

        // Clean all fp session data
        unset(
            $_SESSION['fp_step'],
            $_SESSION['fp_email'],
            $_SESSION['fp_otp_sent_at'],
            $_SESSION['fp_verified_email']
        );

        // Advance to success step
        $_SESSION['fp_step'] = 'success';

        json_out(true, 'Password reset successfully.');

    default:
        json_out(false, 'Invalid action.');
}