<?php
require_once __DIR__ . '/../DAO/ProductDAO.php';

class ProductController {
    private $productDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
    }

    public function getProducts() {
        return $this->productDAO->getAllProducts();
    }

    public function getCategories() {
        return $this->productDAO->getAllCategories();
    }

    public function addProduct($data, $file) {
        $imagePath = $this->handleImageUpload($file);
        $this->productDAO->insertProduct($data, $imagePath);
        header("Location: ../../Public/admin/products.php?success=add");
        exit;
    }

    public function editProduct($data, $file) {
        $imagePath = null;
        if (!empty($file['name'])) {
            $imagePath = $this->handleImageUpload($file);
        }
        $this->productDAO->updateProduct($data, $imagePath);
        header("Location: ../../Public/admin/products.php?success=edit");
        exit;
    }

    public function deleteProduct($id) {
        $this->productDAO->deleteProduct($id);
        header("Location: ../../Public/admin/products.php?success=delete");
        exit;
    }

    private function handleImageUpload($file) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../Public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = time() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            move_uploaded_file($file['tmp_name'], $filePath);
            return 'uploads/' . $fileName;
        }
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ProductController();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            $controller->addProduct($_POST, $_FILES['image']);
            break;

        case 'edit':
            $controller->editProduct($_POST, $_FILES['image']);
            break;

        case 'delete':
            $controller->deleteProduct($_POST['product_id']);
            break;

        default:
            header("Location: ../../Public/admin/products.php");
            exit;
    }
}
?>
