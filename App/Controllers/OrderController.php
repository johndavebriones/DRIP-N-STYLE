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

    public function processOrder($user_id, $pickup_date, $payment_method, $payment_ref = null, $payment_status = 'Pending') {

        $cartItems = $this->cartDAO->getCartItems($user_id);
        if (empty($cartItems)) {
            return false;
        }

        $total_amount = 0;
        foreach ($cartItems as $item) {
            $total_amount += $item['price_at_time'] * $item['quantity'];
        }

        $order_id = $this->orderDAO->createOrder($user_id, $total_amount, $pickup_date);

        $this->orderDAO->addOrderItems($order_id, $cartItems);

        $payment_id = $this->orderDAO->createPayment(
            $order_id,
            $payment_method,
            $payment_ref,
            $total_amount,
            $payment_status
        );

        $this->orderDAO->linkPaymentToOrder($order_id, $payment_id);

        $this->cartDAO->clearCart($user_id);

        return $order_id;
    }


    public function confirmPayment($order_id, $payment_ref, $status = 'Paid') {
        $this->orderDAO->updateOrderStatus($order_id, 'Ready for Pickup');
        $this->orderDAO->createPayment($order_id, 'GCash', $payment_ref, 0, $status);
    }

    public function getUserOrders($user_id) {
    return $this->orderDAO->getUserOrders($user_id);
    }

    public function getAllOrders() {
        return $this->orderDAO->getAllOrders();
    }

    public function getOrderItems($order_id) {
        return $this->orderDAO->getOrderItems($order_id);
    }

    public function updatePaymentProof($order_id, $proof_image_path) {
        $stmt = $this->orderDAO->conn->prepare("
            UPDATE payments p
            JOIN orders o ON o.payment_id = p.payment_id
            SET p.proof_image = ?
            WHERE o.order_id = ?
        ");
        $stmt->bind_param("si", $proof_image_path, $order_id);
        $stmt->execute();
    }

    public function getOrderById($order_id) {
        return $this->orderDAO->getOrderById($order_id);
    }

    public function updateOrderAndPaymentStatus($order_id, $order_status, $payment_status = null) {
        return $this->orderDAO->updateOrderAndPaymentStatus($order_id, $order_status, $payment_status);
    }

}
