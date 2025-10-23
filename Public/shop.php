<?php
session_start();
require_once "../app/config/database_connect.php";

$db = new Database();
$conn = $db->connect();

// FILTER HANDLERS
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Base query
$query = "SELECT p.*, c.category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id
          WHERE 1";

// FILTERS
if (!empty($search)) {
    $query .= " AND p.name LIKE :search";
}
if (!empty($category)) {
    $query .= " AND c.category_id = :category";
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    default:
        $query .= " ORDER BY p.date_added DESC"; // newest first
}

// Prepare & execute
$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}
if (!empty($category)) {
    $stmt->bindValue(':category', $category);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// FETCH CATEGORIES
$catStmt = $conn->prepare("SELECT * FROM categories ORDER BY category_name ASC");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop | Drip N' Style</title>
  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/shop.css">
</head>
<body>
<div id="page-container">
  <?php include 'Partials/navbar.php'; ?>

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
        <form method="GET" class="row g-3 align-items-center justify-content-center">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
          </div>
          <div class="col-md-3">
            <select name="category" class="form-select">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id']; ?>" <?= $category == $cat['category_id'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($cat['category_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="sort" class="form-select">
              <option value="newest" <?= $sort == 'newest' ? 'selected' : ''; ?>>Newest</option>
              <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
          </div>
          <div class="col-md-2 text-center">
            <button class="btn btn-warning w-100">Filter</button>
          </div>
        </form>
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

  <?php include 'Partials/footer.php'; ?>
</div>

<script src="assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>
