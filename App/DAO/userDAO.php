<?php
require_once __DIR__ . '/../config/database_connect.php';
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

    public function findByEmailOrName($emailOrName) {
        return $this->fetchSingle(
            "SELECT * FROM users WHERE (email = ? OR name = ?) AND status = 'active' LIMIT 1",
            "ss",
            $emailOrName,
            $emailOrName
        );
    }

    public function registerUser(UserModel $user) {
        $query = "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->prepareAndExecute(
            $query,
            "sssss",
            $user->name,
            $user->email,
            $user->password,
            $user->role,
            $user->status
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
}
