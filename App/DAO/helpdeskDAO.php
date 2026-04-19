<?php
/**
 * HelpdeskDAO
 * 
 * Principle of Least Privilege enforced:
 *  - Agents can ONLY see: name, email, status, locked_until, failed_attempts, date_created, user_id
 *  - Agents can NEVER retrieve: password, reset_token, token_expiry, birthdate, gender, contact_number
 *  - Order history (order_id, order_date, total_amount) is readable for verification only.
 *  - Write access is restricted to: status, locked_until, failed_attempts, force_password_change.
 * 
 * All queries use PDO with prepared statements to prevent SQL injection.
 */

require_once __DIR__ . '/../Config/database_connect.php';

class HelpdeskDAO {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = $this->makePDO();
    }

    // ── PDO Connection ────────────────────────────────────────────────────

    private function makePDO(): PDO {
        // Read mysqli credentials from existing Database config
        // Mirrors the values in database_connect.php
        $host   = '127.0.0.1';
        $dbname = 'dripnstyle';
        $user   = 'root';
        $pass   = '';
        $port   = 3306;

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        return new PDO($dsn, $user, $pass, $options);
    }

    // ── Customer Lookup (read-only, least privilege) ─────────────────────

    /**
     * Find a customer by email — returns only agent-visible fields, NEVER password.
     */
    public function findCustomerByEmail(string $email): ?array {
        $sql = "SELECT user_id, name, email, status, failed_attempts, locked_until, date_created
                FROM users
                WHERE email = :email AND role = 'customer'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find a customer by user_id — returns only agent-visible fields.
     */
    public function findCustomerById(int $userId): ?array {
        $sql = "SELECT user_id, name, email, status, failed_attempts, locked_until, date_created
                FROM users
                WHERE user_id = :uid AND role = 'customer'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get the customer's last N orders (used for verification and display).
     * Returns: order_id, order_date, total_amount, order_status only.
     */
    public function getCustomerOrders(int $userId, int $limit = 5): array {
        $sql = "SELECT order_id, order_date, total_amount, order_status
                FROM orders
                WHERE user_id = :uid
                ORDER BY order_date DESC
                LIMIT :lim";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get the most recent order date for a customer (for verification).
     */
    public function getLastOrderDate(int $userId): ?string {
        $sql = "SELECT MAX(order_date) AS last_order_date FROM orders WHERE user_id = :uid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row['last_order_date'] ?? null;
    }

    /**
     * Verify identity factor: does a given order_id belong to this customer?
     */
    public function verifyOrderBelongsToUser(int $userId, int $orderId): bool {
        $sql = "SELECT COUNT(*) FROM orders WHERE user_id = :uid AND order_id = :oid";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':uid' => $userId, ':oid' => $orderId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── Account Status Actions (write, restricted) ────────────────────────

    /**
     * Unlock an account that was locked by failed login attempts.
     * Resets: failed_attempts = 0, locked_until = NULL.
     * Sets: force_password_change = 1.
     */
    public function unlockAccount(int $userId): bool {
        $sql = "UPDATE users
                SET failed_attempts = 0,
                    locked_until = NULL,
                    force_password_change = 1
                WHERE user_id = :uid AND role = 'customer'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':uid' => $userId]) && $stmt->rowCount() > 0;
    }

    /**
     * Reset a suspended account back to 'active'.
     * Sets: force_password_change = 1.
     */
    public function reactivateAccount(int $userId): bool {
        $sql = "UPDATE users
                SET status = 'active',
                    force_password_change = 1
                WHERE user_id = :uid AND role = 'customer' AND status = 'suspended'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':uid' => $userId]) && $stmt->rowCount() > 0;
    }

    // ── Audit Logging ─────────────────────────────────────────────────────

    /**
     * Insert a record into helpdesk_action_logs.
     * 
     * @param int    $agentId           The admin/helpdesk agent's user_id.
     * @param int    $targetUserId      The customer's user_id.
     * @param array  $verificationMethods  e.g. ['Full Name','Email Address','Last Order ID']
     * @param string $actionType        e.g. 'Account Unlocked', 'Status Reset to Active'
     * @param string $notes             Optional notes.
     */
    public function logAction(
        int $agentId,
        int $targetUserId,
        array $verificationMethods,
        string $actionType,
        string $notes = ''
    ): bool {
        $sql = "INSERT INTO helpdesk_action_logs
                    (agent_id, target_user_id, verification_method, action_type, notes)
                VALUES
                    (:agent_id, :target_user_id, :verification_method, :action_type, :notes)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':agent_id'            => $agentId,
            ':target_user_id'      => $targetUserId,
            ':verification_method' => json_encode($verificationMethods, JSON_UNESCAPED_UNICODE),
            ':action_type'         => $actionType,
            ':notes'               => $notes,
        ]);
    }

    /**
     * Fetch audit log for a specific customer (for the agent review panel).
     */
    public function getLogsForUser(int $targetUserId, int $limit = 20): array {
        $sql = "SELECT l.log_id, l.agent_id, u.name AS agent_name, l.target_user_id,
                       l.verification_method, l.action_type, l.notes, l.timestamp
                FROM helpdesk_action_logs l
                JOIN users u ON u.user_id = l.agent_id
                WHERE l.target_user_id = :uid
                ORDER BY l.timestamp DESC
                LIMIT :lim";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $targetUserId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,         PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── Password Reset Token (sent to user, never shown to agent) ─────────

    /**
     * Save a secure reset token (token hashed before storing).
     * Returns the raw token to be emailed to the user.
     */
    public function saveResetToken(int $userId): string {
        $rawToken   = bin2hex(random_bytes(32));   // 64-char hex token
        $hashedToken = hash('sha256', $rawToken);
        $expiry     = date('Y-m-d H:i:s', strtotime('+60 minutes'));

        $sql = "UPDATE users
                SET reset_token = :token, token_expiry = :expiry
                WHERE user_id = :uid AND role = 'customer'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':token'  => $hashedToken,
            ':expiry' => $expiry,
            ':uid'    => $userId,
        ]);

        return $rawToken;  // Only the raw token goes in the email link; never logged or shown.
    }

    /**
     * Verify a reset token from the email link.
     */
    public function verifyResetToken(string $rawToken): ?array {
        $hashedToken = hash('sha256', $rawToken);
        $sql = "SELECT user_id, name, email, token_expiry
                FROM users
                WHERE reset_token = :token AND role = 'customer'
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $hashedToken]);
        $row = $stmt->fetch();

        if (!$row) return null;
        if (strtotime($row['token_expiry']) < time()) return null;
        return $row;
    }

    /**
     * Complete a helpdesk password reset: set new hashed password, clear token,
     * and clear the force_password_change flag.
     */
    public function completePasswordReset(int $userId, string $hashedPassword): bool {
        $sql = "UPDATE users
                SET password = :pw,
                    reset_token = NULL,
                    token_expiry = NULL,
                    force_password_change = 0
                WHERE user_id = :uid AND role = 'customer'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':pw' => $hashedPassword, ':uid' => $userId]);
    }
}
