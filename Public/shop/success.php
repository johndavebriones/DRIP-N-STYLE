<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../App/config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);

$payment_reference = isset($_GET['ref']) ? $_GET['ref'] : 'manual-test';
$payment_status = 'Paid';

$order_id = $orderController->processOrder($user_id, $payment_reference, $payment_status);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
<?php if ($order_id): ?>
Swal.fire({
    title: "Payment Successful!",
    text: "Thank you for your purchase. Your order ID is <?= $order_id ?>.",
    icon: "success",
    confirmButtonText: "Back to Shop"
}).then(() => {
    window.location.href = "../index.php";
});
<?php else: ?>
Swal.fire({
    title: "No Items Found",
    text: "Your cart is empty or there was an issue processing your order.",
    icon: "error",
    confirmButtonText: "Go Back"
}).then(() => {
    window.location.href = "../shop.php";
});
<?php endif; ?>
</script>
</body>
</html>
