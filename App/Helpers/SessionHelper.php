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
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    }

}
