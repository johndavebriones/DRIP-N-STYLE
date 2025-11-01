<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionHelper {
    // Require login for admin pages
    public static function requireAdminLogin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header("Location: ../../Public/LoginPage.php");
            exit;
        }
    }

    // Require login for customer pages
    public static function requireCustomerLogin() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
            header("Location: ../../Public/LoginPage.php");
            exit;
        }
    }

    // Prevent logged-in users from accessing login/register again
    public static function redirectIfLoggedIn() {
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['role'] === 'admin') {
                header("Location: ../../Public/admin/dashboard.php");
            } else {
                header("Location: ../../Public/shop/shop.php");
            }
            exit;
        }
    }

    // Prevent page caching after logout
    public static function preventCache() {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}
