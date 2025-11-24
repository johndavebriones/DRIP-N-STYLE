<?php
session_start();
require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/DAO/cartDAO.php';

// Validate checkout token to prevent duplicate submissions
if (!isset($_POST['checkout_token']) || !isset($_SESSION['checkout_token']) || 
    $_POST['checkout_token'] !== $_SESSION['checkout_token']) {
    $_SESSION['error'] = "Invalid checkout session. Please try again.";
    header("Location: cart.php");
    exit;
}

// Validate user session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Public/LoginPage.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);
$cartDAO = new CartDAO($conn);

// Validate item_ids from session
$item_ids = isset($_SESSION['checkout_item_ids']) ? $_SESSION['checkout_item_ids'] : [];
if (empty($item_ids)) {
    $_SESSION['error'] = "No items selected for checkout.";
    header("Location: cart.php");
    exit;
}

// Get cart_id and verify items exist
$cart_id = $cartDAO->getOrCreateCart($user_id);
$cartItems = $cartDAO->getCartItemsByIds($cart_id, $item_ids);

if (empty($cartItems)) {
    $_SESSION['error'] = "Selected items not found in cart.";
    header("Location: cart.php");
    exit;
}

// Calculate total for selected items only
$total_amount = 0;
foreach ($cartItems as $item) {
    $total_amount += $item['price_at_time'] * $item['quantity'];
}

if ($total_amount <= 0) {
    $_SESSION['error'] = "Your cart is empty.";
    unset($_SESSION['checkout_token']);
    unset($_SESSION['checkout_item_ids']);
    header("Location: cart.php");
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

// Process order with selected items only
$order_id = $orderController->processOrder(
    $user_id,
    $pickup_date,
    $payment_method,
    $payment_ref,
    $payment_status,
    $item_ids  // Pass selected item IDs to controller
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

// Clear checkout session and mark order as placed
if ($order_id) {
    // Remove ordered items from cart
    foreach ($item_ids as $item_id) {
        $cartDAO->removeFromCart($item_id);
    }
    
    // Clear checkout session variables
    unset($_SESSION['checkout_token']);
    unset($_SESSION['checkout_item_ids']);
    
    // CRITICAL: Mark order as placed - allows ONE-TIME access to success page
    $_SESSION['order_placed'] = true;
    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['success'] = "Order placed successfully! Order ID: #" . $order_id;
    
    // Redirect to success page
    header("Location: success.php?order_id=" . $order_id);
    exit;
} else {
    $_SESSION['error'] = "Checkout failed. Please try again.";
    header("Location: checkout.php");
    exit;
}
?>