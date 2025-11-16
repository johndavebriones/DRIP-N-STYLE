<?php

$title = $title ?? "Drip N' Style";

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title) ?></title>

  <!-- Bootstrap CSS -->
  <link href="assets/vendor/bootstrap5/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/navbar.css">
  <link rel="stylesheet" href="assets/css/footer.css">
  <link rel="stylesheet" href="assets/css/home.css"> <!-- optional -->
</head>
<body>

  <?php include __DIR__ . '/partials/navbar.php'; ?>

  <main class="content">
    <?= $content ?>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <button id="scrollTop">↑</button>
  <script src="assets/js/scroll-up.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
