<?php
require_once __DIR__ . '/../Config/database_connect.php';
require_once __DIR__ . '/../Models/userModel.php';

class UserDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    private function prepareAndExecute($query, $types = '', ...$params) {
        $stmt = $this->conn->prepare($query);
        if (!$stmt) return false;

        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) return false;

        return $stmt;
    }

    private function fetchSingle($query, $types = '', ...$params) {
        $stmt = $this->prepareAndExecute($query, $types, ...$params);
        if (!$stmt) return null;

        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($user_id) {
        return $this->fetchSingle("SELECT * FROM users WHERE user_id = ? LIMIT 1", "i", $user_id);
    }

    public function findByEmail($email) {
        return $this->fetchSingle("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1", "s", $email);
    }

    public function findByEmailForLogin($email) {
        return $this->fetchSingle("SELECT * FROM users WHERE email = ? LIMIT 1", "s", $email);
    }

    public function findByEmailOrName($emailOrName) {
        return $this->fetchSingle(
            "SELECT * FROM users WHERE (email = ? OR name = ?) AND status = 'active' LIMIT 1",
            "ss",
            $emailOrName,
            $emailOrName
        );
    }

    public function registerUser(UserModel $user) {
        $query = "INSERT INTO users (name, email, password, role, status, birthdate, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->prepareAndExecute(
            $query,
            "sssssss",
            $user->name,
            $user->email,
            $user->password,
            $user->role,
            $user->status,
            $user->birthdate,
            $user->contact_number
        );
        return $stmt !== false;
    }

    public function updateUserFields($user_id, array $fields) {
        $setParts = [];
        $types = '';
        $values = [];

        foreach ($fields as $column => $value) {
            $setParts[] = "$column = ?";
            $types .= is_int($value) ? 'i' : 's';
            $values[] = $value;
        }

        $query = "UPDATE users SET " . implode(', ', $setParts) . " WHERE user_id = ?";
        $types .= 'i';
        $values[] = $user_id;

        $stmt = $this->prepareAndExecute($query, $types, ...$values);
        return $stmt !== false;
    }

    public function updatePassword($user_id, $hashedPassword) {
        return $this->updateUserFields($user_id, ['password' => $hashedPassword]);
    }

    public function incrementFailedAttempts($user_id) {
        $user = $this->findById($user_id);
        if (!$user) return false;
        $attempts = $user['failed_attempts'] + 1;
        return $this->updateUserFields($user_id, ['failed_attempts' => $attempts]);
    }

    public function resetFailedAttempts($user_id) {
        return $this->updateUserFields($user_id, ['failed_attempts' => 0, 'locked_until' => null]);
    }

    public function lockAccount($user_id, $lockDurationMinutes = 12) {
        $lockedUntil = date('Y-m-d H:i:s', strtotime("+{$lockDurationMinutes} minutes"));
        return $this->updateUserFields($user_id, ['locked_until' => $lockedUntil]);
    }

    public function isAccountLocked($user_id) {
        $user = $this->findById($user_id);
        if (!$user) return false;
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return true;
        }
        return false;
    }
}
