<?php
require_once __DIR__ . '/../../App/config/database_connect.php';
require_once __DIR__ . '/../../App/Controllers/OrderController.php';

$db = new Database();
$conn = $db->connect();

$order_id = $_POST['order_id'] ?? null;
$status = $_POST['status'] ?? null;

if ($order_id && $status) {
    $controller = new OrderController($conn);
    $controller->updateStatus($order_id, $status);
}

header("Location: orders.php");
exit;