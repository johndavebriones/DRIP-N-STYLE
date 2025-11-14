<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Public/LoginPage.php");
    exit;
}
$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/cartDAO.php';

$db = new Database();
$conn = $db->connect();
$cartDAO = new CartDAO($conn);

$cartItems = $cartDAO->getCartItems($user_id);
$total = $cartDAO->getCartTotal($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Drip N' Style</title>
<link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<div class="container my-5">
    <div class="checkout-container p-4 bg-white rounded shadow">

        <h2 class="mb-4">Checkout</h2>

        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty. <a href="shop.php" class="btn btn-warning">Continue Shopping</a></p>
        <?php else: ?>
            <form id="checkoutForm" action="checkout_process.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="pickup_date" class="form-label">Pick-up Date</label>
                    <input type="date" name="pickup_date" id="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                        <option value="Cash on Pickup" selected>Cash on Pickup</option>
                        <option value="GCash">GCash</option>
                    </select>
                </div>

                <div id="gcash_fields" style="display:none;">
                    <div class="mb-3">
                        <label for="payment_ref" class="form-label">GCash Reference Number</label>
                        <input type="text" name="payment_ref" id="payment_ref" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="proof_image" class="form-label">Upload Proof of Payment</label>
                        <input type="file" name="proof_image" id="proof_image" class="form-control" accept="image/*">
                    </div>
                </div>

                <h4 class="mt-4">Order Summary</h4>
                <ul class="list-group mb-3">
                    <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)
                            <span>₱<?= number_format($item['price_at_time'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between fw-bold">
                        Total: <span>₱<?= number_format($total, 2) ?></span>
                    </li>
                </ul>

                <div class="d-flex gap-2">
                    <button type="button" id="cancelOrderBtn" class="btn btn-outline-danger">Cancel Order</button>
                    <button type="submit" name="place_order" class="btn btn-warning">Place Order</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
const paymentMethod = document.getElementById('payment_method');
const gcashFields = document.getElementById('gcash_fields');

paymentMethod.addEventListener('change', () => {
    if (paymentMethod.value === 'GCash') {
        gcashFields.style.display = 'block';
        document.getElementById('payment_ref').required = true;
        document.getElementById('proof_image').required = true;
    } else {
        gcashFields.style.display = 'none';
        document.getElementById('payment_ref').required = false;
        document.getElementById('proof_image').required = false;
    }
});

document.getElementById('cancelOrderBtn').addEventListener('click', () => {
    if (confirm("Are you sure you want to cancel your order?")) {
        window.location.href = "shop.php";
    }
});
</script>

</body>
</html>
