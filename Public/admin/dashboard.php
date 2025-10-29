<?php
require_once __DIR__ . '/../../App/Controllers/AdminController.php';
$admin = new AdminController();

$title = "Dashboard";
$totalProducts = $admin->totalProducts();
$totalOrders = $admin->totalOrders();
$totalCustomers = $admin->totalCustomers();
$totalRevenue = $admin->totalRevenue();
$recentOrders = $admin->recentOrders();

ob_start();
?>
<h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?> 👋</h2>

<div class="row g-4 mb-5">
  <!-- Total Products -->
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title fw-bold">Total Products</h5>
        <h3 class="text-warning"><?= $totalProducts ?></h3>
      </div>
    </div>
  </div>
  <!-- Total Orders -->
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title fw-bold">Orders</h5>
        <h3 class="text-warning"><?= $totalOrders ?></h3>
      </div>
    </div>
  </div>
  <!-- Total Customers -->
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title fw-bold">Customers</h5>
        <h3 class="text-warning"><?= $totalCustomers ?></h3>
      </div>
    </div>
  </div>
  <!-- Total Revenue -->
  <div class="col-md-3">
    <div class="card text-center shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title fw-bold">Revenue</h5>
        <h3 class="text-warning">₱<?= number_format($totalRevenue, 2) ?></h3>
      </div>
    </div>
  </div>
</div>

<h4 class="mb-3">Recent Orders</h4>
<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Customer</th>
        <th>Item</th>
        <th>Status</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentOrders as $order): ?>
        <tr>
          <td>#<?= $order['order_id'] ?></td>
          <td><?= htmlspecialchars($order['customer_name']) ?></td>
          <td><?= htmlspecialchars($order['product_name']) ?></td>
          <td>
            <?php
              $badgeClass = match($order['order_status'] ?? '') {
                  'Completed' => 'bg-success',
                  'Pending' => 'bg-warning text-dark',
                  'Cancelled' => 'bg-danger',
                  default => 'bg-secondary'
              };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= $order['order_status'] ?? 'Unknown' ?></span>
          </td>
          <td>₱<?= number_format($order['total_amount'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
