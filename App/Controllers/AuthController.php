<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database_connect.php';

class AuthController {
    private $conn;

    public function __construct() {
        $database = new Database(); // create an instance of Database class
        $this->conn = $database->connect(); // call connect() to get $conn
    }

    // LOGIN FUNCTION
    public function login($emailOrName, $password) {
        $query = "SELECT * FROM users WHERE email = ? OR name = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

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

        // Successful login
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../../Public/admin/dashboard.php");// admin page
        } else {
            header("Location: ../../Public/shop/shop.php"); // customer page
        }
        exit;
    }

    // LOGOUT FUNCTION
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        header("Pragma: no-cache");
        header("Expires: 0");

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
