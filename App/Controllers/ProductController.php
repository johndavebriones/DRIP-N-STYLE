<?php
require_once __DIR__ . '/../DAO/productDAO.php';

class ProductController {
    private $productDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
    }

    public function getFilteredProducts($search, $category, $status) {
        return $this->productDAO->getFilteredProducts($search, $category, $status);
    }

    public function getCategories() {
        return $this->productDAO->getCategories();
    }

    public function getStatuses() {
        return $this->productDAO->getStatuses();
    }

    public function addProduct($data) {
        if ($this->productDAO->checkDuplicateProduct($data)) {
            return [
                'success' => false,
                'message' => 'A product with the same name, category, and size already exists!'
            ];
        }

        $result = $this->productDAO->addProduct($data);
        return [
            'success' => $result,
            'message' => $result ? 'Product added successfully' : 'Failed to add product'
        ];
    }

    public function updateProduct($data) {
        $product_id = $data['product_id'] ?? null;
        if (!$product_id) {
            return ['success' => false, 'message' => 'Product ID is missing'];
        }

        if ($this->productDAO->checkDuplicateProductForUpdate($data, $product_id)) {
            return [
                'success' => false,
                'message' => 'Another product with the same name, category, and size already exists!'
            ];
        }

        $result = $this->productDAO->updateProduct($data);
        return [
            'success' => $result,
            'message' => $result ? 'Product updated successfully' : 'Failed to update product'
        ];
    }

    public function getProductById($id) {
        return $this->productDAO->getProductById($id);
    }

    public function softDelete($productId) {
        if ($this->productDAO->hasActiveOrders($productId)) {
            return ['success' => false, 'message' => 'Cannot delete this product because it has active orders.'];
        }

        $success = $this->productDAO->softDelete($productId);
        return ['success' => $success, 'message' => $success ? 'Product deleted successfully!' : 'Failed to delete product'];
    }

    public function permanentDelete($id) {
        return $this->productDAO->permanentDelete($id);
    }

    public function getDeletedProducts() {
        return $this->productDAO->getDeletedProducts();
    }
}
