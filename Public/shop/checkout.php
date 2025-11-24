<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Public/LoginPage.php");
    exit;
}

// Prevent direct access or refresh after order placement
if (isset($_SESSION['order_placed']) && $_SESSION['order_placed'] === true) {
    unset($_SESSION['order_placed']);
    header("Location: shop.php");
    exit;
}

// Handle checkout session
if (isset($_GET['item_ids']) && !empty($_GET['item_ids'])) {
    // Coming from cart with item_ids - this is a NEW checkout attempt
    
    // Check if we have an existing token with different items
    if (isset($_SESSION['checkout_token']) && 
        isset($_SESSION['checkout_item_ids']) && 
        $_GET['item_ids'] !== $_SESSION['checkout_item_ids']) {
        // Different items - clear old session and create new one
        unset($_SESSION['checkout_token']);
        unset($_SESSION['checkout_item_ids']);
        unset($_SESSION['checkout_timestamp']);
        unset($_SESSION['checkout_loaded']);
    }
    
    // Create new checkout session (or refresh existing one with same items)
    $_SESSION['checkout_token'] = bin2hex(random_bytes(16));
    $_SESSION['checkout_item_ids'] = $_GET['item_ids'];
    $_SESSION['checkout_timestamp'] = time();
    $_SESSION['checkout_loaded'] = true; // Mark as loaded
    
} else {
    // No item_ids in URL - check if we have a valid existing session
    
    if (!isset($_SESSION['checkout_token']) || 
        !isset($_SESSION['checkout_item_ids']) || 
        !isset($_SESSION['checkout_loaded'])) {
        // No valid checkout session - redirect to cart
        $_SESSION['error'] = "Checkout session expired. Please try again.";
        header("Location: cart.php");
        exit;
    }
    
    // Valid session exists - this means user is refreshing the page
    // Clear session and redirect
    unset($_SESSION['checkout_token']);
    unset($_SESSION['checkout_item_ids']);
    unset($_SESSION['checkout_timestamp']);
    unset($_SESSION['checkout_loaded']);
    
    $_SESSION['error'] = "Checkout session expired. Please try again.";
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/cartDAO.php';

$db = new Database();
$conn = $db->connect();
$cartDAO = new CartDAO($conn);

/* -----------------------------
   Get item_ids from session
----------------------------- */
$item_ids = isset($_SESSION['checkout_item_ids']) ? $_SESSION['checkout_item_ids'] : [];
$item_ids = is_array($item_ids) ? $item_ids : [];

$cartItems = [];
$total = 0;

if (!empty($item_ids)) {
    // Get the user's cart_id first
    $cart_id = $cartDAO->getOrCreateCart($user_id);

    // Fetch only the selected items
    $cartItems = $cartDAO->getCartItemsByIds($cart_id, $item_ids);

    foreach ($cartItems as $item) {
        $total += $item['price_at_time'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | Drip N' Style</title>
<link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/navbar.css">
<link rel="stylesheet" href="../assets/css/footer.css">
<style>
.order-item-details {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
.order-item-details span {
    display: inline-block;
    margin-right: 1rem;
}
</style>

<!-- Script to prevent bfcache -->
<script>
(function() {
    // Only prevent page from being cached (bfcache)
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.replace('cart.php');
        }
    };
})();
</script>
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<div class="container my-5">
  <div class="checkout-container p-4 bg-white rounded shadow">
    <h2 class="mb-4">Checkout</h2>

    <?php if (empty($cartItems)): ?>
      <div class="alert alert-warning">
        <p>No selected items found. <a href="cart.php" class="btn btn-warning btn-sm">Back to Cart</a></p>
      </div>
    <?php else: ?>

      <form id="checkoutForm" action="checkout_process.php" method="POST" enctype="multipart/form-data">

        <!-- Pass checkout token for validation -->
        <input type="hidden" name="checkout_token" value="<?= htmlspecialchars($_SESSION['checkout_token']) ?>">

        <!-- Pass item_ids[] to process page -->
        <?php foreach ($item_ids as $id): ?>
            <input type="hidden" name="item_ids[]" value="<?= htmlspecialchars($id) ?>">
        <?php endforeach; ?>

        <!-- Pickup Date -->
        <div class="mb-3">
          <label for="pickup_date" class="form-label">Pick-up Date</label>
          <input type="date" name="pickup_date" id="pickup_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Payment Method -->
        <div class="mb-3">
          <label for="payment_method" class="form-label">Payment Method</label>
          <select name="payment_method" id="payment_method" class="form-select" required>
            <option value="Cash on Pickup" selected>Cash on Pickup</option>
          </select>
        </div>

        <!-- Order Summary -->
        <h4 class="mt-4">Order Summary</h4>
        <ul class="list-group mb-3">
          <?php foreach ($cartItems as $item): ?>
            <li class="list-group-item">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</div>
                  <div class="order-item-details">
                    <?php if (!empty($item['color'])): ?>
                      <span><strong>Color:</strong> <?= htmlspecialchars($item['color']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($item['description'])): ?>
                      <span><strong>Description:</strong> <?= htmlspecialchars($item['description']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <span class="fw-bold">₱<?= number_format($item['price_at_time'] * $item['quantity'], 2) ?></span>
              </div>
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
(function() {
    'use strict';
    
    let isSubmitting = false;
    let isCancelling = false;
    
    // Form submission handling
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function() {
            isSubmitting = true;
        });
    }
    
    // Cancel button handling - clears session and redirects
    const cancelBtn = document.getElementById('cancelOrderBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (confirm("Are you sure you want to cancel your order?")) {
                isCancelling = true;
                
                // Clear checkout session via AJAX
                fetch('clear_checkout_session.php', { 
                    method: 'POST',
                    credentials: 'same-origin'
                })
                .finally(() => {
                    window.location.replace('cart.php');
                });
            }
        });
    }
    
    // Payment method behavior
    const paymentMethod = document.getElementById('payment_method');
    const gcashFields = document.getElementById('gcash_fields');
    const paymentRef = document.getElementById('payment_ref');
    const proofImage = document.getElementById('proof_image');

    if (paymentMethod) {
        paymentMethod.addEventListener('change', () => {
            if (paymentMethod.value === 'GCash') {
                if (gcashFields) gcashFields.classList.remove('d-none');
                if (paymentRef) paymentRef.required = true;
                if (proofImage) proofImage.required = true;
            } else {
                if (gcashFields) gcashFields.classList.add('d-none');
                if (paymentRef) paymentRef.required = false;
                if (proofImage) proofImage.required = false;
            }
        });
    }
    
})();
</script>

</body>
</html>