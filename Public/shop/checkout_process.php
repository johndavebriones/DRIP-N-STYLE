<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/DAO/CartDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
SessionHelper::preventCache();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Public/LoginPage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = $_POST['pickup_date'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;
    $payment_ref = $_POST['payment_ref'] ?? null;

    if (!$pickup_date || !$payment_method) {
        $_SESSION['checkout_error'] = "Please fill in all required fields.";
        header("Location: checkout.php");
        exit;
    }

    $payment_status = ($payment_method === 'Pay at Store') ? 'Unpaid' : 'Pending Verification';

    $order_id = $orderController->processOrder(
        $user_id,
        $payment_method,
        $payment_ref,
        $payment_status
    );

    if ($order_id) {
        $_SESSION['checkout_success'] = true;
        header("Location: success.php?order_id=" . $order_id);
        exit;
    } else {
        $_SESSION['checkout_error'] = "Something went wrong while placing your order.";
        header("Location: checkout.php");
        exit;
    }
} else {
    header("Location: checkout.php");
    exit;
}
