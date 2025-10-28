<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../Public/LoginPage.php");
    exit;
}

require_once __DIR__ . '/../../App/Controllers/AdminController.php';
$admin = new AdminController();

$adminName = $_SESSION['user_name'] ?? 'Admin';

$totalProducts = $admin->totalProducts();
$totalOrders = $admin->totalOrders();
$totalCustomers = $admin->totalCustomers();
$totalRevenue = $admin->totalRevenue();
$recentOrders = $admin->recentOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | Drip N' Style</title>
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/admin-dashboard.css">
</head>
<body class="bg-light">

  <div class="d-flex">
    <!-- SIDEBAR -->
    <?php include '../Partials/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="flex-grow-1 p-4">
      <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</h2>

      <!-- STAT CARDS -->
      <div class="row g-4 mb-5">
        <div class="col-md-3">
          <div class="card text-center shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Total Products</h5>
              <h3 class="text-warning"><?= $totalProducts ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Orders</h5>
              <h3 class="text-warning"><?= $totalOrders ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Customers</h5>
              <h3 class="text-warning"><?= $totalCustomers ?></h3>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card text-center shadow-sm border-0">
            <div class="card-body">
              <h5 class="card-title fw-bold">Revenue</h5>
              <h3 class="text-warning">₱<?= number_format($totalRevenue, 2) ?></h3>
            </div>
          </div>
        </div>
      </div>

      <!-- RECENT ORDERS -->
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

    </div>
  </div>

  <script src="../assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>
