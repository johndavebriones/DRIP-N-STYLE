<?php
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

$shop = new ShopController();
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$brandFilter = $_GET['brand'] ?? '';

$categories = $shop->getCategories();
$products = $shop->getProducts($search, $category, $sort);

// Group products by name and aggregate sizes/stocks
$groupedProducts = [];
foreach ($products as $p) {
    $baseName = trim($p['name']);
    
    if (!isset($groupedProducts[$baseName])) {
        $groupedProducts[$baseName] = [
            'product_id' => $p['product_id'],
            'name' => $baseName,
            'category_name' => $p['category_name'],
            'price' => $p['price'],
            'image' => $p['image'],
            'description' => $p['description'] ?? 'No description',
            'date_added' => $p['date_added'],
            'total_stock' => 0,
            'sizes' => []
        ];
    }
    
    // Add size variation
    $size = $p['size'] ?? 'One Size';
    $groupedProducts[$baseName]['sizes'][] = [
        'product_id' => $p['product_id'],
        'size' => $size,
        'stock' => $p['stock'] ?? 0,
        'description' => $p['description'] ?? 'No description'
    ];
    
    $groupedProducts[$baseName]['total_stock'] += ($p['stock'] ?? 0);
}

// Extract brands
$brands = [];
foreach ($groupedProducts as $p) {
    $brand = explode(' ', trim($p['name']))[0];
    $brands[$brand] = $brand;
}
ksort($brands);

// Filter by brand
if ($brandFilter) {
    $groupedProducts = array_filter($groupedProducts, fn($p) => explode(' ', trim($p['name']))[0] === $brandFilter);
}

if (!empty($_SESSION['order_canceled'])) {
    echo "<script>alert('Your order has been canceled.');</script>";
    unset($_SESSION['order_canceled']);      
    unset($_SESSION['checkout_blocked']);    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop | Drip N' Style</title>

  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="assets/css/shop.css">
  <link rel="stylesheet" href="../assets/css/navbar.css">
  <link rel="stylesheet" href="../assets/css/footer.css">
  
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div id="page-container">
  <?php include '../partials/navbar.php'; ?>

  <main>
    <!-- Hero Section -->
    <section class="shop-header text-center py-5 text-warning">
      <div class="container">
        <h1 class="fw-bold">Shop Our Collection</h1>
        <?php if(isset($_SESSION['user_name'])): ?>
          <p class="mb-0 text-light">Welcome back, <?= htmlspecialchars($_SESSION['user_name']); ?>!</p>
        <?php else: ?>
          <p class="mb-0 text-light">Find your next outfit — simple, stylish, and bold.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Brands -->
    <section class="shop-brands py-4 bg-light">
      <div class="container text-center">
        <h5 class="fw-bold text-dark mb-3">Shop by Brand</h5>
        <?php foreach ($brands as $b): ?>
          <a href="shop.php?brand=<?= urlencode($b) ?>" class="btn btn-outline-warning brand-btn">
            <?= htmlspecialchars($b) ?>
          </a>
        <?php endforeach; ?>
        <?php if($brandFilter): ?>
          <a href="shop.php" class="btn btn-secondary brand-btn">Clear Filter</a>
        <?php endif; ?>
      </div>
    </section>

    <!-- Filters -->
    <section class="shop-filters container my-4">
      <?php include '../Partials/shopfilters.php'; ?>
    </section>

    <!-- Product Grid -->
    <section class="shop-products py-4">
      <div class="container">
        <div class="row g-4">
          <?php 
          $productsWithStock = array_filter($groupedProducts, fn($p) => $p['total_stock'] > 0);
          if (count($productsWithStock) > 0): 
            foreach ($productsWithStock as $product): 
              $isNew = strtotime($product['date_added']) > strtotime('-7 days');
              $isLow = $product['total_stock'] < 20;
              $isUpdated = !$isNew && !$isLow && strtotime($product['date_added']) > strtotime('-30 days');
          ?>
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 product-clickable"
                    data-id="<?= $product['product_id'] ?>"
                    data-name="<?= htmlspecialchars($product['name']) ?>"
                    data-category="<?= htmlspecialchars($product['category_name']) ?>"
                    data-price="<?= $product['price'] ?>"
                    data-stock="<?= $product['total_stock'] ?>"
                    data-description="<?= htmlspecialchars($product['description']) ?>"
                    data-sizes='<?= json_encode($product['sizes']) ?>'
                    data-image="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>">

                  <?php if($isNew): ?>
                    <span class="badge badge-new position-absolute top-0 start-0 m-2">New</span>
                  <?php endif; ?>
                  <?php if($isLow): ?>
                    <span class="badge badge-low position-absolute top-0 end-0 m-2">Low Stock</span>
                  <?php endif; ?>
                  <?php if($isUpdated): ?>
                    <span class="badge badge-updated position-absolute top-0 end-0 m-2">Updated</span>
                  <?php endif; ?>

                  <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>" 
                      alt="<?= htmlspecialchars($product['name']) ?>" 
                      class="card-img-top">
                  <div class="quick-view">Quick View</div>

                  <div class="card-body d-flex flex-column">
                      <h6 class="card-title fw-bold"><?= htmlspecialchars($product['name']); ?></h6>
                      <p class="text-muted small mb-1"><?= htmlspecialchars($product['category_name']); ?></p>
                      <p class="price-tag mb-2 text-warning fw-bold">₱<?= number_format($product['price'], 2); ?></p>
                      <p class="text-muted small mb-3">Total Stock: <?= $product['total_stock'] ?></p>
                  </div>
                </div>
              </div>
          <?php endforeach; 
          else: ?>
            <div class="col-12 text-center py-5">
              <img src="../assets/images/empty-box.png" class="img-fluid" style="max-width:100px;" alt="No products">
              <p class="mt-3 text-muted">No products in this category yet.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include '../Partials/footer.php'; ?>
</div>

<!-- Product Detail Modal -->
<div class="modal fade" id="productDetailModal" data-loggedin="<?= isset($_SESSION['user_id']) ? 1 : 0 ?>" tabindex="-1" aria-labelledby="productDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-5 shadow-xl border-0">
      <div class="modal-header border-0 bg-gradient-warning text-dark py-3 px-4 rounded-top-5">
        <h5 class="modal-title fw-bold" id="productDetailLabel">Product Details</h5>
        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">
          <div class="col-md-6 text-center">
            <div class="overflow-hidden rounded-4 shadow-sm mb-3">
              <img id="detailImage" src="" alt="" class="img-fluid transition hover-scale" style="max-height: 350px; width: 100%;">
            </div>
          </div>
          <div class="col-md-6 d-flex flex-column justify-content-between">
            <div>
              <h4 id="detailName" class="fw-bold mb-2 text-dark"></h4>
              <p class="text-muted small mb-2" id="detailCategory"></p>
              
              <!-- Size Selector -->
              <div class="mb-3">
                <label class="form-label fw-bold small">Select Size:</label>
                <div id="sizeSelector" class="d-flex flex-wrap gap-2"></div>
              </div>
              
              <h5 class="text-warning fw-bold mb-3" id="detailPrice"></h5>
              <div id="detailDescription" class="text-secondary small mb-3"></div>
              <p class="text-muted small mb-2" id="detailStock"></p>
            </div>
            <div id="modalControls" class="mt-3 d-flex gap-2 align-items-center">
              <input type="number" id="detailQty" value="1" min="1" class="form-control text-center rounded-pill border-1 shadow-sm" style="width: 70px; display: none;">
              <button id="modalActionBtn" class="btn btn-warning w-50 fw-bold rounded-pill shadow-sm">
                  Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/shop.js?v=4"></script>
</body>
</html>