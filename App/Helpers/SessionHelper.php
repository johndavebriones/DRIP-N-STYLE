<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionHelper {

    public static function requireAdminLogin() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header("Location: /Websites/DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }
    }

    public static function requireCustomerLogin() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            header("Location: /Websites/DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }
    }

    public static function redirectIfLoggedIn() {
        if (isset($_SESSION['user_id'])) {
            switch ($_SESSION['role'] ?? '') {
                case 'admin':
                    header("Location: /Websites/DRIP-N-STYLE/Public/admin/dashboard.php");
                    break;
                case 'customer':
                    header("Location: /Websites/DRIP-N-STYLE/Public/shop/shop.php");
                    break;
                default:
                    header("Location: /Websites/DRIP-N-STYLE/Public/LoginPage.php");
                    break;
            }
            exit;
        }
    }

    public static function preventCache() {
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }
}
