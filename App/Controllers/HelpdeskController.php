<?php
/**
 * HelpdeskController
 * 
 * Handles all Help Desk actions:
 *   1. Customer lookup
 *   2. Identity verification (minimum 2 factors required)
 *   3. Account unlock / status reset
 *   4. Secure password-reset token dispatch (via email)
 *   5. Audit logging
 * 
 * Principle of Least Privilege:
 *   - Agent session required (admin role).
 *   - Password is NEVER retrieved or displayed.
 *   - Agents cannot edit: email, name, birthdate, contact, role, or any sensitive field.
 *   - Agents can ONLY perform: unlock + reactivate via restricted DAO methods.
 */

ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../DAO/helpdeskDAO.php';
require_once __DIR__ . '/../Helpers/SessionHelper.php';
require_once __DIR__ . '/../Config/email_auth.php';

// PHPMailer
$phpMailerBase = __DIR__ . '/../../Public/assets/PHPMailer-7.0.0/src/';
require_once $phpMailerBase . 'Exception.php';
require_once $phpMailerBase . 'PHPMailer.php';
require_once $phpMailerBase . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class HelpdeskController {

    private HelpdeskDAO $dao;
    private array $emailConfig;

    // The four supported verification factors and how they are validated
    private const FACTORS = [
        'full_name'       => 'Registered Full Name',
        'email_address'   => 'Email Address',
        'last_order_id'   => 'Last Order ID',
        'last_order_date' => 'Date of Last Purchase',
    ];

    public function __construct() {
        $this->dao         = new HelpdeskDAO();
        $this->emailConfig = require __DIR__ . '/../Config/email_auth.php';
    }

    // ── Entry point (called from helpdesk.php) ───────────────────────────

    public function handle(): void {
        // Agent must be logged in as admin
        SessionHelper::requireAdminLogin();

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'lookup':
                $this->handleLookup();
                break;
            case 'verify':
                $this->handleVerify();
                break;
            case 'unlock':
                $this->handleAccountAction('unlock');
                break;
            case 'reactivate':
                $this->handleAccountAction('reactivate');
                break;
            default:
                $this->fail('Invalid action.');
        }
    }

    // ── Step 1: Customer Lookup ───────────────────────────────────────────

    private function handleLookup(): void {
        $email = trim($_POST['customer_email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('Please enter a valid customer email address.');
        }

        $customer = $this->dao->findCustomerByEmail($email);
        if (!$customer) {
            $this->fail('No customer account found with that email address.');
        }

        // Store customer info in session for subsequent steps
        $_SESSION['hd_customer_id']       = $customer['user_id'];
        $_SESSION['hd_customer_email']    = $customer['email'];
        $_SESSION['hd_verified_factors']  = [];
        $_SESSION['hd_verification_done'] = false;

        // Fetch orders for UI display
        $orders = $this->dao->getCustomerOrders($customer['user_id']);

        $this->ok([
            'customer' => $customer,
            'orders'   => $orders,
            'factors'  => self::FACTORS,
        ]);
    }

    // ── Step 2: Identity Verification ────────────────────────────────────

    private function handleVerify(): void {
        $this->requireLookup();

        $userId   = (int)$_SESSION['hd_customer_id'];
        $customer = $this->dao->findCustomerById($userId);

        if (!$customer) {
            $this->fail('Customer session expired. Please perform a new lookup.');
        }

        $selectedFactors = $_POST['factors'] ?? [];
        if (!is_array($selectedFactors) || count($selectedFactors) < 2) {
            $this->fail('You must select and verify at least two (2) identity factors.');
        }

        $verifiedLabels = [];
        $errors         = [];

        foreach ($selectedFactors as $factorKey) {
            if (!array_key_exists($factorKey, self::FACTORS)) {
                $errors[] = "Unknown factor: {$factorKey}";
                continue;
            }

            $value = trim($_POST["factor_{$factorKey}"] ?? '');
            if ($value === '') {
                $errors[] = self::FACTORS[$factorKey] . ' value is required.';
                continue;
            }

            if (!$this->checkFactor($factorKey, $value, $customer, $userId)) {
                $errors[] = self::FACTORS[$factorKey] . ' does not match our records.';
            } else {
                $verifiedLabels[] = self::FACTORS[$factorKey];
            }
        }

        if (!empty($errors)) {
            $this->fail(implode(' | ', $errors));
        }

        if (count($verifiedLabels) < 2) {
            $this->fail('Verification failed. At least two factors must be confirmed.');
        }

        // Mark verification complete in session
        $_SESSION['hd_verified_factors']  = $verifiedLabels;
        $_SESSION['hd_verification_done'] = true;

        $this->ok([
            'verified_factors' => $verifiedLabels,
            'customer'         => $customer,
            'orders'           => $this->dao->getCustomerOrders($userId),
        ]);
    }

    /**
     * Validate a single factor value against the customer record.
     */
    private function checkFactor(string $key, string $value, array $customer, int $userId): bool {
        switch ($key) {
            case 'full_name':
                return mb_strtolower(trim($customer['name'])) === mb_strtolower($value);

            case 'email_address':
                return mb_strtolower(trim($customer['email'])) === mb_strtolower($value);

            case 'last_order_id':
                return $this->dao->verifyOrderBelongsToUser($userId, (int)$value);

            case 'last_order_date':
                $lastOrderDate = $this->dao->getLastOrderDate($userId);
                if (!$lastOrderDate) return false;
                // Accept any date format — normalise to Y-m-d
                $provided = date('Y-m-d', strtotime($value));
                $stored   = date('Y-m-d', strtotime($lastOrderDate));
                return $provided === $stored;

            default:
                return false;
        }
    }

    // ── Step 3: Account Action ────────────────────────────────────────────

    private function handleAccountAction(string $type): void {
        $this->requireVerification();

        $userId  = (int)$_SESSION['hd_customer_id'];
        $agentId = (int)$_SESSION['user_id'];
        $factors = $_SESSION['hd_verified_factors'];

        $customer = $this->dao->findCustomerById($userId);
        if (!$customer) {
            $this->fail('Customer not found.');
        }

        if ($type === 'unlock') {
            // Only applicable when account is locked by failed attempts
            if (empty($customer['locked_until']) || strtotime($customer['locked_until']) <= time()) {
                $this->fail('This account is not currently locked by failed login attempts.');
            }
            $success    = $this->dao->unlockAccount($userId);
            $actionType = 'Account Unlocked';
        } elseif ($type === 'reactivate') {
            if ($customer['status'] !== 'suspended') {
                $this->fail('This account is not in a suspended state.');
            }
            $success    = $this->dao->reactivateAccount($userId);
            $actionType = 'Status Reset to Active';
        } else {
            $this->fail('Unknown action type.');
        }

        if (!$success) {
            $this->fail('The action could not be completed. Please try again or verify the account state.');
        }

        // Audit log
        $this->dao->logAction($agentId, $userId, $factors, $actionType,
            "Agent performed helpdesk action via verification panel.");

        // Issue password reset token & send email
        $rawToken  = $this->dao->saveResetToken($userId);
        $emailSent = $this->sendResetEmail($customer, $rawToken);

        // Clear helpdesk session state
        $this->clearHelpdeskSession();

        $this->ok([
            'action_type' => $actionType,
            'email_sent'  => $emailSent,
            'customer_email' => $customer['email'],
        ]);
    }

    // ── Email ─────────────────────────────────────────────────────────────

    private function sendResetEmail(array $customer, string $rawToken): bool {
        $cfg   = $this->emailConfig;
        $smtp  = $cfg['smtp'];
        $from  = $cfg['from'];

        $baseUrl   = rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'], '/');
        $resetLink = $baseUrl . '/DRIP-N-STYLE/Public/helpdesk_reset.php?token=' . urlencode($rawToken);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp['host'];
            $mail->SMTPAuth   = $smtp['auth'];
            $mail->Username   = $smtp['username'];
            $mail->Password   = $smtp['password'];
            $mail->SMTPSecure = $smtp['encryption'];
            $mail->Port       = $smtp['port'];

            $mail->setFrom($from['email'], $from['name']);
            $mail->addAddress($customer['email'], $customer['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Your DRIP-N-STYLE Account Has Been Restored';
            $mail->Body    = $this->buildResetEmailBody($customer['name'], $resetLink);
            $mail->AltBody = "Your account has been restored by our Help Desk team.\n\n"
                           . "For your security, you must set a new password before continuing.\n\n"
                           . "Click this link (valid for 60 minutes):\n{$resetLink}";

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log("HelpdeskController: email failed for user {$customer['user_id']}: " . $e->getMessage());
            return false;
        }
    }

    private function buildResetEmailBody(string $name, string $resetLink): string {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:20px;background:#f5f0eb;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0eb;padding:40px 20px;">
  <tr><td align="center">
    <table width="520" cellpadding="0" cellspacing="0" style="background:#faf8f5;border:1px solid #e8e0d8;border-radius:3px;">
      <tr><td style="background:linear-gradient(135deg,#b8934a,#d4a84b,#c9a96e);padding:3px 0;"></td></tr>
      <tr><td style="padding:40px 48px 30px;border-bottom:1px solid #ede8e2;">
        <div style="font-family:Georgia,serif;font-size:21px;font-weight:700;letter-spacing:6px;color:#b8934a;text-transform:uppercase;">DRIP-N-STYLE</div>
      </td></tr>
      <tr><td style="padding:44px 48px 36px;">
        <div style="font-family:Georgia,serif;font-size:22px;color:#2d2520;margin-bottom:20px;">Account Restored 🔓</div>
        <p style="font-size:14px;color:#5c4f44;line-height:1.8;margin:0 0 12px;">Hello <strong>{$safeName}</strong>,</p>
        <p style="font-size:14px;color:#6b5e54;line-height:1.8;margin:0 0 24px;">
          Our Help Desk team has restored your <strong style="color:#b8934a;">DRIP-N-STYLE</strong> account.
          For your security, you are required to set a new password before you can continue shopping.
        </p>
        <p style="font-size:13px;color:#888;margin:0 0 28px;">This link is valid for <strong>60 minutes</strong>.</p>
        <table cellpadding="0" cellspacing="0"><tr>
          <td style="background:#b8934a;border-radius:3px;padding:14px 32px;">
            <a href="{$safeLink}" style="color:#fff;text-decoration:none;font-size:14px;font-weight:700;letter-spacing:1px;">SET NEW PASSWORD</a>
          </td>
        </tr></table>
        <p style="font-size:12px;color:#aaa;margin-top:30px;">
          If you did not request this, please contact our support team immediately.<br>
          Do not share this link with anyone.
        </p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    // ── Session Helpers ───────────────────────────────────────────────────

    private function requireLookup(): void {
        if (empty($_SESSION['hd_customer_id'])) {
            $this->fail('Please perform a customer lookup first.');
        }
    }

    private function requireVerification(): void {
        $this->requireLookup();
        if (empty($_SESSION['hd_verification_done'])) {
            $this->fail('Identity verification has not been completed.');
        }
    }

    private function clearHelpdeskSession(): void {
        unset(
            $_SESSION['hd_customer_id'],
            $_SESSION['hd_customer_email'],
            $_SESSION['hd_verified_factors'],
            $_SESSION['hd_verification_done']
        );
    }

    // ── JSON Response Helpers ─────────────────────────────────────────────

    private function ok(array $data): void {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function fail(string $message, int $code = 400): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

// ── Dispatch ─────────────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new HelpdeskController())->handle();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
}
