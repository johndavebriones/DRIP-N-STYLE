<?php
require_once __DIR__ . '/../Controllers/ProductController.php';
$productController = new ProductController();

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../../Public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        $data = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'size' => $_POST['size'],
            'image' => $imagePath,
            'stock' => $_POST['stock'],
            'status' => $_POST['status']
        ];

        $result = $productController->addProduct($data);
        echo json_encode($result);
        break;

    case 'edit':
        $existingImage = $_POST['existing_image'] ?? '';
        $imagePath = $existingImage;

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = '../../Public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'uploads/' . $fileName;
            }
        }

        $data = [
            'product_id' => $_POST['product_id'],
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'size' => $_POST['size'],
            'image' => $imagePath,
            'stock' => $_POST['stock'],
            'status' => $_POST['status']
        ];

        $result = $productController->updateProduct($data);
        echo json_encode($result);
        break;

    case 'delete':
        $result = $productController->softDelete($_POST['product_id']);
        echo json_encode($result);
        break;

    case 'permanentDelete':
        $success = $productController->permanentDelete($_POST['product_id']);
        echo json_encode(['success' => $success, 'message' => $success ? 'Product permanently deleted!' : 'Failed to delete product']);
        break;

    case 'getDeletedProducts':
        $deleted = $productController->getDeletedProducts();
        echo json_encode(['success' => true, 'deletedProducts' => $deleted]);
        break;

    case 'getProductById':
        $product = $productController->getProductById($_POST['product_id']);
        echo json_encode(['success' => true, 'product' => $product]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
