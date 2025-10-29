<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database_connect.php';

class AuthController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // REGISTER FUNCTION
    public function register($name, $email, $password, $confirmPassword) {
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        // Check if email already exists
        $check = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already exists.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insert = $this->conn->prepare("
            INSERT INTO users (name, email, password, role, status) 
            VALUES (?, ?, ?, 'customer', 'active')
        ");
        $insert->bind_param("sss", $name, $email, $hashedPassword);

        if ($insert->execute()) {
            $_SESSION['success'] = "Account created successfully. You can now login.";
            header("Location: ../../Public/LoginPage.php");
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: ../../Public/RegisterPage.php");
        }
        exit;
    }

    // LOGIN FUNCTION
    public function login($emailOrName, $password) {
        $query = "SELECT * FROM users WHERE email = ? OR name = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) die("Prepare failed: " . $this->conn->error);

        $stmt->bind_param("ss", $emailOrName, $emailOrName);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $_SESSION['error'] = "Account not found. Please check your email or username.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Incorrect password. Please try again.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // Redirect by role
        if ($user['role'] === 'admin') {
            header("Location: ../../Public/admin/dashboard.php");
        } else {
            header("Location: ../../Public/shop/shop.php");
        }
        exit;
    }

    // LOGOUT FUNCTION
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Location: ../../Public/LoginPage.php");
        exit;
    }
}

// ACTION HANDLER
if (isset($_GET['action'])) {
    $auth = new AuthController();

    switch ($_GET['action']) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $auth->register($_POST['name'], $_POST['email'], $_POST['password'], $_POST['confirm_password']);
            }
            break;

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
