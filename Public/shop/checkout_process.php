<?php
session_start();
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/DAO/cartDAO.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Public/LoginPage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);
$cartDAO = new CartDAO($conn);

$total_amount = $cartDAO->getCartTotal($user_id);
if ($total_amount <= 0) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: checkout.php");
    exit;
}

// Get form inputs
$pickup_date = $_POST['pickup_date'] ?? date('Y-m-d');
$raw_method = $_POST['payment_method'] ?? '';
$payment_method = ($raw_method === 'GCash') ? 'GCash' : 'Cash on Pickup';
$payment_ref = $_POST['payment_ref'] ?? null;
$payment_status = ($payment_method === 'GCash') ? 'Paid' : 'Pending';

// Validate GCash
if ($payment_method === 'GCash' && empty($payment_ref)) {
    $_SESSION['error'] = "Please enter your GCash reference number.";
    header("Location: checkout.php");
    exit;
}

// Process order
$order_id = $orderController->processOrder(
    $user_id,
    $pickup_date,
    $payment_method,
    $payment_ref,
    $payment_status
);

// Handle proof image
if ($order_id && $payment_method === 'GCash' && isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../../Public/uploads/payments/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $filename = 'proof_' . time() . '_' . basename($_FILES['proof_image']['name']);
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_path)) {
        $proof_image_path = 'uploads/payments/' . $filename;
        // Save proof image in database
        $stmt = $conn->prepare("UPDATE payments SET proof_image=? WHERE order_id=?");
        $stmt->bind_param("si", $proof_image_path, $order_id);
        $stmt->execute();
    }
}

// Redirect to success page
if ($order_id) {
    header("Location: success.php?order_id=" . $order_id);
    exit;
} else {
    $_SESSION['error'] = "Checkout failed. Please try again.";
    header("Location: checkout.php");
    exit;
}
