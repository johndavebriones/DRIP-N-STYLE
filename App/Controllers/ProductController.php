<?php
require_once __DIR__ . '/../config/database_connect.php';

class ProductController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getAllProducts() {
        $query = "SELECT * FROM products ORDER BY product_id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProductById($id) {
        $query = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function addProduct($name, $price, $size, $imagePath) {
        $query = "INSERT INTO products (product_name, price, size, image_path) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sdss", $name, $price, $size, $imagePath);
        return $stmt->execute();
    }

    public function updateProduct($id, $name, $price, $size, $imagePath = null) {
        if ($imagePath) {
            $query = "UPDATE products SET product_name = ?, price = ?, size = ?, image_path = ? WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sdssi", $name, $price, $size, $imagePath, $id);
        } else {
            $query = "UPDATE products SET product_name = ?, price = ?, size = ? WHERE product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sdsi", $name, $price, $size, $id);
        }
        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $query = "DELETE FROM products WHERE product_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}

if (isset($_GET['action'])) {
    $controller = new ProductController();

    switch ($_GET['action']) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = $_POST['product_name'];
                $price = $_POST['price'];
                $size = $_POST['size'];

                $imagePath = null;
                if (!empty($_FILES['image']['name'])) {
                    $targetDir = __DIR__ . '/../../Public/uploads/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                    $imagePath = 'uploads/' . $fileName;
                }

                $controller->addProduct($name, $price, $size, $imagePath);
                header("Location: ../../Public/admin/products.php");
                exit;
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['product_id'];
                $name = $_POST['product_name'];
                $price = $_POST['price'];
                $size = $_POST['size'];

                $imagePath = null;
                if (!empty($_FILES['image']['name'])) {
                    $targetDir = __DIR__ . '/../../Public/uploads/';
                    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $fileName;
                    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                    $imagePath = 'uploads/' . $fileName;
                }

                $controller->updateProduct($id, $name, $price, $size, $imagePath);
                header("Location: ../../Public/admin/products.php");
                exit;
            }
            break;

        case 'delete':
            if (isset($_GET['id'])) {
                $controller->deleteProduct($_GET['id']);
                header("Location: ../../Public/admin/products.php");
                exit;
            }
            break;
    }
}
?>
