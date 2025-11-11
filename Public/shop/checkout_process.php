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

// Get cart total
$amount = $cartDAO->getCartTotal($user_id);
if ($amount <= 0) {
    $_SESSION['error'] = "Your cart is empty.";
    header("Location: checkout.php");
    exit;
}

// Get form inputs
$pickup_date = $_POST['pickup_date'] ?? date('Y-m-d');

$raw_method = $_POST['payment_method'] ?? '';
$payment_method = match($raw_method) {
    'Pay at Store', 'Cash on Pickup' => 'Cash on Pickup',
    'GCash' => 'GCash',
    default => 'Other'
};

$payment_ref = $_POST['payment_ref'] ?? null;
$payment_status = ($payment_method === 'GCash') ? 'Paid' : 'Pending';

// Validate GCash reference
if ($payment_method === 'GCash' && empty($payment_ref)) {
    $_SESSION['error'] = "Please enter your GCash reference number.";
    header("Location: checkout.php");
    exit;
}

// 1️⃣ Create order with pickup date
$order_id = $orderController->processOrder(
    $user_id,
    $pickup_date,     // <-- added pickup date
    $payment_method,
    $payment_ref,
    $payment_status
);

if ($order_id) {
    // 2️⃣ Insert payment
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, payment_method, payment_ref, amount, payment_status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issds", $order_id, $payment_method, $payment_ref, $amount, $payment_status);
    $stmt->execute();

    $payment_id = $stmt->insert_id;

    // 3️⃣ Link payment to order
    $stmt2 = $conn->prepare("UPDATE orders SET payment_id = ? WHERE order_id = ?");
    $stmt2->bind_param("ii", $payment_id, $order_id);
    $stmt2->execute();

    // 4️⃣ Handle GCash proof image
    if ($payment_method === 'GCash' && isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../Public/uploads/payments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $filename = 'proof_' . time() . '_' . basename($_FILES['proof_image']['name']);
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target_path)) {
            $proof_image_path = 'uploads/payments/' . $filename;

            $stmt3 = $conn->prepare("UPDATE payments SET proof_image = ? WHERE payment_id = ?");
            $stmt3->bind_param("si", $proof_image_path, $payment_id);
            $stmt3->execute();
        }
    }

    // 5️⃣ Redirect to success
    header("Location: success.php?order_id=" . $order_id);
    exit;
} else {
    $_SESSION['error'] = "Checkout failed. Please try again.";
    header("Location: checkout.php");
    exit;
}
