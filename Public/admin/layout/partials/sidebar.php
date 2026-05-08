<?php
require_once __DIR__ . '/../../../../App/Config/auth.php';
require_once __DIR__ . '/../../../../App/Helpers/SessionHelper.php';
SessionHelper::requireAdminLogin();
SessionHelper::preventCache();

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar" role="navigation" aria-label="Admin navigation">
  <div style="height:3px;background:linear-gradient(135deg,#b8934a 0%,#d4a84b 50%,#c9a96e 100%);flex-shrink:0;"></div>

  <div style="display:flex;flex-direction:column;height:100%;padding:28px 0 24px;">

    <!-- Brand -->
    <div style="font-family:'Playfair Display',Georgia,serif;font-size:13px;letter-spacing:6px;text-transform:uppercase;color:#b8934a;font-weight:700;text-align:center;padding:0 24px 24px;border-bottom:1px solid #2a2218;margin-bottom:12px;">
      Drip N' Style
    </div>

    <!-- Nav links -->
    <ul class="nav flex-column" style="flex:1;gap:0;padding:0 0 16px;">
      <li>
        <a href="../../Public/admin/dashboard.php"
           class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
          🏠&ensp;Dashboard
        </a>
      </li>
      <li>
        <a href="../../Public/admin/products.php"
           class="nav-link <?= $currentPage === 'products.php' ? 'active' : '' ?>">
          🛍️&ensp;Products
        </a>
      </li>
      <li>
        <a href="../../Public/admin/orders.php"
           class="nav-link <?= ($currentPage === 'orders.php' || $currentPage === 'view_order.php') ? 'active' : '' ?>">
          📦&ensp;Orders
        </a>
      </li>
      <li>
        <a href="../../Public/admin/payments.php"
           class="nav-link <?= $currentPage === 'payments.php' ? 'active' : '' ?>">
          💳&ensp;Payments
        </a>
      </li>
      <li>
        <a href="../../Public/admin/helpdesk.php"
           class="nav-link <?= $currentPage === 'helpdesk.php' ? 'active' : '' ?>">
          🛡️&ensp;Help Desk
        </a>
      </li>
    </ul>

    <!-- Logout -->
    <div style="padding:16px 24px 0;border-top:1px solid #2a2218;">
      <a href="/DRIP-N-STYLE/App/Controllers/AuthController.php?action=logout"
         class="btn-logout">
        Logout
      </a>
    </div>

  </div>
</nav>
