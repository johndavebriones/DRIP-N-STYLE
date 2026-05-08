<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Admin — Drip N\' Style' ?></title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- Bootstrap -->
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">

  <!-- Admin Design System (order matters) -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/sidebar.css">
</head>
<body>

<!-- Gold accent bar -->
<div class="gold-bar"></div>

<!-- Topbar (mobile only) -->
<div class="topbar d-lg-none">
  <button id="sidebarToggle" class="btn" aria-label="Open menu">☰</button>
  <span class="fw-bold">Drip N' Style</span>
</div>

<!-- Sidebar -->
<?php include __DIR__ . '/partials/sidebar.php'; ?>

<!-- Backdrop -->
<div class="sidebar-backdrop"></div>

<!-- Main content -->
<div class="main-content">
  <?= $content ?>
</div>

<script>
  const sidebar  = document.querySelector('.sidebar');
  const backdrop = document.querySelector('.sidebar-backdrop');
  const toggle   = document.getElementById('sidebarToggle');

  function closeSidebar() {
    sidebar.classList.remove('active');
    backdrop.classList.remove('active');
  }

  if (toggle) {
    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('active');
      backdrop.classList.toggle('active');
    });
  }

  backdrop.addEventListener('click', closeSidebar);
</script>

<script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
</body>
</html>
