<?php
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

$shop = new ShopController();
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$categories = $shop->getCategories();
$products = $shop->getProducts($search, $category, $sort);

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

    <!-- Filters -->
    <section class="shop-filters container my-4">
      <?php include '../Partials/shopfilters.php'; ?>
    </section>

    <!-- Product Grid -->
    <section class="shop-products py-4">
      <div class="container">
        <div class="row g-4">
          <?php 
          $productsWithStock = array_filter($products, fn($p) => ($p['stock'] ?? 0) > 0);
          if (count($productsWithStock) > 0): 
            foreach ($productsWithStock as $product): ?>
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card h-100 product-clickable" data-id="<?= $product['product_id'] ?>">
                  <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>" 
                      alt="<?= htmlspecialchars($product['name']) ?>" 
                      class="card-img-top">
                  <div class="card-body d-flex flex-column">
                      <h6 class="card-title fw-bold"><?= htmlspecialchars($product['name']); ?></h6>
                      <p class="text-muted small mb-1"><?= htmlspecialchars($product['category_name']); ?></p>
                      <p class="price-tag mb-2 text-warning fw-bold">₱<?= number_format($product['price'], 2); ?></p>
                      <p class="text-muted small mb-3 text-truncate" title="<?= htmlspecialchars($product['description'] ?? '') ?>">
                        <?= htmlspecialchars($product['description'] ?? '') ?>
                      </p>
                      <p class="text-muted small mb-3">Stock: <?= $product['stock'] ?></p>
                  </div>
                </div>
              </div>
          <?php 
            endforeach; 
          else: ?>
            <div class="col-12 text-center py-5">
              <i class="bi bi-box-seam text-secondary" style="font-size: 3rem;"></i>
              <p class="mt-3 text-muted">No products in stock.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include '../Partials/footer.php'; ?>
</div>

<!-- Product Detail Modal -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-labelledby="productDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg border-0">
      <div class="modal-header border-0 bg-warning">
        <h5 class="modal-title fw-bold" id="productDetailLabel">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">
          <div class="col-md-6 text-center">
            <div class="modal-image-wrapper position-relative overflow-hidden rounded-4 shadow-sm">
              <img id="detailImage" src="" alt="" class="img-fluid transition" style="max-height: 350px;">
            </div>
          </div>
          <div class="col-md-6 d-flex flex-column justify-content-between">
            <div>
              <h4 id="detailName" class="fw-bold mb-2"></h4>
              <p class="text-muted small mb-2" id="detailCategory"></p>
              <h5 class="text-warning fw-bold mb-3" id="detailPrice"></h5>
              <p class="text-muted small mb-2" id="detailStock"></p>
              <div id="detailDescription"></div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="d-flex align-items-center gap-2 mt-3">
              <input type="number" id="detailQty" value="1" min="1" class="form-control text-center" style="width: 70px; border-radius: 0.5rem;">
              <button class="btn btn-warning flex-grow-1 fw-bold rounded-pill" id="detailAddBtn">
                <i class="bi bi-cart-plus"></i> Add to Cart
              </button>
            </div>
            <?php else: ?>
              <a href="../LoginPage.php" class="btn btn-dark w-100 mt-3 rounded-pill">
                <i class="bi bi-person-circle"></i> Log in to Order
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/shop.js"></script>
</body>
</html>
