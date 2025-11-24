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

$paths = [
    'index' => [
        'shop' => '../Public/shop/shop.php',
        'cart' => '../Public/shop/cart.php',
        'profile' => '../Public/customer/profile.php',
        'logout' => '../App/Controllers/AuthController.php?action=logout',
        'login' => '../Public/LoginPage.php',
    ],
    'shop' => [
        'shop' => 'shop.php',
        'cart' => 'cart.php',
        'profile' => '../customer/profile.php',
        'logout' => '../../App/Controllers/AuthController.php?action=logout',
        'login' => '../../Public/LoginPage.php',
    ],
    'cart' => [
        'shop' => 'shop.php',
        'cart' => 'cart.php',
        'profile' => '../customer/profile.php',
        'logout' => '../../App/Controllers/AuthController.php?action=logout',
        'login' => '../../Public/LoginPage.php',
    ],
    'profile' => [
        'shop' => '../shop/shop.php',
        'cart' => '../shop/cart.php',
        'profile' => 'profile.php',
        'logout' => '../../App/Controllers/AuthController.php?action=logout',
    ],
    'checkout' => [
        'shop' => '#',  // Disabled
        'cart' => '#',  // Disabled
        'profile' => '#',  // Disabled
        'logout' => '#',  // Disabled
        'login' => '../../Public/LoginPage.php',
    ],
    'success' => [
        'shop' => 'shop.php',
        'cart' => 'cart.php',
        'profile' => '../customer/profile.php',
        'logout' => '../../App/Controllers/AuthController.php?action=logout',
        'login' => '../../Public/LoginPage.php',
    ],
];

// Determine current page key
$pageKey = in_array($currentPage, ['index.php']) ? 'index' :
           (in_array($currentPage, ['shop.php']) ? 'shop' :
           (in_array($currentPage, ['cart.php']) ? 'cart' :
           (in_array($currentPage, ['profile.php']) ? 'profile' :
           (in_array($currentPage, ['checkout.php']) ? 'checkout' :
           (in_array($currentPage, ['success.php']) ? 'success' : 'index')))));

$base = $paths[$pageKey];

// Check if navigation is restricted (on checkout page)
$isCheckoutPage = ($currentPage === 'checkout.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand text-warning fw-bold <?= $isCheckoutPage ? 'pe-none' : '' ?>" 
       href="<?= $isCheckoutPage ? '#' : (($currentPage === 'index.php') ? './index.php' : '../index.php') ?>"
       <?= $isCheckoutPage ? 'style="cursor: not-allowed;"' : '' ?>>
      Drip N' Style
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">

        <?php if ($currentPage === 'index.php'): ?>
          <li class="nav-item">
            <a class="nav-link" href="index.php#about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php#contact">Contact</a>
          </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Logged-in user links -->
          
          <?php if ($isCheckoutPage): ?>
            <!-- Checkout Page: Restricted Navigation -->
            <li class="nav-item">
              <span class="nav-link text-muted" style="cursor: not-allowed;" 
                    title="Please complete your order first">
                Shop
              </span>
            </li>
            <li class="nav-item">
              <span class="nav-link text-muted" style="cursor: not-allowed;" 
                    title="Please complete your order first">
                Cart
              </span>
            </li>
            <li class="nav-item dropdown ms-3">
              <span class="nav-link dropdown-toggle text-muted" style="cursor: not-allowed;" 
                    title="Please complete your order first">
                <?= htmlspecialchars($_SESSION['user_name']) ?>
              </span>
            </li>
            
          <?php else: ?>
            <!-- Normal Navigation -->
            <li class="nav-item">
              <a class="nav-link <?= ($currentPage === 'shop.php') ? 'active' : '' ?>" 
                 href="<?= $base['shop'] ?>">Shop</a>
            </li>
            <?php if ($pageKey !== 'index'): ?>
            <li class="nav-item">
              <a class="nav-link <?= ($currentPage === 'cart.php') ? 'active' : '' ?>" 
                 href="<?= $base['cart'] ?>">Cart</a>
            </li>
            <?php endif; ?>

            <li class="nav-item dropdown ms-3">
              <a class="nav-link dropdown-toggle text-warning" href="#" id="userDropdown" 
                 role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['user_name']) ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="<?= $base['profile'] ?>">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= $base['logout'] ?>">Logout</a></li>
              </ul>
            </li>
          <?php endif; ?>

        <?php else: ?>
          <!-- Guest links -->
          <li class="nav-item">
            <a class="nav-link" href="<?= $base['shop'] ?>">Shop</a>
          </li>
          <?php if ($pageKey !== 'index'): ?>
          <li class="nav-item ms-3">
            <a class="btn btn-warning text-black fw-semibold" href="<?= $base['login'] ?>">Login</a>
          </li>
          <?php else: ?>
          <li class="nav-item ms-3">
            <a class="btn btn-warning text-black fw-semibold" href="<?= $base['login'] ?>">Login</a>
          </li>
          <?php endif; ?>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<?php if ($isCheckoutPage): ?>
<!-- Additional JavaScript to prevent navigation on checkout page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Disable all navigation links on checkout page
    const restrictedLinks = document.querySelectorAll('.navbar a[href="#"], .navbar span[style*="cursor: not-allowed"]');
    restrictedLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Please complete or cancel your order first.');
        });
    });
    
    // Prevent dropdown from opening
    const dropdownToggle = document.querySelector('.navbar .dropdown-toggle.text-muted');
    if (dropdownToggle) {
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            alert('Please complete or cancel your order first.');
        });
    }
});
</script>
<?php endif; ?>