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

// Detect current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drip N' Style | Home</title>
  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>
  <!-- Navbar -->
   <?php include 'Partials/navbar.php' ?>

  <!-- Hero Section -->
  <section class="hero d-flex align-items-center justify-content-center">
    <div class="hero-content text-center text-white">
      <h1 class="display-4 fw-bold hero-title text-warning">Discover Your Style</h1>
      <p class="lead text-light">— Grab What You Desire 💫</p>
      <a href="../Public/shop/shop.php" class="btn btn-warning btn-lg fw-semibold mt-3">Shop Now</a>
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

<!-- Footer -->
<?php include 'Partials/footer.php' ?>

<!-- Scroll to Top Button -->
<button id="scrollTop">↑</button>
<script src="assets/js/scroll-up.js"></script>
</body>
</html>
