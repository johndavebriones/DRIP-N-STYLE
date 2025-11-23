<?php
require_once __DIR__ . '/../Config/database_connect.php';

class ProductDAO {
    private $conn;

    public function __construct($conn = null) {
        if ($conn) {
            $this->conn = $conn;
        } else {
            $db = new Database();
            $this->conn = $db->connect();
        }
    }

    // Fetch filtered products
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

        $query .= " ORDER BY p.name ASC, p.date_added DESC";

        $stmt = $this->conn->prepare($query);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get single product by ID
    public function getProductById($id) {
        $stmt = $this->conn->prepare("
            SELECT 
                p.product_id, p.name, p.price, p.size, p.color, p.stock, 
                p.status, p.category_id, p.image, p.description, 
                c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = ? 
            LIMIT 1
        ");
        
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $row = $result->fetch_assoc()) {
            $row['description'] = $row['description'] ?? '';
            $row['color'] = $row['color'] ?? '';
            return $row;
        }

        return null;
    }

    // Add product
    public function addProduct($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO products (name, description, price, category_id, size, color, image, stock, status, date_added)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param(
            "ssdisssis",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['size'],
            $data['color'],
            $data['image'],
            $data['stock'],
            $data['status']
        );
        return $stmt->execute();
    }

    // Update product - SIMPLIFIED (NO IMAGE UPDATE)
    public function updateProduct($data) {
        $status = $data['stock'] <= 0 ? 'Out of Stock' : 'Available';

        // Update WITHOUT touching the image field
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET name=?, description=?, price=?, category_id=?, size=?, color=?, stock=?, status=? 
            WHERE product_id=?
        ");
        $stmt->bind_param(
            "ssdissisi",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['size'],
            $data['color'],
            $data['stock'],
            $status,
            $data['product_id']
        );

        return $stmt->execute();
    }

    // Soft delete
    public function softDelete($id) {
        $stmt = $this->conn->prepare("UPDATE products SET deleted_at = NOW() WHERE product_id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Get deleted products
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

    // Get categories
    public function getCategories() {
        $stmt = $this->conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Get product statuses
    public function getStatuses() {
        return ['Available', 'Out of Stock'];
    }

    // Check duplicate before adding
    public function checkDuplicateProduct($data) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM products 
            WHERE name = ? 
              AND category_id = ? 
              AND price = ?
              AND size = ? 
              AND color = ?
              AND deleted_at IS NULL
        ");
        $stmt->bind_param(
            "sidss",
            $data['name'],
            $data['category_id'],
            $data['price'],
            $data['size'],
            $data['color']
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    // Check duplicate before updating (skip current product)
    public function checkDuplicateProductForUpdate($data, $product_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM products 
            WHERE name = ? 
              AND category_id = ? 
              AND price = ?
              AND size = ? 
              AND color = ?
              AND product_id != ? 
              AND deleted_at IS NULL
        ");
        $stmt->bind_param(
            "sidssi",
            $data['name'],
            $data['category_id'],
            $data['price'],
            $data['size'],
            $data['color'],
            $product_id
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    // Check if product has active orders
    public function hasActiveOrders($productId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS count 
            FROM order_items oi
            INNER JOIN orders o ON oi.order_id = o.order_id
            WHERE oi.product_id = ? AND o.order_status IN ('Pending', 'Processing')
        ");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    // Reduce stock and auto-update status if needed
    public function reduceStock($productId, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET stock = GREATEST(stock - ?, 0)
            WHERE product_id = ?
        ");
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => $stmt->error];
        }

        $stmtCheck = $this->conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmtCheck->bind_param("i", $productId);
        $stmtCheck->execute();
        $stockRow = $stmtCheck->get_result()->fetch_assoc();
        $stock = $stockRow['stock'] ?? 0;

        if ($stock <= 0) {
            $stmtStatus = $this->conn->prepare("UPDATE products SET status = 'Out of Stock' WHERE product_id = ?");
            $stmtStatus->bind_param("i", $productId);
            $stmtStatus->execute();
        }

        return ['success' => true];
    }

    // Increase stock and update status if needed
    public function increaseStock($productId, $quantity) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET stock = stock + ?
            WHERE product_id = ?
        ");
        $stmt->bind_param("ii", $quantity, $productId);
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => $stmt->error];
        }

        $stmtCheck = $this->conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmtCheck->bind_param("i", $productId);
        $stmtCheck->execute();
        $stockRow = $stmtCheck->get_result()->fetch_assoc();
        $stock = $stockRow['stock'] ?? 0;

        if ($stock > 0) {
            $stmtStatus = $this->conn->prepare("UPDATE products SET status = 'Available' WHERE product_id = ?");
            $stmtStatus->bind_param("i", $productId);
            $stmtStatus->execute();
        }

        return ['success' => true];
    }

    // Restore soft-deleted product
    public function restoreProduct($id) {
        $stmt = $this->conn->prepare("
            UPDATE products 
            SET deleted_at = NULL 
            WHERE product_id = ?
        ");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}