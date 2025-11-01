<?php
require_once __DIR__ . '/../Config/database_connection.php';

class ProductDAO {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect(); // mysqli connection
    }

    // ✅ Get all products (with optional filters)
    public function getAllProducts($search = '', $category = '', $sort = 'newest') {
        $query = "SELECT p.*, c.name AS category_name 
                  FROM products p
                  JOIN categories c ON p.category_id = c.category_id
                  WHERE 1=1";
        $types = "";    // parameter types for bind_param()
        $params = [];   // parameter values

        if (!empty($search)) {
            $query .= " AND p.name LIKE ?";
            $types .= "s";
            $params[] = "%$search%";
        }

        if (!empty($category)) {
            $query .= " AND c.name = ?";
            $types .= "s";
            $params[] = $category;
        }

        // Sorting
        switch ($sort) {
            case 'price_low':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $query .= " ORDER BY p.price DESC";
                break;
            default:
                $query .= " ORDER BY p.created_at DESC";
        }

        // Prepare and execute
        $stmt = $this->conn->prepare($query);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }

    // ✅ Get product by ID
    public function getProductById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $stmt->close();
        return $product;
    }

    // ✅ Get all categories
    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $result = $this->conn->query($query);

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }
}
