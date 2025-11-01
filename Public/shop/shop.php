<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../App/Helpers/SessionHelper.php';
require_once __DIR__ . '/../../App/Controllers/ShopController.php';

SessionHelper::preventCache();
$shop = new ShopController();
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

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
    <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<div id="page-container">
  <?php include '../partials/navbar.php'; ?>

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
              <div class="card shadow-sm h-100">
                <!-- Product Image -->
                <img src="../../Public/<?= htmlspecialchars($product['image'] ?: 'uploads/no-image.png') ?>" 
                    class="card-img-top" 
                    alt="<?= htmlspecialchars($product['name']) ?>" 
                    style="height: 200px; object-fit: cover;">

                <div class="card-body d-flex flex-column">
                  <!-- Product Info -->
                  <h6 class="card-title fw-bold"><?= htmlspecialchars($product['name']); ?></h6>
                  <p class="text-muted mb-1"><?= htmlspecialchars($product['category_name']); ?></p>
                  <p class="fw-bold text-warning mb-2">₱<?= number_format($product['price'], 2); ?></p>
                  <p class="text-muted mb-2">Stock: <?= $product['stock'] ?? 10 ?></p>

                  <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- AJAX Add Section -->
                    <div class="d-flex align-items-center mt-auto gap-2">
                      <input type="number" id="ajaxQty<?= $product['product_id'] ?>" 
                            value="1" min="1" max="<?= $product['stock'] ?? 10 ?>" 
                            class="form-control text-center" style="width: 70px;">
                      <button class="btn btn-warning fw-bold ajax-add-btn flex-shrink-0" 
                              data-id="<?= $product['product_id'] ?>" 
                              data-price="<?= $product['price'] ?>">
                        Add
                      </button>
                    </div>
                    <?php else: ?>
                      <a href="../LoginPage.php" class="btn btn-dark w-100">Log in to Order</a>
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

<script>
document.querySelectorAll('.ajax-add-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const productId = btn.dataset.id;
    const price = btn.dataset.price;
    const qty = document.getElementById('ajaxQty'+productId).value || 1;

    fetch('../../App/Controllers/CartController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'add', product_id: productId, quantity: qty, price: price })
    })
    .then(res => res.text())
    .then(data => {
      // SweetAlert2 toast
      Swal.fire({
        toast: true,
        position: 'bottom-right',
        icon: 'success',
        title: 'Added to cart!',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true
      });
    })
    .catch(err => console.error(err));
  });
});
</script>

</body>
</html>
