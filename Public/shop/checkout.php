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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/shop.css">
  <style>
    body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    main { flex: 1; }
    .checkout-container { max-width: 900px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; }
    .checkout-header { background: #111827; color: #ffc107; padding: 1.5rem; text-align: center; font-weight: 700; font-size: 1.5rem; }
    .table img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
    .summary-box { background: #fff8e1; border-radius: 8px; padding: 1.2rem; border: 1px solid #ffe082; }
    .btn-warning { font-weight: 600; width: 100%; }
    .form-label { font-weight: 600; }
  </style>
</head>
<body>

<main>
  <div class="checkout-container p-4 mb-5">
    <div class="checkout-header">Checkout</div>

    <?php if (empty($cartItems)): ?>
      <div class="text-center py-5">
        <h5>Your cart is empty.</h5>
        <a href="shop.php" class="btn btn-warning mt-3">Continue Shopping</a>
      </div>
    <?php else: ?>
      <form action="checkout_process.php" method="POST" enctype="multipart/form-data">
        <div class="table-responsive mt-4">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cartItems as $item): ?>
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <img src="../../Public/<?= htmlspecialchars($item['image'] ?: 'uploads/no-image.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="card-img-top">
                    <div class="ms-3">
                      <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                      <small class="text-muted">Size: <?= htmlspecialchars($item['size'] ?? '-') ?></small><br>
                      <small class="text-muted">Description: <?= htmlspecialchars($item['description'] ?? '-') ?></small>
                    </div>
                  </div>
                </td>
                <td><?= (int)$item['quantity'] ?></td>
                <td>₱<?= number_format($item['price_at_time'], 2) ?></td>
                <td>₱<?= number_format($item['price_at_time'] * $item['quantity'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <hr class="my-4">

        <div class="row g-4">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="pickup_date" class="form-label">Pick-up Date</label>
              <input type="date" name="pickup_date" id="pickup_date" class="form-control" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3">
              <label for="payment_method" class="form-label">Payment Method</label>
              <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="Cash on Pickup" selected>Cash on Pickup</option>
                <option value="GCash">GCash</option>
              </select>
            </div>

            <div class="mb-3" id="gcash_ref_box" style="display:none;">
              <label for="payment_ref" class="form-label">GCash Reference Number</label>
              <input type="text" name="payment_ref" id="payment_ref" class="form-control" placeholder="Enter reference number">
              <small class="text-muted">We’ll verify your payment before preparing your order.</small>
            </div>

            <div class="mb-3" id="gcash_proof_box" style="display:none;">
              <label for="proof_image" class="form-label">Upload Proof of Payment</label>
              <input type="file" name="proof_image" id="proof_image" class="form-control" accept="image/*">
              <small class="text-muted">Please upload your GCash payment screenshot.</small>
            </div>
          </div>

          <div class="col-md-6">
            <div class="summary-box">
              <h5 class="fw-bold mb-3">Order Summary</h5>
              <p class="d-flex justify-content-between mb-2">
                <span>Subtotal:</span>
                <span>₱<?= number_format($total, 2) ?></span>
              </p>
              <p class="d-flex justify-content-between mb-2">
                <span>Pickup Fee:</span>
                <span>₱0.00</span>
              </p>
              <hr>
              <h5 class="d-flex justify-content-between">
                <span>Total:</span>
                <span class="text-warning">₱<?= number_format($total, 2) ?></span>
              </h5>
            </div>

            <div class="mt-4">
              <button type="submit" class="btn btn-warning btn-lg">
                <i class="bi bi-bag-check me-2"></i> Place Order
              </button>
            </div>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</main>

<?php include '../partials/footer.php'; ?>

<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('payment_method')?.addEventListener('change', function() {
  const gcashBox = document.getElementById('gcash_ref_box');
  const proofBox = document.getElementById('gcash_proof_box');
  const isGcash = this.value === 'GCash';
  gcashBox.style.display = isGcash ? 'block' : 'none';
  proofBox.style.display = isGcash ? 'block' : 'none';
});
</script>

</body>
</html>
