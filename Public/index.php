<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../App/Helpers/SessionHelper.php';
if (session_status() === PHP_SESSION_NONE) session_start();
SessionHelper::preventCache();

if (isset($_SESSION['user_id']) && !isset($_SESSION['user_name'])) {
    require_once __DIR__ . '/../App/Config/database_connect.php';
    $db = new Database();
    $conn = $db->connect();
    $stmt = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $_SESSION['user_name'] = $row['name'];
        $stmt->close();
    }
}

require_once __DIR__ . '/../App/Controllers/ProductController.php';
$productController = new ProductController();
$featuredProducts  = $productController->getFeaturedProducts(6);
$currentPage       = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Drip N' Style | Home</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>

<div class="gold-bar"></div>

<!-- Navbar -->
<nav class="dns-nav">
  <a href="index.php" class="nav-logo">Drip N' Style</a>
  <div class="nav-links">
    <a href="#about" class="hide-mobile">About</a>
    <a href="#contact" class="hide-mobile">Contact</a>
    <a href="../Public/shop/shop.php">Shop</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php" class="nav-btn">My Account</a>
    <?php else: ?>
      <a href="LoginPage.php" class="nav-btn">Login</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Hero -->
<section class="hero">
  <img src="assets/images/dripnstyleAbout.png" alt="" class="hero-img">
  <div class="hero-content">
    <p class="hero-eyebrow">New Season Collection</p>
    <h1 class="hero-title">Discover Your<br><span>Style</span></h1>
    <p class="hero-sub">— Grab What You Desire</p>
    <a href="../Public/shop/shop.php" class="hero-cta">Shop Now</a>
  </div>
  <div class="hero-deco"><span></span><span></span><span></span></div>
</section>

<!-- Brands -->
<div class="brands-section">
  <p class="brands-label">Brands We Carry</p>
  <div class="brands-row">
    <span class="brand-pill">Calvin Klein</span>
    <span class="brand-pill">Essentials</span>
    <span class="brand-pill">Uniqlo</span>
    <span class="brand-pill">Zara</span>
    <span class="brand-pill">GAP</span>
    <span class="brand-pill">Polo</span>
    <span class="brand-pill">New Era</span>
    <span class="brand-pill">Alo</span>
  </div>
</div>

<!-- Featured Products -->
<section class="dns-section products-section">
  <div style="max-width:1100px;margin:0 auto;">
    <p class="section-eyebrow">Handpicked for You</p>
    <h2 class="section-title">Featured <span>Products</span></h2>

    <?php if (empty($featuredProducts)): ?>
      <p style="color:var(--text-muted);font-size:14px;margin-top:32px;">No featured products available at the moment. Check back soon!</p>
    <?php else: ?>
      <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
        <div class="product-card">
          <div class="product-img-wrap">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <span class="product-badge">Featured</span>
          </div>
          <div class="product-body">
            <p class="product-cat"><?= htmlspecialchars($product['category_name'] ?? 'Fashion') ?></p>
            <p class="product-name"><?= htmlspecialchars($product['name']) ?></p>
            <?php if (!empty($product['description'])): ?>
              <p class="product-desc"><?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...</p>
            <?php endif; ?>
            <div class="product-footer">
              <span class="product-price">₱<?= number_format($product['price'], 2) ?></span>
              <a href="../Public/shop/product_details.php?id=<?= $product['product_id'] ?>" class="product-link">View Details</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>assets/images/dripnstyleAbout.png
      </div>
    <?php endif; ?>

    <div class="view-all-wrap">
      <a href="../Public/shop/shop.php" class="btn-outline-gold">View All Products</a>
    </div>
  </div>
</section>

<div class="dns-divider"></div>

<!-- About -->
<section id="about" class="dns-section about-section">
  <div class="about-grid">
    <div>
      <img src="assets/images/dripnstyleAbout.png" alt="About Drip N' Style" class="about-img">
    </div>
    <div class="about-text">
      <p class="section-eyebrow">Our Story</p>
      <h2 class="section-title">About Drip<br><span>N' Style</span></h2>
      <div style="height:1px;background:var(--border-inner);margin:20px 0 24px;width:60px;"></div>
      <p>Drip N' Style is your trusted online clothing store, offering a wide range of trendy, high-quality apparel for all styles. Whether you're into casual, streetwear, or classy fits — we've got you covered.</p>
      <p>Our mission is to bring premium fashion closer to you with affordable prices, a personalized shopping experience, and friendly customer service.</p>
      <a href="../Public/shop/shop.php" class="hero-cta" style="margin-top:8px;display:inline-block;">Shop Now</a>
    </div>
  </div>
</section>

<div class="dns-divider"></div>

<!-- Contact -->
<section id="contact" class="dns-section contact-section">
  <div style="max-width:1100px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:8px;">
      <p class="section-eyebrow">Get In Touch</p>
      <h2 class="section-title">Contact <span>Us</span></h2>
      <p class="section-sub" style="margin:8px auto 0;">Got questions, concerns, or inquiries? We're here to help anytime.</p>
    </div>

    <div class="contact-info-grid">
      <div class="contact-card">
        <div class="contact-icon">📍</div>
        <p class="contact-card-label">Address</p>
        <p>Damballelos Street, Barangay 4,<br>Balayan, Philippines 4213</p>
      </div>
      <div class="contact-card">
        <div class="contact-icon">📞</div>
        <p class="contact-card-label">Phone</p>
        <p>+63 965 327 9916</p>
      </div>
      <div class="contact-card">
        <div class="contact-icon">📧</div>
        <p class="contact-card-label">Email</p>
        <p>dripnstyle.shop@gmail.com</p>
      </div>
    </div>

    <div class="contact-form-wrap">
      <div class="gold-bar"></div>
      <div class="contact-form-inner">
        <p class="section-eyebrow" style="margin-bottom:16px;">Send a Message</p>
        <form>
          <div class="form-field">
            <input class="form-input" type="text" placeholder="Your name" required>
          </div>
          <div class="form-field">
            <input class="form-input" type="email" placeholder="Your email" required>
          </div>
          <div class="form-field">
            <textarea class="form-input" placeholder="Your message" required></textarea>
          </div>
          <button type="submit" class="hero-cta" style="width:100%;text-align:center;display:block;">Send Message</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="dns-footer">
  <div class="footer-top">
    <div>
      <p class="footer-logo">Drip N' Style</p>
      <p class="footer-tagline">Wear Your Confidence</p>
    </div>
    <div class="footer-links">
      <a href="index.php">Home</a>
      <a href="#about">About</a>
      <a href="../Public/shop/shop.php">Shop</a>
      <a href="#contact">Contact</a>
      <a href="LoginPage.php">Login</a>
    </div>
  </div>
  <p class="footer-bottom">&copy; <?= date('Y') ?> Drip N' Style. All rights reserved.</p>
</footer>
<div class="gold-bar"></div>

<button id="scrollTop">↑</button>

<script>
  const scrollBtn = document.getElementById('scrollTop');
  window.addEventListener('scroll', () => {
    scrollBtn.classList.toggle('visible', window.scrollY > 300);
  });
  scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
</script>
</body>
</html>