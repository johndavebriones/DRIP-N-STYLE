<?php
require_once __DIR__ . '/../Config/database_connect.php';

class ShopDAO {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // ✅ Fetch all categories
    public function fetchCategories() {
        $sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ Fetch products with optional search, category, and sorting
    public function fetchProducts($search = '', $category = '', $sort = 'newest') {
        $sql = "SELECT p.product_id, p.name, p.description, p.price, p.image, p.stock, 
                       p.status, p.date_added, c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                WHERE 1=1";

        $params = [];
        $types = '';

        // 🔍 Search
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }

        // 🏷️ Category filter
        if (!empty($category)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category;
            $types .= 'i';
        }

        // 🔽 Sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'oldest':
                $sql .= " ORDER BY p.date_added ASC";
                break;
            default:
                $sql .= " ORDER BY p.date_added DESC";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ Fetch cart items for a user
    public function fetchCartItems($userId) {
        $sql = "SELECT ci.item_id, ci.quantity, ci.price_at_time AS price, p.name, p.image
                FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.cart_id
                JOIN products p ON ci.product_id = p.product_id
                WHERE c.user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
