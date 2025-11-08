<?php
require_once __DIR__ . '/../DAO/OrderDAO.php';
require_once __DIR__ . '/../DAO/CartDAO.php';

class OrderController {
    private $orderDAO;
    private $cartDAO;

    public function __construct($conn) {
        $this->orderDAO = new OrderDAO($conn);
        $this->cartDAO = new CartDAO($conn);
    }

    /**
     * ✅ Process a new order:
     * - Get all cart items
     * - Create order record
     * - Add items to order_items
     * - Create payment record
     * - Link payment to order
     * - Clear the cart
     */
    public function processOrder($user_id, $payment_method, $payment_ref = null, $payment_status = 'Pending') {
        // 1️⃣ Get user's cart
        $cartItems = $this->cartDAO->getCartItems($user_id);
        if (empty($cartItems)) {
            return false; // Nothing to order
        }

        // 2️⃣ Compute total
        $total_amount = 0;
        foreach ($cartItems as $item) {
            $total_amount += $item['price_at_time'] * $item['quantity'];
        }

        // 3️⃣ Create new order record
        $order_id = $this->orderDAO->createOrder($user_id, $total_amount);

        // 4️⃣ Add all items to order_items table
        $this->orderDAO->addOrderItems($order_id, $cartItems);

        // 5️⃣ Create payment record
        $payment_id = $this->orderDAO->createPayment(
            $order_id,
            $payment_method,
            $payment_ref,
            $total_amount,
            $payment_status
        );

        // 6️⃣ Link payment record to the order
        $this->orderDAO->linkPaymentToOrder($order_id, $payment_id);

        // 7️⃣ Clear user’s cart after successful checkout
        $this->cartDAO->clearCart($user_id);

        return $order_id;
    }

    /**
     * ✅ Update order after payment gateway callback (e.g., PayMongo success)
     */
    public function confirmPayment($order_id, $payment_ref, $status = 'Paid') {
        $this->orderDAO->updateOrderStatus($order_id, 'Ready for Pickup');
        $this->orderDAO->createPayment($order_id, 'GCash', $payment_ref, 0, $status);
    }

    /**
     * ✅ Retrieve all orders for a specific user
     */
    public function getUserOrders($user_id) {
        return $this->orderDAO->getUserOrders($user_id);
    }

    // ✅ Fetch all orders (Admin View)
    public function getAllOrders() {
        return $this->orderDAO->getAllOrders();
    }

    // ✅ Fetch single order items
    public function getOrderItems($order_id) {
        return $this->orderDAO->getOrderItems($order_id);
    }

    // ✅ Update status (Admin change)
    public function updateStatus($order_id, $status) {
        $this->orderDAO->updateOrderStatus($order_id, $status);
    }

}
