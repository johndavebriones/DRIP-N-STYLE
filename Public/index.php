<?php
require_once __DIR__ . '/../App/Helpers/SessionHelper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

// Load user name if not already in session
if (isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    require_once __DIR__ . '/../App/Config/database_connect.php';
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

// Fetch featured products using ProductController
require_once __DIR__ . '/../App/Controllers/ProductController.php';
$productController = new ProductController();
$featuredProducts = $productController->getFeaturedProducts(6);

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drip N' Style | Home</title>

  <!-- Bootstrap CSS CDN -->
  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/home.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
</head>
<body>
  <!-- Navbar -->
  <?php include '../Public/partials/navbar.php'; ?>

  <!-- Hero Section -->
  <section class="hero d-flex align-items-center justify-content-center">
    <div class="hero-content text-center text-white">
      <h1 class="display-4 fw-bold hero-title text-warning">Discover Your Style</h1>
      <p class="lead text-light">— Grab What You Desire 💫</p>
      <a href="../Public/shop/shop.php" class="btn btn-warning btn-lg fw-semibold mt-3">Shop Now</a>
    </div>
  </section>

  <!-- Clothing Brands Section -->
  <section class="brands-section py-4">
    <div class="container text-center mb-3">
      <h2 class="fw-bold text-black">Brands We Have</h2>
    </div>
    <div class="brands-slider">
      <div class="brands-track">
        <img src="assets/images/brands/ck.png" alt="Calvin Klein">
        <img src="assets/images/brands/essentials-removebg-preview.png" alt="Essentials">
        <img src="assets/images/brands/uniqlo-removebg-preview.png" alt="Uniqlo">
        <img src="assets/images/brands/zara-removebg-preview.png" alt="Zara">
        <img src="assets/images/brands/gap-removebg-preview.png" alt="GAP">
        <img src="assets/images/brands/polo.png" alt="Polo">
        <img src="assets/images/brands/new era.png" alt="New Era">
        <img src="assets/images/brands/alo.jpg" alt="alo" style="height: 40px;">
        <img src="assets/images/brands/ck.png" alt="Calvin Klein">
        <img src="assets/images/brands/essentials-removebg-preview.png" alt="Essentials">
        <img src="assets/images/brands/uniqlo-removebg-preview.png" alt="Uniqlo">
        <img src="assets/images/brands/zara-removebg-preview.png" alt="Zara">
        <img src="assets/images/brands/gap-removebg-preview.png" alt="GAP">
        <img src="assets/images/brands/polo.png" alt="Polo">
        <img src="assets/images/brands/new era.png" alt="New Era">
        <img src="assets/images/brands/alo.jpg" alt="alo" style="height: 40px;">
      </div>
    </div>
  </section>

  <!-- Featured Products -->
  <section class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="mb-4 fw-bold text-black">Featured Products</h2>
      
      <?php if (empty($featuredProducts)): ?>
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-2"></i>
          No featured products available at the moment. Check back soon!
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($featuredProducts as $product): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card product-card border-0 shadow-sm h-100 position-relative">
                <!-- Featured Badge -->
                <div class="position-absolute top-0 end-0 m-2 z-index-1">
                  <span class="badge bg-warning text-dark">
                    <i class="bi bi-star-fill"></i> Featured
                  </span>
                </div>
                
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     style="height: 300px; object-fit: cover;">
                
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title fw-bold text-dark"><?php echo htmlspecialchars($product['name']); ?></h5>
                  <p class="text-muted small"><?php echo htmlspecialchars($product['category_name'] ?? 'Fashion'); ?></p>
                  <p class="card-text text-warning fw-bold fs-5">₱<?php echo number_format($product['price'], 2); ?></p>
                  
                  <?php if (!empty($product['description'])): ?>
                    <p class="card-text text-muted small flex-grow-1">
                      <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                    </p>
                  <?php endif; ?>
                  
                  <a href="../Public/shop/product_details.php?id=<?php echo $product['product_id']; ?>" 
                     class="btn btn-warning text-black fw-semibold mt-auto">
                     <i class="bi bi-eye me-1"></i> View Details
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <!-- View All Button -->
      <div class="mt-4">
        <a href="../Public/shop/shop.php" class="btn btn-outline-warning btn-lg fw-semibold">
          <i class="bi bi-shop me-2"></i> View All Products
        </a>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about-section py-5 bg-white">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <img src="assets/images/dripnstyleAbout.png" class="img-fluid rounded shadow" alt="About Drip N' Style">
        </div>
        <div class="col-md-6">
          <h2 class="fw-bold text-black mb-3">About Drip N' Style</h2>
          <p class="text-muted">
            Drip N' Style is your trusted online clothing store, offering a wide range of trendy,
            high-quality apparel for all styles. Whether you're into casual, streetwear, or classy fits—we've got you covered.
          </p>
          <p class="text-muted">
            Our mission is to bring premium fashion closer to you with affordable prices,
            a personalized in-store shopping experience, and friendly customer service.
          </p>
          <a href="../Public/shop/shop.php" class="btn btn-warning fw-semibold text-black mt-2">Shop Now</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="contact-section py-5 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold text-black mb-4">Contact Us</h2>
      <p class="text-muted mb-5">
        Got questions, concerns, or inquiries? We're here to help anytime!
      </p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="p-4 bg-white rounded shadow-sm">
            <h5 class="fw-semibold text-black">📍 Address</h5>
            <p class="text-muted mb-0">Damballelos, Street, Barangay 4, Balayan, Philippines, 4213</p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 bg-white rounded shadow-sm">
            <h5 class="fw-semibold text-black">📞 Phone</h5>
            <p class="text-muted mb-4">+63 965 327 9916</p>
          </div>
        </div>

        <div class="col-md-4">
          <div class="p-4 bg-white rounded shadow-sm">
            <h5 class="fw-semibold text-black">📧 Email</h5>
            <p class="text-muted mb-4">dripnstyle.shop@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Optional Contact Form -->
      <div class="row mt-5 justify-content-center">
        <div class="col-md-8">
          <form class="p-4 bg-white rounded shadow-sm">
            <div class="mb-3">
              <input type="text" class="form-control" placeholder="Your Name" required>
            </div>
            <div class="mb-3">
              <input type="email" class="form-control" placeholder="Your Email" required>
            </div>
            <div class="mb-3">
              <textarea class="form-control" rows="4" placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-warning fw-semibold text-black w-100">Send Message</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <?php include '../Public/partials/footer.php'; ?>

  <!-- Scroll to Top Button -->
  <button id="scrollTop">↑</button>

  <!-- Bootstrap JS Bundle CDN (includes Popper) -->
  <script src="assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/scroll-up.js"></script>
</body>
</html>