<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database_connect.php';

class ShopController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getProducts($search = '', $category = '', $sort = 'newest') {
        $query = "SELECT p.*, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id
                  WHERE 1";

        $params = [];

        if (!empty($search)) {
            $query .= " AND p.name LIKE ?";
            $params[] = "%$search%";
        }
        if (!empty($category)) {
            $query .= " AND c.category_id = ?";
            $params[] = $category;
        }

        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC";
                break;
            default:
                $query .= " ORDER BY p.date_added DESC";
        }

        $stmt = $this->conn->prepare($query);

        if ($params) {
            // Dynamically bind parameters
            $types = str_repeat('s', count($params)); // assuming all params are strings; adjust types if needed
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
