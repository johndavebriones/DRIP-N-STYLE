<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['PHP_SELF']);

if (isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    require_once __DIR__ . '/../../App/config/database_connect.php';
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $_SESSION['user_name'] = $row['name'];
        $stmt->close();
    }
}

$paths = [
    'index'    => ['home' => './index.php',    'shop' => '../Public/shop/shop.php', 'cart' => '../Public/shop/cart.php', 'profile' => '../Public/customer/profile.php', 'logout' => '../App/Controllers/AuthController.php?action=logout', 'login' => '../Public/LoginPage.php'],
    'shop'     => ['home' => '../index.php',   'shop' => 'shop.php',               'cart' => 'cart.php',                'profile' => '../customer/profile.php',          'logout' => '../../App/Controllers/AuthController.php?action=logout',  'login' => '../../Public/LoginPage.php'],
    'cart'     => ['home' => '../index.php',   'shop' => 'shop.php',               'cart' => 'cart.php',                'profile' => '../customer/profile.php',          'logout' => '../../App/Controllers/AuthController.php?action=logout',  'login' => '../../Public/LoginPage.php'],
    'profile'  => ['home' => '../index.php',   'shop' => '../shop/shop.php',        'cart' => '../shop/cart.php',        'profile' => 'profile.php',                      'logout' => '../../App/Controllers/AuthController.php?action=logout'],
    'checkout' => ['home' => '#',             'shop' => '#',                       'cart' => '#',                       'profile' => '#',                                'logout' => '#',                                                      'login' => '../../Public/LoginPage.php'],
    'success'  => ['home' => '../index.php',   'shop' => 'shop.php',               'cart' => 'cart.php',                'profile' => '../customer/profile.php',          'logout' => '../../App/Controllers/AuthController.php?action=logout',  'login' => '../../Public/LoginPage.php'],
];

$pageKey = match(true) {
    in_array($currentPage, ['index.php'])    => 'index',
    in_array($currentPage, ['shop.php'])     => 'shop',
    in_array($currentPage, ['cart.php'])     => 'cart',
    in_array($currentPage, ['profile.php'])  => 'profile',
    in_array($currentPage, ['checkout.php']) => 'checkout',
    in_array($currentPage, ['success.php'])  => 'success',
    default                                  => 'index',
};

$base            = $paths[$pageKey];
$isCheckoutPage  = ($currentPage === 'checkout.php');
$isIndexPage     = ($currentPage === 'index.php');
$isLoggedIn      = isset($_SESSION['user_id']);
$userName        = $isLoggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
$userInitial     = $isLoggedIn ? strtoupper(mb_substr($_SESSION['user_name'], 0, 1)) : '';
?>

<div class="dns-gold-bar"></div>

<nav class="dns-nav">

  <!-- Logo -->
  <?php if ($isCheckoutPage): ?>
    <span class="dns-nav-logo dns-nav-logo--disabled">Drip N' Style</span>
  <?php else: ?>
    <a href="<?= $base['home'] ?>" class="dns-nav-logo">Drip N' Style</a>
  <?php endif; ?>

  <!-- Desktop links -->
  <div class="dns-nav-links" id="dnsNavLinks">

    <?php if ($isIndexPage): ?>
      <a href="index.php#about"   class="dns-nav-link">About</a>
      <a href="index.php#contact" class="dns-nav-link">Contact</a>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>

      <?php if ($isCheckoutPage): ?>
        <span class="dns-nav-link dns-nav-link--disabled" title="Please complete your order first">Shop</span>
        <span class="dns-nav-link dns-nav-link--disabled" title="Please complete your order first">Cart</span>
        <span class="dns-nav-link dns-nav-link--disabled" title="Please complete your order first"><?= $userName ?></span>

      <?php else: ?>
        <a href="<?= $base['shop'] ?>" class="dns-nav-link <?= $currentPage === 'shop.php' ? 'dns-nav-link--active' : '' ?>">Shop</a>

        <?php if ($pageKey !== 'index'): ?>
          <div class="dns-nav-cart-wrap">
            <a href="<?= $base['cart'] ?>" class="dns-nav-link <?= $currentPage === 'cart.php' ? 'dns-nav-link--active' : '' ?>">Cart</a>
            <?php
              // Cart count badge — adjust the session/DB key to match your cart logic
              $cartCount = $_SESSION['cart_count'] ?? 0;
              if ($cartCount > 0): ?>
              <span class="dns-cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- User dropdown -->
        <div class="dns-dropdown" id="dnsUserDropdown">
          <button class="dns-user-btn" onclick="dnsDdToggle()" aria-haspopup="true" aria-expanded="false">
            <span class="dns-user-avatar"><?= $userInitial ?></span>
            <?= $userName ?>
            <span class="dns-user-caret"></span>
          </button>
          <div class="dns-dropdown-menu" role="menu">
            <a href="<?= $base['profile'] ?>" class="dns-dropdown-item">Profile</a>
            <a href="<?= $base['logout'] ?>"  class="dns-dropdown-item dns-dropdown-item--danger">Logout</a>
          </div>
        </div>

      <?php endif; ?>

    <?php else: ?>
      <a href="<?= $base['shop'] ?>" class="dns-nav-link">Shop</a>
      <a href="<?= $base['login'] ?>" class="dns-nav-btn">Login</a>
    <?php endif; ?>

  </div>

  <!-- Hamburger (mobile) -->
  <button class="dns-nav-toggle" onclick="dnsMenuToggle()" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>

</nav>

<style>
  .dns-gold-bar { height: 3px; background: linear-gradient(135deg, #b8934a 0%, #d4a84b 50%, #c9a96e 100%); }

  .dns-nav {
    background: #faf8f5;
    border-bottom: 1px solid #e8e0d8;
    padding: 0 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .dns-nav-logo {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 5px;
    color: #b8934a;
    text-transform: uppercase;
    text-decoration: none;
    white-space: nowrap;
  }

  .dns-nav-logo--disabled {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 5px;
    color: #b8934a;
    text-transform: uppercase;
    opacity: 0.45;
    cursor: not-allowed;
    white-space: nowrap;
  }

  .dns-nav-links {
    display: flex;
    align-items: center;
    gap: 28px;
  }

  .dns-nav-link {
    font-family: 'DM Sans', Arial, sans-serif;
    font-size: 12px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #9a8a7c;
    text-decoration: none;
    font-weight: 500;
    transition: color .2s;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    white-space: nowrap;
  }

  .dns-nav-link:hover              { color: #b8934a; }
  .dns-nav-link--active            { color: #b8934a; }
  .dns-nav-link--disabled          { color: #b0a090; cursor: not-allowed; opacity: 0.6; }
  .dns-nav-link--disabled:hover    { color: #b0a090; }

  .dns-nav-btn {
    padding: 9px 24px;
    background: linear-gradient(135deg, #b8934a, #d4a84b);
    border: none;
    border-radius: 2px;
    font-family: 'DM Sans', Arial, sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    transition: opacity .2s;
    white-space: nowrap;
  }

  .dns-nav-btn:hover { opacity: .88; }

  /* Cart badge */
  .dns-nav-cart-wrap { position: relative; display: flex; align-items: center; }
  .dns-cart-badge {
    position: absolute;
    top: -7px; right: -10px;
    background: #b8934a;
    color: #fff;
    font-size: 9px;
    font-weight: 700;
    width: 16px; height: 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Sans', Arial, sans-serif;
    line-height: 1;
  }

  /* User dropdown */
  .dns-dropdown { position: relative; }

  .dns-user-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'DM Sans', Arial, sans-serif;
    font-size: 12px;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #b8934a;
    font-weight: 500;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: opacity .2s;
    white-space: nowrap;
  }

  .dns-user-btn:hover { opacity: .8; }

  .dns-user-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #b8934a, #d4a84b);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; color: #fff;
    flex-shrink: 0;
    font-family: 'DM Sans', Arial, sans-serif;
  }

  .dns-user-caret {
    display: inline-block;
    width: 8px; height: 8px;
    border-right: 1.5px solid #b8934a;
    border-bottom: 1.5px solid #b8934a;
    transform: rotate(45deg);
    margin-top: -3px;
    transition: transform .2s;
    flex-shrink: 0;
  }

  .dns-dropdown.open .dns-user-caret { transform: rotate(-135deg); margin-top: 3px; }

  .dns-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 14px);
    right: 0;
    background: #faf8f5;
    border: 1px solid #e8e0d8;
    border-radius: 3px;
    min-width: 160px;
    overflow: hidden;
    z-index: 300;
    box-shadow: 0 4px 20px rgba(45,37,32,0.08);
  }

  .dns-dropdown-menu::before {
    content: '';
    display: block;
    height: 3px;
    background: linear-gradient(135deg, #b8934a, #d4a84b);
  }

  .dns-dropdown.open .dns-dropdown-menu { display: block; }

  .dns-dropdown-item {
    display: block;
    padding: 11px 18px;
    font-family: 'DM Sans', Arial, sans-serif;
    font-size: 12px;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #9a8a7c;
    text-decoration: none;
    font-weight: 500;
    transition: background .15s, color .15s;
    border-bottom: 1px solid #ede8e2;
    white-space: nowrap;
  }

  .dns-dropdown-item:last-child { border-bottom: none; }
  .dns-dropdown-item:hover { background: #f5f0eb; color: #b8934a; }
  .dns-dropdown-item--danger { color: #b04030; }
  .dns-dropdown-item--danger:hover { background: #fdf2ee; color: #c04030; }

  /* Hamburger */
  .dns-nav-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
  }

  .dns-nav-toggle span {
    display: block;
    width: 22px; height: 1.5px;
    background: #9a8a7c;
    transition: all .2s;
  }

  /* Mobile nav */
  @media (max-width: 700px) {
    .dns-nav { padding: 0 20px; }
    .dns-nav-toggle { display: flex; }

    .dns-nav-links {
      display: none;
      position: absolute;
      top: 64px; left: 0; right: 0;
      background: #faf8f5;
      border-bottom: 1px solid #e8e0d8;
      flex-direction: column;
      align-items: flex-start;
      gap: 0;
      padding: 8px 0 16px;
      z-index: 99;
    }

    .dns-nav-links.open { display: flex; }

    .dns-nav-link, .dns-nav-btn, .dns-user-btn {
      padding: 12px 24px;
      width: 100%;
    }

    .dns-nav-btn { border-radius: 0; }

    .dns-dropdown { width: 100%; }
    .dns-dropdown-menu { position: static; box-shadow: none; border-left: 2px solid #c9a96e; border-radius: 0; margin-left: 24px; min-width: unset; }
  }
</style>

<script>
  function dnsDdToggle() {
    const dd = document.getElementById('dnsUserDropdown');
    dd.classList.toggle('open');
  }

  function dnsMenuToggle() {
    document.getElementById('dnsNavLinks').classList.toggle('open');
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    const dd = document.getElementById('dnsUserDropdown');
    if (dd && !dd.contains(e.target)) dd.classList.remove('open');
  });

  <?php if ($isCheckoutPage): ?>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dns-nav-link--disabled').forEach(el => {
      el.addEventListener('click', function(e) {
        e.preventDefault();
        alert('Please complete or cancel your order first.');
      });
    });
  });
  <?php endif; ?>
</script>