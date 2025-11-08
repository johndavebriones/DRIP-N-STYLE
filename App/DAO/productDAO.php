<?php
require_once __DIR__ . '/../Config/database_connect.php';

class ProductDAO {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // 🔹 Fetch filtered products
    public function getFilteredProducts($search = '', $category = '', $status = '') {
        $query = "
            SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.deleted_at IS NULL
        ";

        $params = [];
        $types = '';

        if ($search) {
            $query .= " AND p.name LIKE ?";
            $params[] = "%$search%";
            $types .= 's';
        }

        if ($category) {
            $query .= " AND LOWER(c.category_name) = ?";
            $params[] = strtolower($category);
            $types .= 's';
        }

        if ($status) {
            $query .= " AND LOWER(p.status) = ?";
            $params[] = strtolower($status);
            $types .= 's';
        }

        $stmt = $this->conn->prepare($query);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔹 Get single product by ID
    public function getProductById($id) {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = ? LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // 🔹 Add product
    public function addProduct($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO products (name, description, price, category_id, size, image, stock, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssdissis",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['size'],
            $data['image'],
            $data['stock'],
            $data['status']
        );
        return $stmt->execute();
    }

    // 🔹 Update product
    public function updateProduct($data) {
        if (!empty($data['image'])) {
            $stmt = $this->conn->prepare("
                UPDATE products 
                SET name=?, description=?, price=?, category_id=?, size=?, image=?, stock=?, status=? 
                WHERE product_id=?
            ");
            $stmt->bind_param(
                "ssdissisi",
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['size'],
                $data['image'],
                $data['stock'],
                $data['status'],
                $data['product_id']
            );
        } else {
            $stmt = $this->conn->prepare("
                UPDATE products 
                SET name=?, description=?, price=?, category_id=?, size=?, stock=?, status=? 
                WHERE product_id=?
            ");
            $stmt->bind_param(
                "ssdisssi",
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category_id'],
                $data['size'],
                $data['stock'],
                $data['status'],
                $data['product_id']
            );
        }
        return $stmt->execute();
    }

    // 🔹 Soft delete
    public function softDelete($id) {
        $stmt = $this->conn->prepare("UPDATE products SET deleted_at = NOW() WHERE product_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // 🔹 Permanent delete
    public function permanentDelete($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // 🔹 Get deleted products
    public function getDeletedProducts() {
        $stmt = $this->conn->prepare("
            SELECT p.*, c.category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.deleted_at IS NOT NULL
            ORDER BY p.deleted_at DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔹 Get categories
    public function getCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔹 Get product statuses
    public function getStatuses() {
        return ['Available', 'Out of Stock'];
    }

    // 🔹 Check duplicate before adding
    public function checkDuplicateProduct($data) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM products 
            WHERE name = ? 
              AND category_id = ? 
              AND size = ? 
              AND deleted_at IS NULL
        ");
        $stmt->bind_param(
            "sis",
            $data['name'],
            $data['category_id'],
            $data['size']
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    // 🔹 Check duplicate before updating (skip current product)
    public function checkDuplicateProductForUpdate($data, $product_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM products 
            WHERE name = ? 
              AND category_id = ? 
              AND size = ? 
              AND product_id != ? 
              AND deleted_at IS NULL
        ");
        $stmt->bind_param(
            "sisi",
            $data['name'],
            $data['category_id'],
            $data['size'],
            $product_id
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    // 🔹 Check if product has active orders
    public function hasActiveOrders($productId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = ? AND o.status IN ('Pending', 'Processing')
        ");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

}
