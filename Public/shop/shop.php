<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../App/Controllers/AuthController.php';
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

$auth = new AuthController();
$shop = new ShopController();

// Default values
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// ✅ Get data safely
$categories = $shop->getCategories();
$products = $shop->getProducts($search, $category, $sort);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop | Drip N' Style</title>
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body>
<div id="page-container">
  <?php include '../Partials/navbar.php'; ?>

  <main>
    <!-- Shop Header -->
    <section class="shop-header text-center py-5 bg-dark text-warning">
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
    <section class="shop-filters py-4">
      <div class="container">
        <?php include '../Partials/shopfilters.php'; ?>
      </div>
    </section>

    <!-- Products -->
    <section class="shop-products py-5">
      <div class="container">
        <div class="row g-4">
          <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card shadow-sm fade-in">
                  <img src="assets/images/<?= htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>">
                  <div class="card-body text-center">
                    <h5 class="card-title"><?= htmlspecialchars($product['name']); ?></h5>
                    <p class="text-muted mb-1"><?= htmlspecialchars($product['category_name']); ?></p>
                    <p class="fw-bold text-warning mb-2">₱<?= number_format($product['price'], 2); ?></p>

                    <?php if (isset($_SESSION['user_id'])): ?>
                      <a href="add_to_cart.php?id=<?= $product['product_id']; ?>" class="btn btn-dark w-100">Add to Cart</a>
                    <?php else: ?>
                      <a href="auth.php" class="btn btn-dark w-100">Log in to Order</a>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center text-muted">
              <p>No products found matching your filters.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  </main>

  <?php include '../Partials/footer.php'; ?>
</div>

<script src="assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>
