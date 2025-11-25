<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class SessionHelper {

    // Session timeout in seconds (15 minutes = 900 seconds)
    const SESSION_TIMEOUT = 60;

    /**
     * Check if session has timed out due to inactivity
     * @return bool True if session timed out, false otherwise
     */
    public static function checkSessionTimeout() {
        if (isset($_SESSION['user_id']) && isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > self::SESSION_TIMEOUT) {
                // Session has expired
                $_SESSION = [];
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                    );
                }
                session_destroy();
                return true;
            }
            
            // Update last activity timestamp
            $_SESSION['last_activity'] = time();
        }
        
        return false;
    }

    public static function requireAdminLogin() {
        // Check for timeout first
        if (self::checkSessionTimeout()) {
            $_SESSION['timeout_alert'] = "Your session has expired due to inactivity. Please login again.";
            header("Location: /DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
            header("Location: /DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }
    }

    public static function requireCustomerLogin() {
        // Check for timeout first
        if (self::checkSessionTimeout()) {
            $_SESSION['timeout_alert'] = "Your session has expired due to inactivity. Please login again.";
            header("Location: /DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }

        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            header("Location: /DRIP-N-STYLE/Public/LoginPage.php");
            exit;
        }
    }

    public static function redirectIfLoggedIn() {
        // Check timeout but don't show error message if redirecting from login page
        if (isset($_SESSION['user_id'])) {
            if (self::checkSessionTimeout()) {
                return; // Session expired, user can proceed to login page
            }

            switch ($_SESSION['role'] ?? '') {
                case 'admin':
                    header("Location: /DRIP-N-STYLE/Public/admin/dashboard.php");
                    break;
                case 'customer':
                    header("Location: /DRIP-N-STYLE/Public/shop/shop.php");
                    break;
                default:
                    header("Location: /DRIP-N-STYLE/Public/LoginPage.php");
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