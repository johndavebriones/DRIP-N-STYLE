<?php
// app/controllers/ShopController.php
session_start();
require_once "../app/config/database_connect.php";

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

        if (!empty($search)) {
            $query .= " AND p.name LIKE :search";
        }
        if (!empty($category)) {
            $query .= " AND c.category_id = :category";
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

        if (!empty($search)) $stmt->bindValue(':search', "%$search%");
        if (!empty($category)) $stmt->bindValue(':category', $category);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
