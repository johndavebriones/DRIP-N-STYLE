<?php
require_once __DIR__ . '/../../App/Controllers/AdminController.php';
$admin = new AdminController();

$title = "Products";
ob_start();
?>

<div>
    
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/assets/layout/main.php';
