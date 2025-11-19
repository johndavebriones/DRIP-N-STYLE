<?php
require_once __DIR__ . '/../Controllers/ProductController.php';
$productController = new ProductController();

$action = $_POST['action'] ?? '';

switch ($action) {

    /* ============================================
       ADD PRODUCT
    ============================================ */
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
            'description' => $_POST['description'] ?? '',
            'image' => $imagePath,
            'stock' => $_POST['stock'],
            'status' => $_POST['status']
        ];

        $result = $productController->addProduct($data);
        echo json_encode($result);
        break;


    /* ============================================
       EDIT PRODUCT
    ============================================ */
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
            'description' => $_POST['description'] ?? '',
            'image' => $imagePath,
            'stock' => $_POST['stock'],
            'status' => $_POST['status']
        ];

        $result = $productController->updateProduct($data);
        echo json_encode($result);
        break;


    /* ============================================
       SOFT DELETE PRODUCT
    ============================================ */
    case 'delete':
        $result = $productController->softDelete($_POST['product_id']);
        echo json_encode($result);
        break;


    /* ============================================
       RESTORE DELETED PRODUCT
    ============================================ */
    case 'restoreProduct':
    if (empty($_POST['product_id'])) {
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }

    $productId = intval($_POST['product_id']);

    // DAO instance
    $productDao = new ProductDAO();

    if ($productDao->restoreProduct($productId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Product has been successfully restored.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to restore product.'
        ]);
    }
    exit;


    /* ============================================
       FETCH DELETED PRODUCTS
    ============================================ */
    case 'getDeletedProducts':
        $deleted = $productController->getDeletedProducts();
        echo json_encode(['success' => true, 'deletedProducts' => $deleted]);
        break;


    /* ============================================
    GET PRODUCT BY ID
    ============================================ */
    case 'getProductById':
        $productId = $_POST['product_id'] ?? null;

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID missing']);
            break;
        }

        $product = $productController->getProductById((int)$productId);

        if ($product) {
            // Ensure description is always a string
            $product['description'] = $product['description'] ?? '';

            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        break;

    /* ============================================
     INVALID ACTION
    ============================================ */
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
