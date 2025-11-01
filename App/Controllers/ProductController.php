<?php
require_once __DIR__ . '/../DAO/productDAO.php';

class ProductController {
    private $productDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
    }

    public function getProducts($search = '', $category = '', $sort = 'newest') {
        return $this->productDAO->getAllProducts($search, $category, $sort);
    }

    public function getProductById($id) {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->productDAO->getProductById($id);
    }

    public function getCategories() {
        return $this->productDAO->getCategories();
    }
}
