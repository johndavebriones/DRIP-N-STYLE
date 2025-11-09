<?php
require_once __DIR__ . '/../DAO/ShopDAO.php';

class ShopController {
    private $dao;

    public function __construct() {
        $this->dao = new ShopDAO();
    }

    // ✅ Get all categories
    public function getCategories() {
        return $this->dao->fetchCategories();
    }

    // ✅ Get all products (with search, filter, and sort)
    public function getProducts($search = '', $category = '', $sort = 'newest') {
        return $this->dao->fetchProducts($search, $category, $sort);
    }
}
