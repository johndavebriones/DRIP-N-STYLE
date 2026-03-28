<?php
session_start();

$emailConfigPath = __DIR__ . '/../App/Config/email_auth.php';
if (!file_exists($emailConfigPath)) {
    $_SESSION['error'] = 'Server configuration error: email settings missing.';
    header('Location: LoginPage.php');
    exit;
}

$config = require $emailConfigPath;

require __DIR__ . '/../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();

// Include PHPMailer
require __DIR__ . '/assets/PHPMailer-7.0.0/src/PHPMailer.php';
require __DIR__ . '/assets/PHPMailer-7.0.0/src/SMTP.php';
require __DIR__ . '/assets/PHPMailer-7.0.0/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 0){
        $_SESSION['error'] = "Email not found!";
        header("Location: LoginPage.php");
        exit;
    }

    // Generate secure token
    $token = bin2hex(random_bytes(50));

    // Save token and expiry in DB
    $stmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
    $stmt->bind_param("ss", $token, $email);
    $stmt->execute();

    // Prepare reset link
    $resetLink = "http://localhost/DRIP-N-STYLE/Public/reset-password.php?token=$token";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = $config['smtp']['auth'];
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        $mail->SMTPSecure = $config['smtp']['encryption'];
        $mail->Port = $config['smtp']['port'];

        $mail->setFrom($config['from']['email'], $config['from']['name']);
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $config['forgot_password']['subject'];
        $body = str_replace('{reset_link}', $resetLink, $config['forgot_password']['body']);
        $mail->Body = $body;

        $mail->send();
        $_SESSION['error'] = 'Reset link sent to your email!';
        header("Location: LoginPage.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Email send failed: {$e->getMessage()}";
        header("Location: LoginPage.php");
        exit;
    }
} else {
    header("Location: LoginPage.php");
    exit;
}
?>
