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

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drip N' Style | Home</title>

  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
      <div class="row g-4">
        <!-- Product cards -->
        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="assets/images/565706852_1214219220724446_7902029893493702505_n.jpg" class="card-img-top" alt="Denim Shorts">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Denim Shorts</h5>
              <p class="card-text text-muted">₱600</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="assets/images/564735523_1214149110731457_2363958690448676242_n.jpg" class="card-img-top" alt="Y2K Tops">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Y2K Tops</h5>
              <p class="card-text text-muted">₱450</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="assets/images/565639940_1214149134064788_3962912159579614764_n.jpg" class="card-img-top" alt="GAP Basic Tops">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">GAP Basic Tops</h5>
              <p class="card-text text-muted">₱850</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>
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
            high-quality apparel for all styles. Whether you're into casual, streetwear, or classy fits—we’ve got you covered.
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
        Got questions, concerns, or inquiries? We’re here to help anytime!
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/scroll-up.js"></script>
</body>
</html>