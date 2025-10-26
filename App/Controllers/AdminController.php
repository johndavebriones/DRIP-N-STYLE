<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database_connect.php';

class AdminController {
    private $conn;

    public function __construct() {
        $db = new Database(); // your MySQLi Database class
        $this->conn = $db->connect();

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    private function querySingleValue($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }
        $row = $result->fetch_assoc();
        return $row ? array_values($row)[0] : 0;
    }

    public function totalProducts() {
        return (int)$this->querySingleValue("SELECT COUNT(*) AS total FROM products");
    }

    public function totalOrders() {
        return (int)$this->querySingleValue("SELECT COUNT(*) AS total FROM orders");
    }

    public function totalCustomers() {
        return (int)$this->querySingleValue("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
    }

    public function totalRevenue() {
        $revenue = $this->querySingleValue("SELECT SUM(total_amount) AS revenue FROM orders WHERE order_status = 'Completed'");
        return $revenue ?: 0;
    }

    public function recentOrders($limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT o.order_id, u.name AS customer_name, p.name AS product_name, o.order_status, o.total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            ORDER BY o.order_date DESC
            LIMIT ?
        ");

        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        return $orders;
    }
}
