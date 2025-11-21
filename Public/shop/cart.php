<?php
require_once __DIR__ . '/../../App/DAO/cartDAO.php';
require_once __DIR__ . '/../../App/config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$cart = new CartDAO($conn);

if (session_status() === PHP_SESSION_NONE) session_start();
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
  <link rel="stylesheet" href="assets/css/shop.css">
  <link rel="stylesheet" href="../assets/css/cart.css">
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

        <?php if (!isset($_SESSION['user_id'])): ?>
          <div class="alert alert-warning text-center cart-empty">
            <p>You must <a href="../LoginPage.php" class="fw-bold text-dark">log in</a> to view your cart.</p>
            <a href="shop.php" class="btn btn-warning mt-3">Go to Shop</a>
          </div>
        <?php else: ?>
          <?php $cartItems = $cart->getCartItems($_SESSION['user_id']); ?>

          <?php if (empty($cartItems)): ?>
            <div class="alert alert-warning text-center cart-empty">
              <p>Your cart is currently empty.</p>
              <a href="shop.php" class="btn btn-warning mt-3">Continue Shopping</a>
            </div>
          <?php else: ?>
            <div class="table-responsive mt-4">
              <table class="table align-middle cart-table shadow-sm">
                <thead class="table-light">
                  <tr>
                    <th style="width: 35%">Product</th>
                    <th class="text-center" style="width: 15%">Quantity</th>
                    <th style="width: 10%">Price</th>
                    <th style="width: 10%">Subtotal</th>
                    <th style="width: 10%">Action</th>
                    <th style="width: 5%">Select</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $total = 0; ?>
                  <?php foreach ($cartItems as $item): ?>
                    <?php $subtotal = $item['price_at_time'] * $item['quantity']; ?>
                    <?php $total += $subtotal; ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <img src="../../Public/<?= htmlspecialchars($item['image'] ?: 'uploads/no-image.png') ?>"
                               alt="<?= htmlspecialchars($item['name']) ?>"
                               class="rounded"
                               style="width:70px; height:70px; object-fit:cover;">
                          <div class="ms-3">
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            <small class="text-muted">Size: <?= htmlspecialchars($item['size'] ?? '-') ?></small><br>
                            <small class="text-muted">Description: <?= htmlspecialchars($item['description'] ?? '-') ?></small>
                          </div>
                        </div>
                      </td>

                      <!-- QUANTITY -->
                      <td class="text-center">
                        <form method="POST" action="../../App/Controllers/CartController.php" class="d-inline-flex justify-content-center quantity-form">
                          <input type="hidden" name="action" value="update">
                          <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                          <button type="submit" name="quantity_action" value="decrease" class="btn btn-sm btn-outline-secondary">−</button>
                          <span class="mx-2 fw-bold"><?= $item['quantity'] ?></span>
                          <button type="submit" name="quantity_action" value="increase" class="btn btn-sm btn-outline-secondary">+</button>
                        </form>
                      </td>

                      <td>₱<?= number_format($item['price_at_time'], 2) ?></td>
                      <td>₱<?= number_format($subtotal, 2) ?></td>

                      <!-- REMOVE -->
                      <td>
                        <form method="POST" action="../../App/Controllers/CartController.php">
                          <input type="hidden" name="action" value="remove">
                          <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i> Remove
                          </button>
                        </form>
                      </td>

                      <td>
                        <input type="checkbox" name="select_item[]" value="<?= $item['item_id'] ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>

                  <tr class="fw-bold">
                    <td colspan="3" class="text-end">Total:</td>
                    <td>₱<?= number_format($total, 2) ?></td>
                    <td colspan="2"></td>
                  </tr>
                </tbody>
              </table>

              <div class="text-end mt-3">
                <a href="checkout.php" class="btn btn-warning fw-bold">Proceed to Checkout</a>
              </div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include '../Partials/footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/cart.js"></script>
</body>
</html>
