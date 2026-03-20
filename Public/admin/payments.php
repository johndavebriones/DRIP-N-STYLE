<?php
require_once __DIR__ . '/../../App/DAO/paymentsDAO.php';
require_once __DIR__ . '/../../App/Config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$paymentController = new PaymentDAO($conn);

$payments = $paymentController->getAllPayments();
$title = "Payments Management";

ob_start();
?>
<link rel="stylesheet" href="assets/css/payments.css">

<div class="page-fade">
  <div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">💰 Payments Management</h2>
  </div>

  <!-- 🔹 Filters -->
  <form method="GET" id="filterForm" class="sticky-filters filter-card mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-md-4">
        <input type="text" id="searchPayment" name="search" class="form-control shadow-sm"
              placeholder="🔍 Search by Payment ID, Order, or Customer...">
      </div>
      <div class="col-md-3">
        <select id="statusFilter" name="status" class="form-select shadow-sm">
          <option value="">All Statuses</option>
          <option value="Paid">Paid</option>
          <option value="Pending">Pending</option>
          <option value="Failed">Failed</option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="date" id="startDate" name="start_date" class="form-control shadow-sm">
      </div>
      <div class="col-md-2">
        <input type="date" id="endDate" name="end_date" class="form-control shadow-sm">
      </div>
      <div class="col-md-1">
        <button type="button" id="applyDateFilter" class="btn btn-primary w-100">Apply</button>
      </div>
    </div>
  </form>

  <!-- 🔹 Payments Table -->
    <div class="table-wrapper">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
            <th>Payment ID</th>
            <th>Order #</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Reference</th>
            <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $payment): ?>
                <tr class="payment-row"
                    data-payment-id="<?= $payment['payment_id'] ?>"
                    data-order-id="<?= $payment['order_id'] ?>"
                    data-amount="<?= $payment['amount'] ?>"
                    data-method="<?= htmlspecialchars($payment['method'] ?? 'N/A') ?>"
                    data-status="<?= $payment['payment_status'] ?>"
                    data-ref="<?= htmlspecialchars($payment['payment_ref'] ?? '') ?>"
                    data-date="<?= $payment['payment_date'] ?>"
                    data-proof="<?= $payment['proof_image'] ?? '' ?>"
                    style="cursor:pointer;">
                <td class="fw-semibold">#<?= htmlspecialchars($payment['payment_id']) ?></td>
                <td>#<?= htmlspecialchars($payment['order_id']) ?></td>
                <td>
                    <div class="fw-semibold"><?= htmlspecialchars($payment['customer_name'] ?? 'Guest') ?></div>
                    <small class="text-muted"><?= htmlspecialchars($payment['email'] ?? '-') ?></small>
                </td>
                <td class="fw-bold text-success">₱<?= number_format($payment['amount'], 2) ?></td>
                <td><?= htmlspecialchars($payment['payment_method'] ?? 'N/A') ?></td>
                <td>
                    <span class="badge 
                    <?= $payment['payment_status'] === 'Paid' ? 'bg-success' : ($payment['payment_status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                    <?= htmlspecialchars($payment['payment_status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($payment['payment_ref'] ?? '-') ?></td>
                <td><?= date('Y-m-d', strtotime($payment['payment_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No payments found.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
        </table>
    </div>
    </div>

    <!-- 🔹 Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-gradient text-white" style="background: linear-gradient(90deg, #0d6efd, #0d6efd);">
            <h5 class="modal-title fw-bold"><i class="bi bi-credit-card me-2"></i>Payment Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="paymentDetailsContent">
            <div class="text-center text-muted">Loading payment details...</div>
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
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script src="assets/js/payments.js"></script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout/main.php';
?>