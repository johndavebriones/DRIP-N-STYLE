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
        <li class="nav-item">
          <button class="btn btn-warning text-black ms-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#loginModal">
            Login
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>


  <!-- Hero Section -->
  <section class="hero d-flex align-items-center justify-content-center">
    <div class="hero-content text-center text-white">
      <h1 class="display-4 fw-bold hero-title text-warning">Discover Your Style</h1>
      <p class="lead text-light">— Grab What You Desire 💫</p>
      <a href="shop.php" class="btn btn-warning btn-lg fw-semibold mt-3">Shop Now</a>
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
              <h5 class="card-title fw-bold text-dark">Classic Denim Jacket</h5>
              <p class="card-text text-muted">₱600</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>

        <!-- Repeat product 2–6 -->
        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Product 2">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Summer Oversized Tee</h5>
              <p class="card-text text-muted">₱450</p>
              <button class="btn btn-warning text-black fw-semibold">View Details</button>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card product-card border-0 shadow-sm">
            <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=800&q=80" class="card-img-top" alt="Product 3">
            <div class="card-body">
              <h5 class="card-title fw-bold text-dark">Streetwear Hoodie</h5>
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

  <!-- UNIVERSAL LOGIN MODAL -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login to Drip N' Style</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="../app/controllers/AuthController.php?action=login">
          <div class="mb-3">
            <label>Username or Email</label>
            <input type="text" class="form-control" name="login_id" required>
          </div>
          <div class="mb-3">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button type="submit" class="btn btn-dark w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0">Don’t have an account?
          <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal" class="text-decoration-none fw-bold text-dark">Register</a>
        </p>
      </div>
    </div>
  </div>
</div>

  <!-- REGISTER MODAL -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="../app/controllers/AuthController.php?action=register">
            <div class="mb-3">
              <label>Full Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
              <label>Email</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label>Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Register</button>
          </form>
          <p class="text-center mt-3 mb-0">Already have an account? 
            <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal" class="text-decoration-none fw-bold text-dark">Login</a>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll to Top Button -->
  <button id="scrollTop">↑</button>

  <script src="assets/vendor/bootstrap5/js/bootstrap.min.js"></script>
  <script src="assets/js/scroll-up.js"></script>
</body>
</html>