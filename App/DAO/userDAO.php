<?php
require_once __DIR__ . '/../config/database_connect.php';
require_once __DIR__ . '/../Models/userModel.php';

class UserDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function findByEmailOrName($emailOrName) {
        $query = "SELECT * FROM users WHERE (email = ? OR name = ?) AND status = 'active' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) return null;

        $stmt->bind_param("ss", $emailOrName, $emailOrName);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) return null;

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function registerUser(UserModel $user) {
        $query = "
            INSERT INTO users (name, email, password, role, status, contact_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) return false;

        $stmt->bind_param(
            "ssssss",
            $user->name,
            $user->email,
            $user->password,
            $user->role,
            $user->status,
            $user->contact_number
        );

        return $stmt->execute();
    }

    public function findById($user_id) {
        $query = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) return null;

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
