<?php
require_once __DIR__ . '/../../App/config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';

$db = new Database();
$conn = $db->connect();

$order_id = $_GET['id'] ?? null;
$controller = new OrderController($conn);

$items = $controller->getOrderItems($order_id);

if (empty($items)) {
    echo "<p class='text-muted'>No items found for this order.</p>";
    exit;
}

echo "<table class='table table-bordered text-center'>";
echo "<thead class='table-dark'><tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead><tbody>";

$total = 0;
foreach ($items as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    echo "<tr>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity']}</td>
            <td>₱" . number_format($item['price'], 2) . "</td>
            <td>₱" . number_format($subtotal, 2) . "</td>
          </tr>";
}

echo "</tbody></table>";
echo "<h5 class='text-end fw-bold mt-3'>Total: ₱" . number_format($total, 2) . "</h5>";