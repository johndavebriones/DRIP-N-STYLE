<?php
require_once __DIR__ . '/../Models/orderModel.php';
require_once __DIR__ . '/ProductDAO.php';

class OrderDAO {
    public $conn;
    private $productDAO;

    public function __construct($conn) {
    $this->conn = $conn;
    $this->productDAO = new ProductDAO($conn); // pass the same connection
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
        UPDATE ORDER + PAYMENT STATUS
    ------------------------------------------------------------*/
    public function updateOrderAndPaymentStatus($order_id, $newOrderStatus = null, $newPaymentStatus = null) {
        $order = $this->getOrderById($order_id);
        if (!$order) return ['success' => false, 'message' => 'Order not found.'];

        $currentPaymentStatus = $order['payment_status'] ?? null;
        $currentOrderStatus = $order['order_status'] ?? null;

        // Normalize empty strings -> null for easier checks
        $newOrderStatus = (isset($newOrderStatus) && $newOrderStatus !== '') ? $newOrderStatus : null;
        $newPaymentStatus = (isset($newPaymentStatus) && $newPaymentStatus !== '') ? $newPaymentStatus : null;

        // Apply sync rules
        if ($newOrderStatus === 'Cancelled') $newPaymentStatus = 'Failed';
        if ($newPaymentStatus === 'Failed') $newOrderStatus = 'Cancelled';

        // Determine what the final statuses will be (after update)
        $finalOrderStatus = $newOrderStatus !== null ? $newOrderStatus : $currentOrderStatus;
        $finalPaymentStatus = $newPaymentStatus !== null ? $newPaymentStatus : $currentPaymentStatus;

        // Prevent Completed if payment is Pending
        if ($finalOrderStatus === 'Completed' && ($finalPaymentStatus === 'Pending' || $finalPaymentStatus === null)) {
            return ['success' => false, 'message' => 'Cannot complete order while payment is pending.'];
        }

        // Begin updates (payment then order)
        // Update payment if needed
        if (!empty($order['payment_id']) && $newPaymentStatus !== null) {
            $stmt = $this->conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
            $stmt->bind_param("si", $newPaymentStatus, $order['payment_id']);
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Failed to update payment status: ' . $stmt->error];
            }
        }

        // Update order if needed
        if ($newOrderStatus !== null) {
            $stmt2 = $this->conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $stmt2->bind_param("si", $newOrderStatus, $order_id);
            if (!$stmt2->execute()) {
                return ['success' => false, 'message' => 'Failed to update order status: ' . $stmt2->error];
            }
        }

        // Recompute final statuses in case DB/other logic changed them
        $finalOrderStatus = $newOrderStatus !== null ? $newOrderStatus : $currentOrderStatus;
        $finalPaymentStatus = $newPaymentStatus !== null ? $newPaymentStatus : $currentPaymentStatus;

        // If order just became Completed and payment is Paid -> reduce stock
        if ($finalOrderStatus === 'Completed' && $finalPaymentStatus === 'Paid') {
            $items = $this->getOrderItems($order_id);
            foreach ($items as $item) {
                $res = $this->productDAO->reduceStock($item['product_id'], (int)$item['quantity']);
                if (!$res['success']) {
                    // Log or handle partial failures — but continue to attempt other items
                    // For now, return failure
                    return ['success' => false, 'message' => 'Failed reducing stock for product ' . $item['product_id'] . ': ' . ($res['error'] ?? '')];
                }
            }
        }

        // If order has become Cancelled (and wasn’t Cancelled before) -> restore stock
        if ($finalOrderStatus === 'Cancelled' && $currentOrderStatus !== 'Cancelled') {
            $items = $this->getOrderItems($order_id);
            foreach ($items as $item) {
                $res = $this->productDAO->increaseStock($item['product_id'], (int)$item['quantity']);
                if (!$res['success']) {
                    return ['success' => false, 'message' => 'Failed restoring stock for product ' . $item['product_id'] . ': ' . ($res['error'] ?? '')];
                }
            }
        }

        return ['success' => true, 'message' => 'Order and payment statuses updated successfully.'];
    }
}
