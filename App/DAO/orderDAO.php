<?php
require_once __DIR__ . '/../Models/orderModel.php';

class OrderDAO {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✅ Create a new order record
    public function createOrder($user_id, $total_amount) {
        $stmt = $this->conn->prepare("
            INSERT INTO orders (user_id, total_amount, order_status)
            VALUES (?, ?, 'Pending')
        ");
        if (!$stmt) {
            die("SQL Error (createOrder): " . $this->conn->error);
        }

        $stmt->bind_param("id", $user_id, $total_amount);
        $stmt->execute();

        return $this->conn->insert_id;
    }

    // ✅ Insert order items from cart
    public function addOrderItems($order_id, $cartItems) {
        $stmt = $this->conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmt) {
            die("SQL Error (addOrderItems): " . $this->conn->error);
        }

        foreach ($cartItems as $item) {
            $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price_at_time']);
            $stmt->execute();
        }
    }

    // ✅ Create a payment record (Cash or GCash)
    public function createPayment($order_id, $method, $ref, $amount, $status = 'Pending') {
        $stmt = $this->conn->prepare("
            INSERT INTO payments (order_id, payment_method, payment_ref, amount, payment_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            die("SQL Error (createPayment): " . $this->conn->error);
        }

        $stmt->bind_param("issds", $order_id, $method, $ref, $amount, $status);
        $stmt->execute();

        return $this->conn->insert_id;
    }

    // ✅ Link payment_id to order
    public function linkPaymentToOrder($order_id, $payment_id) {
        $stmt = $this->conn->prepare("
            UPDATE orders SET payment_id = ? WHERE order_id = ?
        ");
        if (!$stmt) {
            die("SQL Error (linkPaymentToOrder): " . $this->conn->error);
        }

        $stmt->bind_param("ii", $payment_id, $order_id);
        $stmt->execute();
    }

    // ✅ Update order status (e.g. after payment confirmation)
    public function updateOrderStatus($order_id, $status) {
        $stmt = $this->conn->prepare("
            UPDATE orders SET order_status = ? WHERE order_id = ?
        ");
        if (!$stmt) {
            die("SQL Error (updateOrderStatus): " . $this->conn->error);
        }

        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
    }

    // ✅ Fetch all orders of a user
    public function getUserOrders($user_id) {
        $stmt = $this->conn->prepare("
            SELECT o.*, p.payment_method, p.payment_status, p.amount
            FROM orders o
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC
        ");
        if (!$stmt) {
            die("SQL Error (getUserOrders): " . $this->conn->error);
        }

        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ Fetch all orders for admin
    public function getAllOrders() {
        $sql = "
            SELECT 
                o.order_id,
                o.user_id,
                u.name AS customer_name,
                o.total_amount AS total,
                o.order_status AS status,
                o.pickup_date,
                o.order_date,
                p.payment_method,
                p.payment_status,
                p.payment_ref,
                p.amount AS paid_amount
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            ORDER BY o.order_date DESC
        ";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ✅ Fetch items inside one specific order
    public function getOrderItems($order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                oi.order_item_id,
                p.name AS product_name,
                p.image,
                oi.price,
                oi.quantity
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}
