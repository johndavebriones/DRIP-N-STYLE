<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? 'Admin Dashboard' ?></title>

  <!-- Bootstrap -->
  <link href="../assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/sidebar.css">
</head>
<body>
  <!-- Topbar with burger -->
  <div class="topbar d-lg-none bg-dark text-white px-3 py-2 shadow-sm">
    <button id="sidebarToggle" class="btn btn-warning me-3">☰</button>
    <span class="fw-bold">Drip N' Style</span>
  </div>

  <!-- Sidebar -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- Optional backdrop -->
  <div class="sidebar-backdrop"></div>

  <!-- Main content -->
  <div class="main-content">
    <?= $content ?>
  </div>
  
  <script>
    // Sidebar toggle
    const sidebar = document.querySelector('.sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    const toggleBtn = document.getElementById('sidebarToggle');

    function closeSidebar() {
      sidebar.classList.remove('active');
      backdrop.classList.remove('active');
    }

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('active');
      backdrop.classList.toggle('active');
    });

    backdrop.addEventListener('click', closeSidebar);
  </script>
  <script src="../assets/vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
</body>
</html>
