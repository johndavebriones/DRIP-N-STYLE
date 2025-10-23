<?php
session_start();
require_once __DIR__ . '/../config/database_connect.php';
$authConfig = require __DIR__ . '/../config/auth.php';

class AuthController {
    private $conn;
    private $config;

    public function __construct($config) {
        $database = new Database();
        $this->conn = $database->connect();
        $this->config = $config;
    }

    // USER REGISTRATION FUNCTION
    public function register($name, $email, $password) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "You already have an account, try logging in.";
            header("Location: " . $this->config['redirects']['on_error']);
            exit();
        }

        // Hash password
        $hashedPassword = password_hash($password, $this->config['password_algo']);

        // Insert new user
        $insert = "INSERT INTO users (name, email, password, role)
                   VALUES (:name, :email, :password, :role)";
        $stmt = $this->conn->prepare($insert);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $this->config['default_role']);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Account created successfully!";
            header("Location: " . $this->config['redirects']['after_register']);
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: " . $this->config['redirects']['on_error']);
        }
        exit();
    }

    // USER LOGIN FUNCTION
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION[$this->config['session_keys']['id']] = $user['user_id'];
                $_SESSION[$this->config['session_keys']['name']] = $user['name'];
                $_SESSION[$this->config['session_keys']['role']] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: " . $this->config['redirects']['after_login_admin']);
                } else {
                    header("Location: " . $this->config['redirects']['after_login_customer']);
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
                header("Location: " . $this->config['redirects']['on_error']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid credentials.";
            header("Location: " . $this->config['redirects']['on_error']);
            exit();
        }
    }

    // USER LOGOUT FUNCTION
    public function logout() {
        session_destroy();
        header("Location: " . $this->config['redirects']['after_logout']);
        exit();
    }
}

// ACTION HANDLER FUNCTION
if (isset($_GET['action'])) {
    $auth = new AuthController($authConfig);

    switch ($_GET['action']) {
        case 'register':
            if (isset($_POST['name'], $_POST['email'], $_POST['password'])) {
                $auth->register($_POST['name'], $_POST['email'], $_POST['password']);
            }
            break;

        case 'login':
            if (isset($_POST['email'], $_POST['password'])) {
                $auth->login($_POST['email'], $_POST['password']);
            }
            break;

        case 'logout':
            $auth->logout();
            break;

        default:
            echo "Invalid action.";
    }
}
