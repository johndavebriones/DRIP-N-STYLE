<?php
session_start();

$config = require __DIR__ . '/../App/Config/email_config.php';
// Include database
require __DIR__ . '/../App/Config/database_connect.php'; // correct relative path

// Create a Database instance and get $conn
$db = new Database();
$conn = $db->connect(); // <- now $conn is defined

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
    $resetLink = "http://localhost/Websites/DRIP-N-STYLE/Public/reset-password.php?token=$token";

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $config['email'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('johndavealdaybriones009@gmail.com', 'Drip N Style');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body = "Hi,<br>Click this link to reset your password:<br><a href='$resetLink'>$resetLink</a>";

        $mail->send();
        $_SESSION['success'] = "Reset link sent! Check your email.";
        header("Location: LoginPage.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Email could not be sent. Error: {$mail->ErrorInfo}";
        header("Location: LoginPage.php");
        exit;
    }
} else {
    header("Location: LoginPage.php");
    exit;
}
?>
