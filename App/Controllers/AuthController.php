<?php
session_start();
require_once __DIR__ . '/../config/database_connect.php';

class AuthController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // USER REGISTRATION FUNCTION
    public function register($name, $email, $password) {
        // Check if email already exists
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "You already have an account, try logging in.";
            header("Location: ../../public/auth.php");
            exit();
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $insert = "INSERT INTO users (name, email, password, role) 
                   VALUES (:name, :email, :password, 'customer')";
        $stmt = $this->conn->prepare($insert);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Account created successfully!";
            header("Location: ../../public/auth.php");
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: ../../public/auth.php");
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
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../../admin/dashboard.php");
                } else {
                    header("Location: ../../public/shop.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
                header("Location: ../../public/auth.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid credentials.";
            header("Location: ../../public/auth.php");
            exit();
        }
    }

    // USER LOGOUT FUNCTION
    public function logout() {
        session_destroy();
        header("Location: ../../public/index.php");
        exit();
    }
}

// ACTION HANDLER FUNCTION
if (isset($_GET['action'])) {
    $auth = new AuthController();

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
