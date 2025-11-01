<?php
require_once __DIR__ . '/../Config/database_connect.php';

class ProductDAO {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllProducts() {
        $sql = "SELECT p.*, c.category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY p.product_id DESC";
        $result = $this->conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        return $products;
    }

    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY category_name ASC";
        $result = $this->conn->query($sql);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        return $categories;
    }

    public function insertProduct($data, $imagePath) {
        $stmt = $this->conn->prepare(
            "INSERT INTO products (name, description, category_id, price, stock, status, image) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "sssdiss",
            $data['name'],
            $data['description'],
            $data['category_id'],
            $data['price'],
            $data['stock'],
            $data['status'],
            $imagePath
        );
        $stmt->execute();
        $stmt->close();
    }

    public function updateProduct($data, $imagePath = null) {
        if ($imagePath) {
            $sql = "UPDATE products 
                    SET name=?, description=?, category_id=?, price=?, stock=?, status=?, image=? 
                    WHERE product_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssdissi",
                $data['name'],
                $data['description'],
                $data['category_id'],
                $data['price'],
                $data['stock'],
                $data['status'],
                $imagePath,
                $data['product_id']
            );
        } else {
            $sql = "UPDATE products 
                    SET name=?, description=?, category_id=?, price=?, stock=?, status=? 
                    WHERE product_id=?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param(
                "sssdssi",
                $data['name'],
                $data['description'],
                $data['category_id'],
                $data['price'],
                $data['stock'],
                $data['status'],
                $data['product_id']
            );
        }
        $stmt->execute();
        $stmt->close();
    }

    public function deleteProduct($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}
?>
