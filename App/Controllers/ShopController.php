<?php
require_once __DIR__ . '/../DAO/shopDAO.php';

class ShopController {
    private $dao;

    public function __construct() {
        $this->dao = new ShopDAO();
    }

    public function getCategories() {
        return $this->dao->fetchCategories();
    }

    public function getProducts($search = '', $category = '', $sort = 'newest') {
        return $this->dao->fetchProducts($search, $category, $sort);
    }

    public function getCartItems($userId) {
        return $this->dao->fetchCartItems($userId);
    }
}
