<?php
require_once __DIR__ . '/../Models/orderModel.php';
require_once __DIR__ . '/ProductDAO.php';

class OrderDAO {
    public $conn;
    private $productDAO;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->productDAO = new ProductDAO($conn);
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

    public function getOrderItems($order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                oi.order_item_id,
                p.product_id,
                p.name AS product_name,
                p.image,
                p.size,
                p.color,
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

    public function getOrderById($order_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                o.*, 
                u.name AS customer_name,
                u.email AS customer_email,
                u.contact_number AS customer_phone,
                a.address AS customer_address,
                p.payment_method,
                p.payment_status,
                p.payment_id,
                p.amount,
                p.proof_image
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.payment_id = p.payment_id
            LEFT JOIN addresses a ON o.user_id = a.user_id AND a.is_default = 1
            WHERE o.order_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateOrderAndPaymentStatus($order_id, $newOrderStatus = null, $newPaymentStatus = null) {
        // Get current order details
        $order = $this->getOrderById($order_id);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $currentOrderStatus = $order['order_status'] ?? null;
        $currentPaymentStatus = $order['payment_status'] ?? null;

        // Normalize empty strings to null
        $newOrderStatus = (isset($newOrderStatus) && $newOrderStatus !== '') ? $newOrderStatus : null;
        $newPaymentStatus = (isset($newPaymentStatus) && $newPaymentStatus !== '') ? $newPaymentStatus : null;

        // Apply sync rules
        if ($newOrderStatus === 'Cancelled') {
            $newPaymentStatus = 'Failed';
        }
        if ($newPaymentStatus === 'Failed') {
            $newOrderStatus = 'Cancelled';
        }

        // Determine final statuses
        $finalOrderStatus = $newOrderStatus !== null ? $newOrderStatus : $currentOrderStatus;
        $finalPaymentStatus = $newPaymentStatus !== null ? $newPaymentStatus : $currentPaymentStatus;

        // Validation: Cannot complete order if payment is pending
        if ($finalOrderStatus === 'Completed' && ($finalPaymentStatus === 'Pending' || $finalPaymentStatus === null)) {
            return ['success' => false, 'message' => 'Cannot complete order while payment is pending.'];
        }

        // Update payment status if needed
        if (!empty($order['payment_id']) && $newPaymentStatus !== null) {
            $stmt = $this->conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
            $stmt->bind_param("si", $newPaymentStatus, $order['payment_id']);
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Failed to update payment status: ' . $stmt->error];
            }
        }

        // Update order status if needed
        if ($newOrderStatus !== null) {
            $stmt2 = $this->conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
            $stmt2->bind_param("si", $newOrderStatus, $order_id);
            if (!$stmt2->execute()) {
                return ['success' => false, 'message' => 'Failed to update order status: ' . $stmt2->error];
            }
        }

        $orderBecameCompleted = ($finalOrderStatus === 'Completed' && $currentOrderStatus !== 'Completed');
        $paymentIsPaid = ($finalPaymentStatus === 'Paid');

        if ($orderBecameCompleted && $paymentIsPaid) {
            // Reduce stock for ALL items in this order
            $items = $this->getOrderItems($order_id);
            
            if (empty($items)) {
                return ['success' => false, 'message' => 'No items found in this order.'];
            }

            error_log("Order #{$order_id} completed - Reducing stock for " . count($items) . " items");

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (int)$item['quantity'];
                
                error_log("  - Product #{$productId}: Reducing {$quantity} units");
                
                $res = $this->productDAO->reduceStock($productId, $quantity);
                
                if (!$res['success']) {
                    error_log("  - ERROR: Failed to reduce stock for product #{$productId}");
                    return [
                        'success' => false, 
                        'message' => 'Failed reducing stock for ' . $item['product_name'] . ': ' . ($res['error'] ?? 'Unknown error')
                    ];
                }
                
                error_log("  - SUCCESS: Stock reduced for product #{$productId}");
            }

            error_log("Order #{$order_id}: All stocks reduced successfully");
        }

        if ($finalOrderStatus === 'Cancelled') {
            error_log("Order #{$order_id} cancelled - Stock will NOT be increased (as per requirement)");
        }

        return [
            'success' => true, 
            'message' => 'Order and payment statuses updated successfully.',
            'stock_reduced' => ($orderBecameCompleted && $paymentIsPaid)
        ];
    }
}