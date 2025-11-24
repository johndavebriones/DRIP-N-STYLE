<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['checkout_token'])) {
    unset($_SESSION['checkout_token']);
    unset($_SESSION['checkout_item_ids']);
    unset($_SESSION['checkout_timestamp']);
}
require_once __DIR__ . '/../../App/DAO/cartDAO.php';
require_once __DIR__ . '/../../App/config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$cart = new CartDAO($conn);
$cartItems = $cart->getCartItems($_SESSION['user_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart | Drip N' Style</title>

<link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="assets/css/cart.css">
<link rel="stylesheet" href="assets/css/shop.css">
<link rel="stylesheet" href="../assets/css/navbar.css">
<link rel="stylesheet" href="../assets/css/footer.css">
</head>

<body>
<div id="page-container">

  <?php include '../partials/navbar.php'; ?>

  <main>
    <section class="shop-header text-center py-5 bg-dark text-warning">
      <div class="container">
        <h1 class="fw-bold">Your Cart</h1>
        <?php if(isset($_SESSION['user_name'])): ?>
          <p class="mb-0 text-light">Hello, <?= htmlspecialchars($_SESSION['user_name']); ?>! Review your cart below.</p>
        <?php else: ?>
          <p class="mb-0 text-light">Please log in to see your cart and continue shopping.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="py-5 page-fade">
      <div class="container">

        <?php if (empty($cartItems)): ?>
          <div class="alert alert-warning text-center cart-empty">
            <p>Your cart is currently empty.</p>
            <a href="shop.php" class="btn btn-warning mt-3">Continue Shopping</a>
          </div>

        <?php else: ?>

          <!-- CART TABLE (NO OUTER FORM) -->
          <div class="table-responsive mt-4">
            <table class="table align-middle cart-table shadow-sm">
              <thead class="table-light">
                <tr>
                  <th style="width: 35%">Product</th>
                  <th class="text-center" style="width: 15%">Quantity</th>
                  <th style="width: 10%">Price</th>
                  <th style="width: 10%">Subtotal</th>
                  <th style="width: 10%">Action</th>
                  <th style="width: 5%"></th>
                </tr>
              </thead>

              <tbody>
              <?php foreach ($cartItems as $item): ?>
                <tr data-stock="<?= $item['stock'] ?>">

                  <!-- PRODUCT INFO -->
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="../../Public/<?= htmlspecialchars($item['image'] ?: 'uploads/no-image.png') ?>"
                           alt="<?= htmlspecialchars($item['name']) ?>"
                           style="width:80px;height:80px;object-fit:cover;" class="rounded shadow-sm">

                      <div class="ms-3">
                        <strong class="d-block mb-1"><?= htmlspecialchars($item['name']) ?></strong>
                        
                        <?php if (!empty($item['size'])): ?>
                          <small class="text-muted d-block">
                            Size: <span class="fw-semibold"><?= htmlspecialchars($item['size']) ?></span>
                          </small>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['color'])): ?>
                          <small class="text-muted d-block">
                            Color: <span class="fw-semibold"><?= htmlspecialchars($item['color']) ?></span>
                          </small>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['description'])): ?>
                          <small class="text-muted d-block mt-1"><?= htmlspecialchars($item['description']) ?></small>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>

                  <!-- QUANTITY -->
                  <td class="text-center">
                    <div class="d-inline-flex justify-content-center align-items-center">
                      <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn"
                              data-item="<?= $item['item_id'] ?>" data-action="decrease">−</button>

                      <span class="mx-3 fw-bold quantity-value" style="min-width: 30px; text-align: center;"><?= $item['quantity'] ?></span>

                      <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn"
                              data-item="<?= $item['item_id'] ?>" data-action="increase">+</button>
                    </div>
                  </td>

                  <!-- PRICE + SUBTOTAL -->
                  <td class="price">₱<?= number_format($item['price_at_time'], 2) ?></td>
                  <td class="subtotal fw-semibold">₱<?= number_format($item['price_at_time'] * $item['quantity'], 2) ?></td>

                  <!-- REMOVE BUTTON (SEPARATE FORM) -->
                  <td>
                    <form method="POST" action="../../App/Controllers/CartController.php" class="remove-item-form d-inline">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i> Remove
                      </button>
                    </form>
                  </td>

                  <!-- CHECKBOX -->
                  <td class="text-center">
                    <input class="form-check-input select-item"
                           type="checkbox"
                           value="<?= $item['item_id'] ?>">
                  </td>

                </tr>
              <?php endforeach; ?>

                <!-- TOTAL ROW -->
                <tr class="fw-bold">
                  <td colspan="3" class="text-end">Total:</td>
                  <td class="total text-end">₱0.00</td>
                  <td colspan="2"></td>
                </tr>

              </tbody>
            </table>
          </div>
          <form id="checkoutForm" method="GET" action="/DRIP-N-STYLE/Public/shop/checkout.php">
            <div class="text-end mt-3">
              <button type="submit" id="checkoutBtn" class="btn btn-warning fw-bold" disabled>
                Proceed to Checkout
              </button>
            </div>
          </form>

        <?php endif; ?>

      </div>
    </section>
  </main>

  <?php include '../partials/footer.php'; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/cart.js"></script>
</body>
</html>