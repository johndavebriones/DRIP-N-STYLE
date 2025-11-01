<?php
require_once __DIR__ . '/../DAO/adminDAO.php';

class AdminController {
    private $adminDAO;

    public function __construct() {
        $this->adminDAO = new AdminDAO();
    }

    public function getDashboardStats() {
        return [
            'totalProducts'  => $this->adminDAO->countProducts(),
            'totalOrders'    => $this->adminDAO->countOrders(),
            'totalCustomers' => $this->adminDAO->countCustomers(),
            'totalRevenue'   => $this->adminDAO->sumRevenue(),
        ];
    }

    public function getRecentOrders($limit = 5) {
        return $this->adminDAO->getRecentOrders($limit);
    }
}
