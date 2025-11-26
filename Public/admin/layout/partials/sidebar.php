<?php
require_once __DIR__ . '/../../../../App/config/auth.php';
require_once __DIR__ . '/../../../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar bg-dark text-white vh-100 d-flex flex-column justify-content-between p-3 shadow">
  <div>
    <div class="brand fs-4 fw-bold text-warning mb-4 text-center">Drip N' Style</div>
    <ul class="nav flex-column gap-2" id="sidebarMenu">
      <li>
        <a href="../../Public/admin/dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a>
      </li>
      <li>
        <a href="../../Public/admin/products.php" class="nav-link <?= $currentPage === 'products.php' ? 'active' : '' ?>">🛍️ Products</a>
      </li>
      <li>
        <a href="../../Public/admin/orders.php" class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>">📦 Orders</a>
      </li>
      <li>
        <a href="../../Public/admin/payments.php" class="nav-link <?= $currentPage === 'payments.php' ? 'active' : '' ?>">💳 Payments</a>
      </li>
      <li>
        <a href="../../Public/admin/payments.php" class="nav-link <?= $currentPage === 'settings.php' ? 'active' : '' ?>">⚙️ Settings</a>
      </li>
    </ul>
  </div>

  <div class="text-center mt-auto">
    <a href="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=logout" class="btn btn-warning btn-logout fw-bold">Logout</a>
  </div>
</nav>
