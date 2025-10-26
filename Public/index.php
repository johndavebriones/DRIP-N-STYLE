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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold text-warning" href="#">Drip N' Style</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto text-uppercase fw-semibold">
        <li class="nav-item"><a class="nav-link active text-warning" href="#">Home</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">About</a></li>
        <li class="nav-item"><a class="nav-link text-light" href="#">Contact</a></li>
        <li class="nav-item"><a href="LoginPage.php" class="btn btn-warning text-black ms-3 fw-semibold w-100">Login</a></li>
      </ul>
    </div>
  </div>
</nav>


  <!-- Hero Section -->
  <section class="hero d-flex align-items-center justify-content-center">
    <div class="hero-content text-center text-white">
      <h1 class="display-4 fw-bold hero-title text-warning">Discover Your Style</h1>
      <p class="lead text-light">— Grab What You Desire 💫</p>
      <a href="shop/shop.php" class="btn btn-warning btn-lg fw-semibold mt-3">Shop Now</a>
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
            <img src="assets/images/565706852_1214219220724446_7902029893493702505_n.jpg" class="card-img-top" alt="Product 1">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Denim Shorts</h5>
              <p class="card-text text-muted">₱600</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>

        <!-- Repeat product 2–6 -->
        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="assets/images/564735523_1214149110731457_2363958690448676242_n.jpg" class="card-img-top" alt="Product 2">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Y2K Tops</h5>
              <p class="card-text text-muted">₱450</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="assets/images/565639940_1214149134064788_3962912159579614764_n.jpg" class="card-img-top" alt="Product 3">
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
  <?php include 'Partials/footer.php'?>

  <!-- Scroll to Top Button -->
  <button id="scrollTop">↑</button>

  <script src="assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
  <script src="assets/js/scroll-up.js"></script>
</body>
</html>