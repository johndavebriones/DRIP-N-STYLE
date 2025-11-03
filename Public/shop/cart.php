<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

// ✅ Start session and prevent caching
SessionHelper::preventCache();

$shop = new ShopController();
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
  <link rel="stylesheet" href="../assets/css/shop.css">
<style>
.page-fade { 
  opacity: 0; 
  animation: fadeIn 0.6s ease-in-out forwards; 
}
@keyframes fadeIn { 
  from { opacity: 0; transform: translateY(10px); } 
  to { opacity: 1; transform: translateY(0); } 
}

.cart-table th, .cart-table td { 
  vertical-align: middle; 
}
.cart-table tbody tr:hover { 
  background-color: #f8f9fa; 
}
.cart-empty { 
  min-height: 200px; 
}

.quantity-controls { 
  display: flex; 
  align-items: center; 
  gap: 5px; 
  justify-content: center; 
}
.quantity-controls button { 
  width: 32px; 
  padding: 0; 
}
.quantity-display { 
  width: 40px; 
  text-align: center; 
  font-weight: bold; 
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .cart-table th, .cart-table td { font-size: 0.9rem; }
  .quantity-controls button { width: 28px; height: 28px; font-size: 0.8rem; }
  .quantity-display { width: 30px; font-size: 0.9rem; }
}
@media (max-width: 576px) {
  .cart-table th, .cart-table td { font-size: 0.8rem; padding: 0.4rem; }
  .quantity-controls { flex-direction: column; gap: 2px; }
  .quantity-controls button { width: 28px; height: 28px; font-size: 0.8rem; }
  .quantity-display { width: 28px; font-size: 0.85rem; }
  .text-end { text-align: left !important; }
}
</style>
</head>
<body>
<div id="page-container">
  <?php include '../partials/navbar.php'; ?>

  <main>
    <!-- Cart Header -->
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
            <?php $cartItems = $shop->getCartItems($_SESSION['user_id']); ?>

            <?php if (empty($cartItems)): ?>
                <div class="alert alert-warning text-center cart-empty">
                    <p>Your cart is currently empty.</p>
                    <a href="shop.php" class="btn btn-warning mt-3">Continue Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover cart-table align-middle shadow-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $total = 0; ?>
                            <?php foreach ($cartItems as $item): ?>
                                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                                <?php $total += $subtotal; ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['name']) ?></td>
                                    <td>₱<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <form method="POST" action="../../App/Controllers/CartController.php" class="quantity-controls">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <button type="submit" name="quantity_action" value="decrease" class="btn btn-sm btn-outline-secondary">-</button>
                                            <span class="quantity-display"><?= $item['quantity'] ?></span>
                                            <button type="submit" name="quantity_action" value="increase" class="btn btn-sm btn-outline-secondary">+</button>
                                        </form>
                                    </td>
                                    <td>₱<?= number_format($subtotal, 2) ?></td>
                                    <td>
                                        <form method="POST" action="../../App/Controllers/CartController.php">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td>₱<?= number_format($total, 2) ?></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-end mt-3">
                        <a href="test_checkout.php" class="btn btn-warning fw-bold">Proceed to Checkout</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include '../Partials/footer.php'; ?>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_SESSION['update_success'])): ?>
<script>
Swal.fire({
  toast: true,
  position: 'bottom-end',
  icon: 'success',
  title: 'Quantity updated!',
  showConfirmButton: false,
  timer: 1500,
  timerProgressBar: true
});
</script>
<?php unset($_SESSION['update_success']); endif; ?>

<?php if(isset($_SESSION['stock_limit'])): ?>
<script>
Swal.fire({
    icon: 'warning',
    title: 'Stock limit reached!',
    toast: true,
    position: 'bottom-end',
    showConfirmButton: false,
    timer: 1500
});
</script>
<?php unset($_SESSION['stock_limit']); endif; ?>
</body>
</html>
