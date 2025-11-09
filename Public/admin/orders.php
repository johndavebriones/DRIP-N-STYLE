<?php
require_once __DIR__ . '/../../App/Controllers/OrderController.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);

$orders = $orderController->getAllOrders();
$title = "Orders Management";

ob_start();
?>

<style>
  .page-header {
    background: linear-gradient(90deg, #343a40, #495057);
    color: white;
    padding: 1.2rem 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
  }

  .sticky-filters {
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .table thead {
    background-color: #343a40;
    color: #fff;
  }

  .table-hover tbody tr:hover {
    background-color: #f0f0f0;
    transition: 0.2s;
  }

  .btn-modern {
    border-radius: 10px;
    font-weight: 600;
    transition: 0.2s ease;
  }

  .btn-modern:hover {
    transform: translateY(-2px);
  }

  .badge {
    font-size: 0.85rem;
    padding: 0.45em 0.6em;
    border-radius: 10px;
  }

  .filter-card {
    border: 1px solid #dee2e6;
    background: #fff;
    border-radius: 10px;
    padding: 1rem 1.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  }

  .table-wrapper {
    background: white;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  }
</style>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <h2 class="fw-bold mb-0">📦 Orders Management</h2>
  <button class="btn btn-outline-light btn-modern" id="refreshOrdersBtn">
    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
  </button>
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
        <option value="GCash">GCash</option>
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
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
            <tr>
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
              <td>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-dark btn-modern view-order-btn"
                          data-order-id="<?= $order['order_id'] ?>"
                          title="View Order">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-success btn-modern update-status-btn"
                          data-order-id="<?= $order['order_id'] ?>"
                          title="Update Status">
                    <i class="bi bi-arrow-repeat"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-4">
              <i class="bi bi-inbox fs-4 d-block mb-2"></i>No orders found.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- 🔹 View Order Modal -->
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

<script>
document.querySelectorAll('.view-order-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.orderId;
    fetch(`view_order.php?id=${id}`)
      .then(res => res.text())
      .then(html => {
        document.getElementById('orderDetailsBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('orderModal')).show();
      });
  });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>
