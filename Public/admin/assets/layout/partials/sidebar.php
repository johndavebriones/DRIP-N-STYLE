<?php
require_once __DIR__ . '/../../../../../App/config/auth.php';
require_once __DIR__ . '/../../../../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar bg-dark text-white vh-100 d-flex flex-column justify-content-between p-3 shadow">
  <div>
    <div class="brand fs-4 fw-bold text-warning mb-4 text-center">Drip N' Style</div>
    <ul class="nav flex-column gap-2">
      <li>
        <a href="../../Public/admin/dashboard.php" class="nav-link text-white <?= $currentPage === 'dashboard.php' ? 'active text-warning fw-bold' : '' ?>">🏠 Dashboard</a>
      </li>
      <li>
        <a href="../../Public/admin/products.php" class="nav-link text-white <?= $currentPage === 'products.php' ? 'active text-warning fw-bold' : '' ?>">🛍️ Products</a>
      </li>
      <li>
        <a href="../../Public/admin/orders.php" class="nav-link text-white <?= $currentPage === 'orders.php' ? 'active text-warning fw-bold' : '' ?>">📦 Orders</a>
      </li>
      <li>
        <a href="../../Public/admin/customers.php" class="nav-link text-white <?= $currentPage === 'customers.php' ? 'active text-warning fw-bold' : '' ?>">👥 Customers</a>
      </li>
      <li>
        <a href="../../Public/admin/payments.php" class="nav-link text-white <?= $currentPage === 'payments.php' ? 'active text-warning fw-bold' : '' ?>">💳 Payments</a>
      </li>
    </ul>
  </div>

  <div class="text-center mt-auto">
    <!-- ✅ Fixed logout link -->
    <a href="<?= BASE_URL ?>App/Controllers/AuthController.php?action=logout" class="btn btn-warning m-3 fw-bold">Logout</a>
  </div>
</nav>

<style>
  .sidebar {
    width: 250px;
    position: fixed;
    top: 0;
    left: 0;
  }

  .nav-link {
    display: block;
    padding: 8px 15px;
    border-radius: 5px;
    transition: background 0.2s;
  }

  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }

  .nav-link.active {
    background-color: rgba(255, 193, 7, 0.2);
  }
</style>
