<?php
require __DIR__ . '/../../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Cancelled</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    title: "Payment Cancelled",
    text: "You cancelled your payment.",
    icon: "error",
    confirmButtonText: "Try Again"
}).then(() => {
    window.location.href = "../index.php";
});
</script>
</body>
</html>
