<?php
// Save this as: admin/ajax_featured.php

require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();

require_once __DIR__ . '/../../App/Controllers/ProductController.php';

header('Content-Type: application/json');

$productController = new ProductController();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_products':
            // Get all products for featured selection
            $products = $productController->getAllProductsForFeatured();
            $featuredCount = $productController->getFeaturedCount();
            
            echo json_encode([
                'success' => true,
                'products' => $products,
                'featured_count' => $featuredCount
            ]);
            break;

        case 'toggle_featured':
            // Toggle featured status
            $productId = intval($_POST['product_id'] ?? 0);
            
            if (!$productId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid product ID'
                ]);
                exit;
            }

            $result = $productController->toggleFeatured($productId);
            echo json_encode($result);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>