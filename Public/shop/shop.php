<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

$shop        = new ShopController();
$search      = $_GET['search'] ?? '';
$category    = $_GET['category'] ?? '';
$sort        = $_GET['sort'] ?? 'newest';
$brandFilter = $_GET['brand'] ?? '';

$categories = $shop->getCategories();
$products   = $shop->getProducts($search, $category, $sort);

$groupedProducts = [];
foreach ($products as $p) {
    $baseName = trim($p['name']);
    if (!isset($groupedProducts[$baseName])) {
        $groupedProducts[$baseName] = [
            'name'          => $baseName,
            'category_name' => $p['category_name'],
            'image'         => $p['image'],
            'date_added'    => $p['date_added'],
            'total_stock'   => 0,
            'variations'    => []
        ];
    }
    $groupedProducts[$baseName]['variations'][] = [
        'product_id'  => $p['product_id'],
        'size'        => $p['size'] ?? 'One Size',
        'color'       => $p['color'] ?? 'Default',
        'stock'       => $p['stock'] ?? 0,
        'price'       => $p['price'],
        'description' => $p['description'] ?? 'No description'
    ];
    $groupedProducts[$baseName]['total_stock'] += ($p['stock'] ?? 0);
}

$brands = [];
foreach ($groupedProducts as $p) {
    $brand = explode(' ', trim($p['name']))[0];
    $brands[$brand] = $brand;
}
ksort($brands);

if ($brandFilter) {
    $groupedProducts = array_filter($groupedProducts, fn($p) => explode(' ', trim($p['name']))[0] === $brandFilter);
}

if (!empty($_SESSION['order_canceled'])) {
    echo "<script>alert('Your order has been canceled.');</script>";
    unset($_SESSION['order_canceled']);
    unset($_SESSION['checkout_blocked']);
}

$productsWithStock = array_filter($groupedProducts, fn($p) => $p['total_stock'] > 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop | Drip N' Style</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="assets/css/shop.css?v=6"/>
</head>
<body>

<div class="gold-bar"></div>

<!-- Navbar -->
<?php include '../partials/navbar.php'; ?>

<!-- Hero -->
<section class="shop-hero">
  <div>
    <p class="shop-hero-eyebrow">Drip N' Style Collection</p>
    <h1 class="shop-hero-title">Shop Our <span>Collection</span></h1>
    <p class="shop-hero-sub">
      <?php if (isset($_SESSION['user_name'])): ?>
        Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!
      <?php else: ?>
        Find your next outfit — simple, stylish, and bold.
      <?php endif; ?>
    </p>
  </div>
</section>

<!-- Brands -->
<div class="brands-bar">
  <div class="brands-inner">
    <span class="brands-label">Filter by Brand</span>
    <?php foreach ($brands as $b): ?>
      <a href="shop.php?brand=<?= urlencode($b) ?>"
         class="brand-chip <?= $brandFilter === $b ? 'active' : '' ?>">
        <?= htmlspecialchars($b) ?>
      </a>
    <?php endforeach; ?>
    <?php if ($brandFilter): ?>
      <a href="shop.php" class="brand-chip clear">Clear Filter ✕</a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<form method="GET" action="shop.php">
  <?php if ($brandFilter): ?>
    <input type="hidden" name="brand" value="<?= htmlspecialchars($brandFilter) ?>">
  <?php endif; ?>
  <div class="filters-wrap">
    <input class="filter-input filter-search" type="text" name="search"
      placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
    <select class="filter-input" name="category">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['category_id'] ?>" <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['category_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select class="filter-input" name="sort">
      <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
      <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
    </select>
    <button type="submit" class="filter-btn">Search</button>
    <span class="results-count">Showing <?= count($productsWithStock) ?> products</span>
  </div>
</form>

<!-- Product Grid -->
<section class="shop-grid-section">
  <div class="shop-grid" id="productGrid"
       data-loggedin="<?= isset($_SESSION['user_id']) ? 1 : 0 ?>">
    <?php if (count($productsWithStock) > 0): ?>
      <?php foreach ($productsWithStock as $product):
        $isNew     = strtotime($product['date_added']) > strtotime('-7 days');
        $isLow     = $product['total_stock'] < 20;
        $isUpdated = !$isNew && !$isLow && strtotime($product['date_added']) > strtotime('-30 days');
      ?>
      <div class="product-card product-clickable"
           data-name="<?= htmlspecialchars($product['name']) ?>"
           data-category="<?= htmlspecialchars($product['category_name']) ?>"
           data-variations='<?= json_encode($product['variations']) ?>'
           data-image="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>">

        <div class="product-img-wrap">
          <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>"
               alt="<?= htmlspecialchars($product['name']) ?>">
          <?php if ($isNew): ?><span class="product-badge badge-new">New</span><?php endif; ?>
          <?php if ($isLow): ?><span class="product-badge badge-low">Low Stock</span><?php endif; ?>
          <?php if ($isUpdated): ?><span class="product-badge badge-updated">Updated</span><?php endif; ?>
          <div class="quick-view">Quick View</div>
        </div>

        <div class="product-body">
          <p class="product-cat"><?= htmlspecialchars($product['category_name']) ?></p>
          <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
          <p class="product-stock">Stock: <?= $product['total_stock'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty-state">
        <div class="empty-icon">◦</div>
        <p class="empty-title">No products found</p>
        <p class="empty-sub">Try adjusting your search or filters.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Footer -->
<footer class="dns-footer">
  <div class="footer-top">
    <div>
      <p class="footer-logo">Drip N' Style</p>
      <p class="footer-tagline">Wear Your Confidence</p>
    </div>
    <div class="footer-links">
      <a href="../../Public/index.php">Home</a>
      <a href="../../Public/index.php#about">About</a>
      <a href="shop.php">Shop</a>
      <a href="../../Public/index.php#contact">Contact</a>
      <a href="../../Public/LoginPage.php">Login</a>
    </div>
  </div>
  <p class="footer-bottom">&copy; <?= date('Y') ?> Drip N' Style. All rights reserved.</p>
</footer>
<div class="gold-bar"></div>

<button id="scrollTop">↑</button>

<!-- Product Detail Modal -->
<div class="modal-overlay" id="productModalOverlay" onclick="closeOnBg(event)"
     data-loggedin="<?= isset($_SESSION['user_id']) ? 1 : 0 ?>">
  <div class="modal-box">
    <div class="gold-bar"></div>
    <div class="modal-header-bar">
      <span class="modal-product-title" id="productDetailLabel">Product Details</span>
      <button class="modal-close" onclick="closeModal()">&#x2715;</button>
    </div>
    <div class="modal-body-grid">
      <div class="modal-img-col">
        <img id="detailImage" src="" alt="">
      </div>
      <div class="modal-info-col">
        <div>
          <p class="modal-cat" id="detailCategory"></p>
          <h4 id="detailName" style="font-family:'Playfair Display',Georgia,serif;font-size:22px;font-weight:400;color:var(--text-dark);margin-bottom:8px;"></h4>
          <p class="modal-price" id="detailPrice"></p>
          <p class="modal-desc" id="detailDescription"></p>
        </div>

        <div>
          <span class="selector-label">Select Size</span>
          <div class="selector-group" id="sizeSelector"></div>
        </div>

        <div>
          <span class="selector-label">Select Color</span>
          <div class="selector-group" id="colorSelector"></div>
        </div>

        <p class="modal-stock" id="detailStock"></p>

        <div class="qty-row" id="modalControls">
          <input type="number" id="detailQty" value="1" min="1" class="qty-input" style="display:none;">
          <button id="modalActionBtn" class="add-cart-btn">Add to Cart</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/shop.js?v=5"></script>
<script>
  const scrollBtn = document.getElementById('scrollTop');
  window.addEventListener('scroll', () => scrollBtn.classList.toggle('visible', window.scrollY > 300));
  scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  function closeModal() { document.getElementById('productModalOverlay').classList.remove('open'); }
  function closeOnBg(e) { if (e.target === document.getElementById('productModalOverlay')) closeModal(); }

  // Wire product cards to open the modal (your existing shop.js handles the data population)
  document.querySelectorAll('.product-clickable').forEach(card => {
    card.addEventListener('click', () => {
      document.getElementById('productModalOverlay').classList.add('open');
      // shop.js populates the modal fields using data attributes
    });
  });
</script>
</body>
</html>