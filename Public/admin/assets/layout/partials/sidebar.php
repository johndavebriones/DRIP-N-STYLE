<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
  <div class="brand">Drip N' Style</div>
  <ul class="nav flex-column">
    <li><a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">🏠 Dashboard</a></li>
    <li><a href="products.php" class="nav-link <?= $currentPage === 'products.php' ? 'active' : '' ?>">🛍️ Products</a></li>
    <li><a href="orders.php" class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>">📦 Orders</a></li>
    <li><a href="customers.php" class="nav-link <?= $currentPage === 'customers.php' ? 'active' : '' ?>">👥 Customers</a></li>
    <li><a href="payments.php" class="nav-link <?= $currentPage === 'payments.php' ? 'active' : '' ?>">💳 Payments</a></li>
  </ul>
  <a href="../../App/Controllers/AuthController.php?action=logout" class="btn btn-warning m-3 fw-bold">Logout</a>
</nav>
