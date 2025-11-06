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
        return $this->productDAO->addProduct($data);
    }

    public function updateProduct($data) {
        return $this->productDAO->updateProduct($data);
    }

    public function getProductById($id) {
        return $this->productDAO->getProductById($id);
    }

    public function softDelete($id) {
        return $this->productDAO->softDelete($id);
    }

    public function permanentDelete($id) {
        return $this->productDAO->permanentDelete($id);
    }

    public function getDeletedProducts() {
        return $this->productDAO->getDeletedProducts();
    }
}