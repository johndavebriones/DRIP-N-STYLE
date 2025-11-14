<?php
session_start();
require_once __DIR__ . '/../Helpers/SessionHelper.php';
require_once __DIR__ . '/../DAO/UserDAO.php';

SessionHelper::requireCustomerLogin();

class ProfileController {
    private $userDAO;
    private $user_id;

    public function __construct() {
        $this->userDAO = new UserDAO();
        $this->user_id = $_SESSION['user_id'];
    }

    // Update profile (name and email only, username change every 15 days)
    public function update() {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (empty($name) || empty($email) || empty($username)) {
            $_SESSION['error'] = "Name, email, and username are required.";
            header("Location: ../../Public/profile.php");
            exit;
        }

        $user = $this->userDAO->findById($this->user_id);

        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: ../../Public/profile.php");
            exit;
        }

        // Check if username changed
        if ($username !== $user['username']) {
            $lastChange = $user['last_username_change']; // DATETIME column in users table
            $now = new DateTime();
            $allowChange = true;

            if ($lastChange) {
                $lastChangeDate = new DateTime($lastChange);
                $diff = $now->diff($lastChangeDate)->days;

                if ($diff < 15) {
                    $allowChange = false;
                }
            }

            if (!$allowChange) {
                $_SESSION['error'] = "You can only change your username once every 15 days.";
                header("Location: ../../Public/profile.php");
                exit;
            }
        }

        $updated = $this->userDAO->updateUserProfile($this->user_id, $name, $email, $username);

        if ($updated) {
            // Update last_username_change if username was changed
            if ($username !== $user['username']) {
                $this->userDAO->updateLastUsernameChange($this->user_id, $now->format('Y-m-d H:i:s'));
            }
            $_SESSION['success'] = "Profile updated successfully.";
        } else {
            $_SESSION['error'] = "No changes were made.";
        }

        header("Location: ../../Public/profile.php");
        exit;
    }

    // Change password (unchanged)
    public function changePassword() {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = "All password fields are required.";
            header("Location: ../../Public/profile.php");
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New password and confirmation do not match.";
            header("Location: ../../Public/profile.php");
            exit;
        }

        $user = $this->userDAO->findById($this->user_id);
        if (!$user || !password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            header("Location: ../../Public/profile.php");
            exit;
        }

        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $this->userDAO->updatePassword($this->user_id, $hashedPassword);

        $_SESSION['success'] = "Password changed successfully.";
        header("Location: ../../Public/profile.php");
        exit;
    }
}

// Route actions
$action = $_GET['action'] ?? '';
$controller = new ProfileController();

if ($action === 'update') {
    $controller->update();
} elseif ($action === 'changePassword') {
    $controller->changePassword();
} else {
    header("Location: ../../Public/profile.php");
    exit;
}
