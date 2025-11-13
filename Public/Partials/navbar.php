<?php
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

$currentPage = basename($_SERVER['PHP_SELF']);

// Load user name if logged in
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    require_once __DIR__ . '/../../App/config/database_connect.php';
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $_SESSION['user_name'] = $row['name'];
        }
        $stmt->close();
    }
}

// Determine brand link
if (in_array($currentPage, ['shop.php', 'cart.php', 'checkout.php'])) {
    $brandLink = '../index.php';
} elseif ($currentPage === 'profile.php') {
    $brandLink = '../index.php';
} elseif ($currentPage === 'index.php') {
    $brandLink = '../Public/index.php';
} else {
    $brandLink = '../Public/index.php';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand text-warning fw-bold" href="<?= $brandLink ?>">Drip N' Style</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">

        <?php if (isset($_SESSION['user_id'])): ?>

          <?php if ($currentPage === 'checkout.php'): ?>
            <!-- Minimal navbar for checkout: only dropdown -->
            <li class="nav-item dropdown ms-3">
              <a class="nav-link dropdown-toggle text-warning" href="#" id="userDropdown" role="button"
                 data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['user_name']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="../customer/profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item text-danger" href="../../App/Controllers/AuthController.php?action=logout">
                      Logout
                    </a>
                  </li>
              </ul>
            </li>

          <?php else: ?>
            <!-- Full navbar for other pages -->
            <?php if ($currentPage === 'index.php'): ?>
              <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'shop.php') ? 'active' : '' ?>" href="../Public/shop/shop.php">Shop</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>" href="about.php">About</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>" href="contact.php">Contact</a>
              </li>
            <?php else: ?>
              <?php
                // Applies to cart.php, shop.php, etc.
                $shopLink = (in_array($currentPage, ['cart.php', 'shop.php', 'checkout.php'])) ? '../shop/shop.php' : '../Public/shop/shop.php';
              ?>
              <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'shop.php') ? 'active' : '' ?>" href="<?= $shopLink ?>">Shop</a>
              </li>
              <li class="nav-item">
                <a class="nav-link <?= ($currentPage === 'cart.php') ? 'active' : '' ?>" href="../shop/cart.php">Cart</a>
              </li>
            <?php endif; ?>

            <li class="nav-item dropdown ms-3">
              <a class="nav-link dropdown-toggle text-warning" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['user_name']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="../customer/profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <?php
                    $logoutLink = (in_array($currentPage, ['cart.php', 'shop.php', 'checkout.php', 'success.php', 'profile.php'])) ? '../../App/Controllers/AuthController.php?action=logout' : '../App/Controllers/AuthController.php?action=logout';
                  ?>
                  <a class="dropdown-item text-danger" href="<?= $logoutLink ?>">Logout</a>
                </li>
              </ul>
            </li>
          <?php endif; ?>

        <?php else: ?>
          <!-- For guests -->
          <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'index.php' || $currentPage === 'shop.php') ? 'active' : '' ?>"
               href="<?= ($currentPage === 'shop.php') ? '../index.php' : '../Public/shop/shop.php' ?>">
               <?= ($currentPage === 'shop.php') ? 'Home' : 'Shop' ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'about.php') ? 'active' : '' ?>" href="about.php">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($currentPage === 'contact.php') ? 'active' : '' ?>" href="contact.php">Contact</a>
          </li>
          <li class="nav-item ms-3">
            <a class="btn btn-warning text-black fw-semibold <?= ($currentPage === 'LoginPage.php' || $currentPage === 'shop.php') ? 'active' : '' ?>"
               href="<?= ($currentPage === 'shop.php') ? '../LoginPage.php' : '../Public/LoginPage.php' ?>"
               style="color: black; background-color: #ffc107; border-color: #ffc107;">
               Login
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
