<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);

$orders = $orderController->getAllOrders();
$title = "Orders Management";

ob_start();
?>
<link rel="stylesheet" href="assets/css/orders.css">
<link rel="stylesheet" href="assets/css/admin-page-header.css">

<div class="page-fade">
  <!-- ── Page Header ── -->
  <div class="admin-page-header mb-4">
    <div class="admin-page-header-inner d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="admin-page-icon">
          <i class="bi bi-box-seam"></i>
        </div>
        <div>
          <h2 class="admin-page-title mb-0">Orders Management</h2>
          <p class="admin-page-sub mb-0">Track, filter, and manage all customer orders.</p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="admin-page-badge"><i class="bi bi-calendar3 me-1"></i><?= date('F j, Y') ?></span>
      </div>
    </div>
  </div>

  <!-- 🔹 Filters -->
  <form method="GET" id="filterForm" class="sticky-filters filter-card mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-md-4">
        <input type="text" name="search" class="form-control shadow-sm"
              placeholder="🔍 Search by Order ID or Customer...">
      </div>
      <div class="col-md-4">
        <select name="status" class="form-select shadow-sm">
          <option value="">All Status</option>
          <option value="Pending">Pending</option>
          <option value="Confirmed">Confirmed</option>
          <option value="Ready for Pickup">Ready for Pickup</option>
          <option value="Completed">Completed</option>
          <option value="Cancelled">Cancelled</option>
        </select>
      </div>
      <div class="col-md-4">
        <select name="payment" class="form-select shadow-sm">
          <option value="">All Payment Methods</option>
          <option value="Cash on Pickup">Cash on Pickup</option>
        </select>
      </div>
    </div>
  </form>

  <!-- 🔹 Orders Table -->
  <div class="table-wrapper">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment Method</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Order Date</th>
            <th>Pickup Date</th>
          </tr>
        </thead>

        <tbody>
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <tr class="order-row"
                  onclick="window.location='view_order.php?order_id=<?= urlencode($order['order_id']) ?>'"
                  style="cursor:pointer;">

                <td class="fw-semibold">#<?= htmlspecialchars($order['order_id']) ?></td>
                <td><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                <td class="fw-bold text-success">₱<?= number_format($order['total_amount'], 2) ?></td>
                <td><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></td>

                <td>
                  <span class="badge bg-<?= $order['payment_status'] === 'Paid' ? 'success' : 'secondary' ?>">
                    <?= htmlspecialchars($order['payment_status']) ?>
                  </span>
                </td>

                <td>
                  <span class="badge 
                    <?php
                    echo match($order['order_status']) {
                      'Pending' => 'bg-warning text-dark',
                      'Confirmed' => 'bg-info text-dark',
                      'Ready for Pickup' => 'bg-primary',
                      'Completed' => 'bg-success',
                      'Cancelled' => 'bg-danger',
                      default => 'bg-secondary'
                    };
                    ?>">
                    <?= htmlspecialchars($order['order_status']) ?>
                  </span>
                </td>

                <td><?= date('Y-m-d', strtotime($order['order_date'])) ?></td>
                <td><?= htmlspecialchars($order['pickup_date'] ?? '-') ?></td>

              </tr>
            <?php endforeach; ?>

          <?php else: ?>
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No orders found.
              </td>
            </tr>
          <?php endif; ?>
          </tbody>

      </table>
    </div>
  </div>

  <!-- 🔹 Order Details Modal -->
  <div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-gradient text-white" style="background: linear-gradient(90deg, #ffc107, #ffca2c);">
          <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Order Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="orderDetailsBody">
          <div class="text-center text-muted">Loading order details...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.page-fade {
  opacity: 0;
  animation: fadeIn 0.6s ease-in-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<script src="assets/js/orders.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>