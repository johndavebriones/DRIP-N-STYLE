<?php
require __DIR__ . '/../../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    title: "Payment Successful!",
    text: "Thank you for your purchase.",
    icon: "success",
    confirmButtonText: "Back to Shop"
}).then(() => {
    window.location.href = "../index.php";
});
</script>
</body>
</html>
