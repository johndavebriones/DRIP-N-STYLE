<?php
require_once __DIR__ . '/../Controllers/ProductController.php';
$productController = new ProductController();

$action = $_POST['action'] ?? '';

// Enable error logging for debugging
error_log("=== Product Helper Action: $action ===");
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));

switch ($action) {

    /* ============================================
       ADD PRODUCT
    ============================================ */
    case 'add':
        error_log("ADD PRODUCT: Processing...");
        $imagePath = '';

        // Handle new image upload for ADD mode
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("ADD: File upload detected - " . $_FILES['image']['name']);
            $uploadDir = '../../Public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                error_log("ADD: Created upload directory");
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $imagePath = 'uploads/' . $fileName;
                error_log("ADD: File uploaded successfully - " . $imagePath);
            } else {
                error_log("ADD ERROR: Failed to upload file");
            }
        } else {
            // Check if image path was sent as POST parameter (for variants)
            if (!empty($_POST['image'])) {
                $imagePath = $_POST['image'];
                error_log("ADD: Using image path from POST - " . $imagePath);
            } else {
                error_log("ADD ERROR: No image provided");
            }
        }

        $stock = intval($_POST['stock']);
        $status = $stock > 0 ? 'Available' : 'Out of Stock';

        $data = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'size' => $_POST['size'],
            'color' => $_POST['color'] ?? '',
            'description' => $_POST['description'] ?? '',
            'image' => $imagePath,
            'stock' => $stock,
            'status' => $status
        ];

        error_log("ADD: Final data - " . json_encode($data));

        $result = $productController->addProduct($data);
        error_log("ADD: Result - " . json_encode($result));
        echo json_encode($result);
        break;


    /* ============================================
       EDIT PRODUCT
    ============================================ */
    case 'edit':
        error_log("EDIT PRODUCT: Processing...");
        
        // For EDIT mode, ONLY use the image path from POST parameter
        // DO NOT process file uploads in edit mode
        $imagePath = $_POST['image'] ?? '';
        
        if (empty($imagePath)) {
            error_log("EDIT ERROR: No image path received in POST data!");
            echo json_encode([
                'success' => false, 
                'message' => 'Image path missing. Please try again.'
            ]);
            exit;
        }
        
        error_log("EDIT: Image path received - " . $imagePath);

        $stock = intval($_POST['stock']);
        $status = $stock > 0 ? 'Available' : 'Out of Stock';

        $data = [
            'product_id' => $_POST['product_id'],
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'size' => $_POST['size'],
            'color' => $_POST['color'] ?? '',
            'description' => $_POST['description'] ?? '',
            'image' => $imagePath,  // Keep existing image from the group
            'stock' => $stock,
            'status' => $status
        ];

        error_log("EDIT: Final data - " . json_encode($data));

        $result = $productController->updateProduct($data);
        error_log("EDIT: Result - " . json_encode($result));
        echo json_encode($result);
        break;

    /* ============================================
       SOFT DELETE PRODUCT
    ============================================ */
    case 'delete':
        error_log("DELETE PRODUCT: ID - " . $_POST['product_id']);
        $result = $productController->softDelete($_POST['product_id']);
        error_log("DELETE: Result - " . json_encode($result));
        echo json_encode($result);
        break;


    /* ============================================
       RESTORE DELETED PRODUCT
    ============================================ */
    case 'restoreProduct':
        if (empty($_POST['product_id'])) {
            error_log("RESTORE ERROR: Product ID missing");
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }

        $productId = intval($_POST['product_id']);
        error_log("RESTORE PRODUCT: ID - " . $productId);
        $productDao = new ProductDAO();

        if ($productDao->restoreProduct($productId)) {
            error_log("RESTORE: Success");
            echo json_encode([
                'success' => true,
                'message' => 'Product has been successfully restored.'
            ]);
        } else {
            error_log("RESTORE ERROR: Failed");
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
        error_log("GET DELETED PRODUCTS: Fetching...");
        $deleted = $productController->getDeletedProducts();
        error_log("GET DELETED: Count - " . count($deleted));
        echo json_encode(['success' => true, 'deletedProducts' => $deleted]);
        break;


    /* ============================================
       GET PRODUCT BY ID
    ============================================ */
    case 'getProductById':
        $productId = $_POST['product_id'] ?? null;

        if (!$productId) {
            error_log("GET BY ID ERROR: Product ID missing");
            echo json_encode(['success' => false, 'message' => 'Product ID missing']);
            break;
        }

        error_log("GET BY ID: Product ID - " . $productId);
        $product = $productController->getProductById((int)$productId);

        if ($product) {
            $product['description'] = $product['description'] ?? '';
            $product['color'] = $product['color'] ?? '';
            error_log("GET BY ID: Success - Image: " . $product['image']);
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            error_log("GET BY ID ERROR: Product not found");
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
        break;

    /* ============================================
       INVALID ACTION
    ============================================ */
    default:
        error_log("INVALID ACTION: " . $action);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

error_log("=== Product Helper Complete ===");
?>