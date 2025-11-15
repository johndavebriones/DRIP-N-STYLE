<?php
require_once __DIR__ . '/../Models/orderModel.php';

class OrderDAO {
    public $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /*-----------------------------------------------------------
        CREATE ORDER
    ------------------------------------------------------------*/
    public function createOrder($user_id, $total_amount, $pickup_date) {
        $stmt = $this->conn->prepare("
            INSERT INTO orders (user_id, total_amount, order_status, pickup_date)
            VALUES (?, ?, 'Pending', ?)
        ");
        if (!$stmt) die("SQL Error (createOrder): " . $this->conn->error);

        $stmt->bind_param("ids", $user_id, $total_amount, $pickup_date);
        $stmt->execute();

        return $this->conn->insert_id;
    }

    /*-----------------------------------------------------------
        ADD ORDER ITEMS
    ------------------------------------------------------------*/
    public function addOrderItems($order_id, $cartItems) {
        $stmt = $this->conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) die("SQL Error (addOrderItems): " . $this->conn->error);

        foreach ($cartItems as $item) {
            $stmt->bind_param("iiid",
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price_at_time']
            );
            $stmt->execute();
        }
    }

    /*-----------------------------------------------------------
        CREATE PAYMENT
    ------------------------------------------------------------*/
    public function createPayment($order_id, $method, $ref, $amount, $status = 'Pending') {
        $stmt = $this->conn->prepare("
            INSERT INTO payments (order_id, payment_method, payment_ref, amount, payment_status)
            VALUES (?, ?, ?, ?, ?)
        ");

        if (!$stmt) die("SQL Error (createPayment): " . $this->conn->error);

        $stmt->bind_param("issds", $order_id, $method, $ref, $amount, $status);
        $stmt->execute();

        return $this->conn->insert_id;
    }

    /*-----------------------------------------------------------
        LINK PAYMENT TO ORDER
    ------------------------------------------------------------*/
    public function linkPaymentToOrder($order_id, $payment_id) {
        $stmt = $this->conn->prepare("
            UPDATE orders SET payment_id = ? WHERE order_id = ?
        ");
        $stmt->bind_param("ii", $payment_id, $order_id);
        $stmt->execute();
    }

    /*-----------------------------------------------------------
        GET USER ORDERS
    ------------------------------------------------------------*/
    public function getUserOrders($user_id) {
        $stmt = $this->conn->prepare("
            SELECT o.*, p.payment_method, p.payment_status, p.amount
            FROM orders o
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            WHERE o.user_id = ?
            ORDER BY o.order_date DESC
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /*-----------------------------------------------------------
        GET ALL ORDERS
    ------------------------------------------------------------*/
    public function getAllOrders() {
        $sql = "
            SELECT 
                o.*,
                u.name AS customer_name,
                p.payment_method,
                p.payment_status
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            ORDER BY o.order_date DESC
        ";
        return $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    /*-----------------------------------------------------------
        GET ORDER ITEMS
    ------------------------------------------------------------*/
    public function getOrderItems($order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                oi.order_item_id,
                p.product_id,
                p.name AS product_name,
                p.image,
                p.size,
                p.description,
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

    /*-----------------------------------------------------------
        GET ORDER BY ID
    ------------------------------------------------------------*/
    public function getOrderById($order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*, 
                u.name AS customer_name,
                p.payment_method,
                p.payment_status,
                p.payment_id,
                p.amount
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            WHERE o.order_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /*-----------------------------------------------------------
        UPDATE ORDER + PAYMENT STATUS (with rules + stock reduce)
    ------------------------------------------------------------*/
    public function updateOrderAndPaymentStatus($order_id, $newOrderStatus, $newPaymentStatus = null) {
        $order = $this->getOrderById($order_id);
        if (!$order)
            return ['success' => false, 'message' => 'Order not found.'];

        /*---------------------------------
             AUTO-SYNC RULES
        ----------------------------------*/
        if ($newOrderStatus === 'Cancelled') {
            $newPaymentStatus = 'Failed';
        }
        if ($newPaymentStatus === 'Failed') {
            $newOrderStatus = 'Cancelled';
        }

        // RULE: Cannot complete if payment is pending
        if ($newOrderStatus === 'Completed' && $order['payment_status'] === 'Pending') {
            return ['success' => false, 'message' => 'Cannot complete order while payment is pending.'];
        }

        /*---------------------------------
             UPDATE PAYMENT
        ----------------------------------*/
        if (!empty($order['payment_id']) && $newPaymentStatus) {
            $stmtPay = $this->conn->prepare("
                UPDATE payments SET payment_status = ? WHERE payment_id = ?
            ");
            $stmtPay->bind_param("si", $newPaymentStatus, $order['payment_id']);
            $stmtPay->execute();
        }

        /*---------------------------------
            UPDATE ORDER
        ----------------------------------*/
        $stmtOrd = $this->conn->prepare("
            UPDATE orders SET order_status = ? WHERE order_id = ?
        ");
        $stmtOrd->bind_param("si", $newOrderStatus, $order_id);
        $stmtOrd->execute();

        /*---------------------------------
            REDUCE STOCK ONLY IF:
            - Order is Completed
            - Payment is Paid
        ----------------------------------*/
        if (
            $newOrderStatus === 'Completed' && 
            (($newPaymentStatus && $newPaymentStatus === 'Paid') || $order['payment_status'] === 'Paid')
        ) {

            $items = $this->getOrderItems($order_id);

            foreach ($items as $item) {
                $stmtStock = $this->conn->prepare("
                    UPDATE products 
                    SET stock = GREATEST(stock - ?, 0)
                    WHERE product_id = ?
                ");
                $stmtStock->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmtStock->execute();
            }
        }
        return ['success' => true, 'message' => 'Statuses updated successfully'];
    }
}
