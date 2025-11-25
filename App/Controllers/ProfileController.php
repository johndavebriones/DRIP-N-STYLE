<?php
require_once __DIR__ . '/../DAO/UserDAO.php';
require_once __DIR__ . '/../Helpers/SessionHelper.php';

SessionHelper::requireCustomerLogin();
$user_id = $_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userDAO = new UserDAO();
$user = $userDAO->findById($user_id);

switch ($action) {

    // ✏️ Update profile info
    case 'update':
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact_number'] ?? '';

        if ($userDAO->updateUserFields($user_id, ['name' => $name, 'email' => $email, 'contact_number' => $contact])) {
            header("Location: ../../public/profile.php?success=profile_updated");
        } else {
            header("Location: ../../public/profile.php?error=profile_failed");
        }
        exit;
        break;

    // 🔒 Change password
    case 'changePassword':
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $user['password'])) {
            header("Location: ../../public/profile.php?error=wrong_current_password");
            exit;
        }

        if ($new_password !== $confirm_password) {
            header("Location: ../../public/profile.php?error=password_mismatch");
            exit;
        }

        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        if ($userDAO->updatePassword($user_id, $hashed)) {
            header("Location: ../../public/profile.php?pw_change=success");
            exit();
        } else {
            header("Location: ../../public/profile.php?pw_change=error");
            exit();
        }
        exit;
        break;

    default:
        header("HTTP/1.1 400 Bad Request");
        echo "Invalid action";
        exit;
}
