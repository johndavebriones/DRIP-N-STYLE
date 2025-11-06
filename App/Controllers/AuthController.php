<?php
ini_set('session.cookie_path', '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../DAO/userDAO.php';
require_once __DIR__ . '/../Models/userModel.php';
require_once __DIR__ . '/../Helpers/SessionHelper.php';

class AuthController {
    private $userDAO;

    public function __construct() {
        $this->userDAO = new UserDAO();
    }

// REGISTER NEW USER
    public function register($name, $email, $password, $confirmPassword) {
        $name  = trim($name);
        $email = trim($email);
        $password = trim($password);
        $confirmPassword = trim($confirmPassword);

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        if ($this->userDAO->findByEmail($email)) {
            $_SESSION['error'] = "Email already exists.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        $user = new UserModel();
        $user->name = $name;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->role = 'customer';
        $user->status = 'active';
        $user->contact_number = null;

        if ($this->userDAO->registerUser($user)) {
            $_SESSION['user_id'] = $this->userDAO->findByEmail($email)['user_id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'customer';
            header("Location: ../../Public/shop/shop.php");
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: ../../Public/RegisterPage.php");
        }
        exit;
    }

// LOGIN USER
    public function login($email, $password) {
    $email = trim($email);
    $password = trim($password);

    $user = $this->userDAO->findByEmail($email);

    if (!$user) {
        $_SESSION['error'] = "Account not found. Please check your email.";
        header("Location: ../../Public/LoginPage.php");
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Incorrect password. Please try again.";
        header("Location: ../../Public/LoginPage.php");
        exit;
    }

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role']      = $user['role'];

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../../Public/admin/dashboard.php");
    } else {
        header("Location: ../../Public/shop/shop.php");
    }
    exit;
}

// LOGOUT USER
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

        header("Location: ../../Public/LoginPage.php");
        exit;
    }
}

// HANDLE ACTION
if (isset($_GET['action'])) {
    $auth = new AuthController();

    switch ($_GET['action']) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $auth->register(
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['password'],
                    $_POST['confirm_password']
                );
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
