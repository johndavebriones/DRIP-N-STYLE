<?php
require_once __DIR__ . '/../Config/database_connect.php';

class AdminDAO {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    private function querySingleValue($sql) {
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }
        $row = $result->fetch_assoc();
        return $row ? array_values($row)[0] : 0;
    }

    public function countProducts() {
        return (int)$this->querySingleValue("SELECT COUNT(*) AS total FROM products WHERE deleted_at IS NULL");
    }

    public function countOrders() {
        return (int)$this->querySingleValue("SELECT COUNT(*) AS total FROM orders");
    }

    public function sumRevenue() {
        $revenue = $this->querySingleValue("SELECT SUM(total_amount) AS revenue FROM orders WHERE order_status = 'Completed'");
        return $revenue ?: 0;
    }

    // Sales Analytics Methods
    
    public function getTotalSales($period = 'daily') {
        $dateCondition = match($period) {
            'daily' => "DATE(order_date) = CURDATE()",
            'weekly' => "YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)",
            'monthly' => "YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE())",
            'yearly' => "YEAR(order_date) = YEAR(CURDATE())",
            default => "DATE(order_date) = CURDATE()"
        };

        $sql = "SELECT COALESCE(SUM(total_amount), 0) AS total_sales 
                FROM orders 
                WHERE order_status = 'Completed' AND $dateCondition";
        
        return $this->querySingleValue($sql);
    }

    public function getRevenueTrends($days = 30) {
        $sql = "SELECT DATE(order_date) AS date, COALESCE(SUM(total_amount), 0) AS revenue
                FROM orders
                WHERE order_status = 'Completed' 
                AND order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(order_date)
                ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $days);
        $stmt->execute();
        $result = $stmt->get_result();

        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }

        $stmt->close();
        return $trends;
    }

    public function getTopSellingProducts($limit = 10) {
        $sql = "SELECT p.name, SUM(oi.quantity) AS total_quantity
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.order_status = 'Completed' AND p.deleted_at IS NULL
                GROUP BY p.product_id, p.name
                ORDER BY total_quantity DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }

    public function getSalesByCategory() {
        $sql = "SELECT c.category_name AS category, COALESCE(SUM(oi.quantity * oi.price), 0) AS total_sales
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id AND p.deleted_at IS NULL
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'Completed'
                GROUP BY c.category_id, c.category_name
                ORDER BY total_sales DESC";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return $categories;
    }

    // Order Analytics Methods
    
    public function getTotalOrders($period = 'daily') {
        $dateCondition = match($period) {
            'daily' => "DATE(order_date) = CURDATE()",
            'weekly' => "YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)",
            'monthly' => "YEAR(order_date) = YEAR(CURDATE()) AND MONTH(order_date) = MONTH(CURDATE())",
            'yearly' => "YEAR(order_date) = YEAR(CURDATE())",
            default => "DATE(order_date) = CURDATE()"
        };

        $sql = "SELECT COUNT(*) AS total_orders FROM orders WHERE $dateCondition";
        return (int)$this->querySingleValue($sql);
    }

    public function getOrderTrends($days = 30) {
        $sql = "SELECT DATE(order_date) AS date, COUNT(*) AS order_count
                FROM orders
                WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(order_date)
                ORDER BY date ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $days);
        $stmt->execute();
        $result = $stmt->get_result();

        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }

        $stmt->close();
        return $trends;
    }

    public function getOrdersByStatus() {
        $sql = "SELECT order_status, COUNT(*) AS order_count
                FROM orders
                GROUP BY order_status
                ORDER BY order_count DESC";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        $statusData = [];
        while ($row = $result->fetch_assoc()) {
            $statusData[] = $row;
        }

        return $statusData;
    }

    public function getOrdersByCategory() {
        $sql = "SELECT c.category_name, COUNT(DISTINCT o.order_id) AS order_count
                FROM categories c
                LEFT JOIN products p ON c.category_id = p.category_id AND p.deleted_at IS NULL
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id
                GROUP BY c.category_id, c.category_name
                ORDER BY order_count DESC";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        $categoryData = [];
        while ($row = $result->fetch_assoc()) {
            $categoryData[] = $row;
        }

        return $categoryData;
    }

    public function getRecentOrders($limit = 5) {
        $stmt = $this->conn->prepare("
            SELECT o.order_id, u.name AS customer_name, p.name AS product_name, o.order_status, o.total_amount
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            ORDER BY o.order_date DESC
            LIMIT ?
        ");
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        return $orders;
    }

    // ==================== INVENTORY ANALYTICS METHODS ====================

    /**
     * Get products running low on stock
     * @param int $threshold Stock level considered as "low"
     * @param int $limit Maximum number of products to return
     * @return array Products with stock below threshold
     */
    public function getLowStockProducts($threshold = 10, $limit = 10) {
        $sql = "SELECT product_id, name, stock, size, color, category_id
                FROM products
                WHERE deleted_at IS NULL 
                AND stock > 0 
                AND stock <= ?
                ORDER BY stock ASC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $threshold, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }

    /**
     * Get out of stock products
     * @param int $limit Maximum number of products to return
     * @return array Products with zero stock or status 'Out of Stock'
     */
    public function getOutOfStockProducts($limit = 10) {
        $sql = "SELECT product_id, name, stock, size, color, category_id, status
                FROM products
                WHERE deleted_at IS NULL 
                AND (stock = 0 OR status = 'Out of Stock')
                ORDER BY date_added DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $stmt->close();
        return $products;
    }

    /**
     * Calculate inventory turnover rate for products
     * Turnover = Total Quantity Sold / Average Stock Level
     * @param int $days Period to calculate turnover (default 30 days)
     * @param int $limit Number of products to return
     * @return array Products with turnover rates
     */
    public function getInventoryTurnoverRate($days = 30, $limit = 10) {
        $sql = "SELECT 
                    p.product_id,
                    p.name,
                    p.stock AS current_stock,
                    COALESCE(SUM(oi.quantity), 0) AS total_sold,
                    CASE 
                        WHEN p.stock > 0 THEN ROUND(COALESCE(SUM(oi.quantity), 0) / p.stock, 2)
                        ELSE 0
                    END AS turnover_rate
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id 
                    AND o.order_status = 'Completed'
                    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                WHERE p.deleted_at IS NULL
                GROUP BY p.product_id, p.name, p.stock
                HAVING total_sold > 0
                ORDER BY turnover_rate DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("ii", $days, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $turnover = [];
        while ($row = $result->fetch_assoc()) {
            $turnover[] = $row;
        }

        $stmt->close();
        return $turnover;
    }

    /**
     * Get most popular sizes based on order volume
     * @return array Size distribution with order counts
     */
    public function getPopularSizes() {
        $sql = "SELECT 
                    p.size,
                    COUNT(DISTINCT oi.order_id) AS order_count,
                    SUM(oi.quantity) AS total_quantity_sold,
                    SUM(p.stock) AS total_current_stock
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'Completed'
                WHERE p.deleted_at IS NULL
                GROUP BY p.size
                ORDER BY total_quantity_sold DESC";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        $sizes = [];
        while ($row = $result->fetch_assoc()) {
            $sizes[] = $row;
        }

        return $sizes;
    }

    /**
     * Get most popular colors based on order volume
     * @param int $limit Maximum number of colors to return
     * @return array Color distribution with order counts
     */
    public function getPopularColors($limit = 10) {
        $sql = "SELECT 
                    p.color,
                    COUNT(DISTINCT oi.order_id) AS order_count,
                    SUM(oi.quantity) AS total_quantity_sold,
                    SUM(p.stock) AS total_current_stock
                FROM products p
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.order_id AND o.order_status = 'Completed'
                WHERE p.deleted_at IS NULL AND p.color IS NOT NULL AND p.color != ''
                GROUP BY p.color
                ORDER BY total_quantity_sold DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $colors = [];
        while ($row = $result->fetch_assoc()) {
            $colors[] = $row;
        }

        $stmt->close();
        return $colors;
    }

    /**
     * Get total inventory value
     * @return float Total value of all products in stock
     */
    public function getTotalInventoryValue() {
        $sql = "SELECT COALESCE(SUM(price * stock), 0) AS total_value
                FROM products
                WHERE deleted_at IS NULL";
        
        return (float)$this->querySingleValue($sql);
    }

    /**
     * Count products by stock status
     * @return array Counts for different stock levels
     */
    public function getStockLevelCounts() {
        $sql = "SELECT 
                    SUM(CASE WHEN stock = 0 OR status = 'Out of Stock' THEN 1 ELSE 0 END) AS out_of_stock,
                    SUM(CASE WHEN stock > 0 AND stock <= 10 THEN 1 ELSE 0 END) AS low_stock,
                    SUM(CASE WHEN stock > 10 THEN 1 ELSE 0 END) AS in_stock
                FROM products
                WHERE deleted_at IS NULL";
        
        $result = $this->conn->query($sql);
        if (!$result) {
            die("SQL Error: " . $this->conn->error);
        }

        return $result->fetch_assoc();
    }
}