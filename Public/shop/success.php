<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

require_once __DIR__ . '/../../App/Config/database_connect.php';
require_once __DIR__ . '/../../App/DAO/OrderDAO.php';

$db = new Database();
$conn = $db->connect();
$orderDAO = new OrderDAO($conn);

$order_id = $_GET['order_id'] ?? null;
$order = null;
$orderItems = [];

if ($order_id) {
  $orderQuery = $conn->prepare("
    SELECT o.*, p.payment_method, p.payment_status
    FROM orders o
    LEFT JOIN payments p ON o.payment_id = p.payment_id
    WHERE o.order_id = ?
  ");
  $orderQuery->bind_param("i", $order_id);
  $orderQuery->execute();
  $order = $orderQuery->get_result()->fetch_assoc();

  $orderItems = $orderDAO->getOrderItems($order_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Success | Drip N' Style</title>
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/shop.css">
  <style>
    body { background-color: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .success-container {
      max-width: 800px; margin: 80px auto; background: #fff;
      border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 2rem;
    }
    .success-icon {
      font-size: 4rem; color: #28a745;
    }
    .order-summary {
      background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px;
      padding: 1rem; margin-top: 1rem;
    }
  </style>
</head>
<body>

<?php include '../partials/navbar.php'; ?>

<div class="success-container text-center">
  <?php if ($order): ?>
    <div class="success-icon mb-3"><i class="bi bi-check-circle-fill"></i></div>
    <h3 class="fw-bold text-success">Order Placed Successfully!</h3>
    <p class="text-muted">Thank you for shopping with <span class="text-warning fw-bold">Drip N' Style</span>.</p>

    <div class="order-summary text-start mt-4">
      <h5 class="fw-bold mb-3">Order Details</h5>
      <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
      <p><strong>Order Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
      <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method'] ?? '-') ?></p>
      <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status'] ?? '-') ?></p>
      <hr>
      <h6 class="fw-bold">Items Ordered:</h6>
      <ul class="list-group mb-3">
        <?php foreach ($orderItems as $item): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
              <img src="../../Public/<?= htmlspecialchars($item['image'] ?: 'uploads/no-image.png') ?>" 
                   alt="<?= htmlspecialchars($item['product_name']) ?>" width="50" height="50"
                   style="object-fit:cover; border-radius:8px; margin-right:10px;">
              <?= htmlspecialchars($item['product_name']) ?> (x<?= (int)$item['quantity'] ?>)
            </div>
            <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="text-end fw-bold">
        Total: ₱<?= number_format($order['total_amount'], 2) ?>
      </div>
    </div>

    <div class="mt-4">
      <a href="shop.php" class="btn btn-warning"><i class="bi bi-bag"></i> Continue Shopping</a>
      <a href="orders.php" class="btn btn-outline-dark"><i class="bi bi-clock-history"></i> View My Orders</a>
    </div>

  <?php else: ?>
    <div class="text-danger">
      <i class="bi bi-exclamation-triangle-fill"></i> Invalid or missing order ID.
    </div>
  <?php endif; ?>
</div>

<?php include '../partials/footer.php'; ?>
</body>
</html>
