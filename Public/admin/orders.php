<?php
session_start();
require_once __DIR__ . '/../../App/config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';

$db = new Database();
$conn = $db->connect();
$orderController = new OrderController($conn);

$title = "Orders Management";
$orders = $orderController->getAllOrders();

ob_start();
?>
<div class="container py-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-bag-check"></i> Orders Management</h2>

  <?php if (empty($orders)): ?>
    <div class="alert alert-warning text-center">No orders yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle text-center shadow-sm">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Pickup Date</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td><?= $o['order_id'] ?></td>
              <td><?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></td>
              <td>₱<?= number_format($o['total'], 2) ?></td>
              <td><?= $o['pickup_date'] ? date('M d, Y h:i A', strtotime($o['pickup_date'])) : 'N/A' ?></td>
              <td>
                <?= htmlspecialchars($o['payment_method'] ?? '—') ?><br>
                <small class="text-muted"><?= htmlspecialchars($o['payment_status'] ?? '—') ?></small>
              </td>
              <td>
                <form method="POST" action="update_order_status.php" class="d-inline">
                  <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                  <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php
                    $statuses = ['Pending','Confirmed','Ready for Pickup','Completed','Cancelled'];
                    foreach ($statuses as $s):
                      $sel = $s === $o['status'] ? 'selected' : '';
                      echo "<option value='$s' $sel>$s</option>";
                    endforeach;
                    ?>
                  </select>
                </form>
              </td>
              <td>
                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                        data-bs-target="#orderModal" 
                        data-orderid="<?= $o['order_id'] ?>">
                  <i class="bi bi-eye"></i> View
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal for Order Details -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-light">
        <h5 class="modal-title"><i class="bi bi-receipt"></i> Order Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="orderDetails">Loading...</div>
    </div>
  </div>
</div>

<script>
document.getElementById('orderModal').addEventListener('show.bs.modal', event => {
  const btn = event.relatedTarget;
  const id = btn.getAttribute('data-orderid');
  fetch('view_order_details.php?id=' + id)
    .then(res => res.text())
    .then(html => document.getElementById('orderDetails').innerHTML = html);
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
?>