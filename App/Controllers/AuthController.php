<?php
session_start();
require_once __DIR__ . '/../config/database_connect.php';

class AuthController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // LOGIN FUNCTION
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 🔸 If email not found
        if (!$user) {
            $_SESSION['error'] = "Email not found. Please try again.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        // 🔸 If password is incorrect
        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Incorrect password. Please try again.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        // ✅ Successful login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../../Public/shop.php");
        exit;
    }

    // LOG OUT FUNCTION
    public function logout() {
        // Destroy the session safely
        session_unset();
        session_destroy();

        // Redirect back to login
        header("Location: ../../Public/LoginPage.php");
        exit;
    }
}

// ACTION HANDLER
if (isset($_GET['action'])) {
    $auth = new AuthController();

    switch ($_GET['action']) {
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $auth->login($_POST['email'], $_POST['password']);
            }
            break;

        case 'logout':
            $auth->logout();
            break;

        default:
            header("Location: ../../Public/LoginPage.php");
            exit;
    }
}
