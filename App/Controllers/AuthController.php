<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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


    public function register($name, $email, $password, $confirmPassword, $dob = null, $parentalConsent = null) {
        $name  = trim($name);
        $email = trim($email);
        $password = trim($password);
        $confirmPassword = trim($confirmPassword);
        $dob = trim((string)($dob ?? ''));
        $consentGiven = ($parentalConsent === 'on' || $parentalConsent === '1' || $parentalConsent === 1 || $parentalConsent === true);

        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($dob)) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

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

        try {
            $birth = new DateTime($dob);
            $formattedDob = $birth->format('Y-m-d');
        } catch (Exception $e) {
            $_SESSION['error'] = "Invalid date of birth.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        $today = new DateTime();
        $age = $today->diff($birth)->y;

        if ($age < 13) {
            $_SESSION['error'] = "You must be at least 13 years old to create an account.";
            header("Location: ../../Public/RegisterPage.php");
            exit;
        }

        if ($age < 18 && !$consentGiven) {
            $_SESSION['error'] = "Parental consent is required for users under 18.";
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
        $user->birthdate = $formattedDob;

        if ($this->userDAO->registerUser($user)) {
            $_SESSION['user_id'] = $this->userDAO->findByEmail($email)['user_id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'customer';
            header("Location: ../../Public/LoginPage.php");
        } else {
            $_SESSION['error'] = "Registration failed. Please try again.";
            header("Location: ../../Public/RegisterPage.php");
        }
        exit;
    }

    public function login($email, $password) {
        $email = trim($email);
        $password = trim($password);

        $user = $this->userDAO->findByEmailForLogin($email);

        if (!$user) {
            $_SESSION['error'] = "Account not found. Please check your email.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Account is inactive. Please contact support.";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        // Check if account is locked
        if ($this->userDAO->isAccountLocked($user['user_id'])) {
            $lockedUntil = strtotime($user['locked_until']);
            $remainingTime = $lockedUntil - time();
            $minutes = ceil($remainingTime / 60);
            $_SESSION['error'] = "Account is locked due to too many failed attempts. Try again in {$minutes} minute(s).";
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            // Increment failed attempts
            $this->userDAO->incrementFailedAttempts($user['user_id']);
            
            // Check if we need to lock the account
            $updatedUser = $this->userDAO->findById($user['user_id']);
            if ($updatedUser['failed_attempts'] >= 3) {
                $this->userDAO->lockAccount($user['user_id']);
                $_SESSION['error'] = "Account locked due to 3 failed login attempts. Please try again later.";
            } else {
                $remaining = 3 - $updatedUser['failed_attempts'];
                $_SESSION['error'] = "Incorrect password. {$remaining} attempt(s) remaining.";
            }
            header("Location: ../../Public/LoginPage.php");
            exit;
        }

        // Successful login - reset failed attempts
        $this->userDAO->resetFailedAttempts($user['user_id']);

        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['last_activity'] = time(); // Set initial activity timestamp

        // ── Help Desk: force password change check ───────────────────────
        // If a help desk agent restored this account, the user must set a new
        // password before accessing any other page.
        if (!empty($user['force_password_change']) && $user['role'] === 'customer') {
            $_SESSION['force_password_change'] = true;
            header("Location: ../../Public/helpdesk_reset.php");
            exit;
        }

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: ../../Public/admin/dashboard.php");
        } else {
            header("Location: ../../Public/shop/shop.php");
        }
        exit;
    }

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

if (isset($_GET['action'])) {
    $auth = new AuthController();

    switch ($_GET['action']) {
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $auth->register(
                    $_POST['name'],
                    $_POST['email'],
                    $_POST['password'],
                    $_POST['confirm_password'],
                    $_POST['dob'] ?? null,
                    $_POST['parental_consent'] ?? null
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