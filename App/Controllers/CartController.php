<?php
session_start();
require_once __DIR__ . '/../DAO/cartDAO.php';
require_once __DIR__ . '/../config/database_connect.php';

$db = new Database();
$conn = $db->connect();
$cartDAO = new CartDAO($conn);

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'add':
        $user_id = $_SESSION['user_id'] ?? 0;
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        $price = floatval($_POST['price'] ?? 0);

        if ($user_id && $product_id) {
            $cartDAO->addToCart($user_id, $product_id, $quantity, $price);
        }
        header("Location: ../../Public/shop/shop.php");
        exit;

    case 'update':
        $item_id = intval($_POST['item_id'] ?? 0);
        $quantity_action = $_POST['quantity_action'] ?? '';

        $item = $cartDAO->getCartItemById($item_id);
        if (!$item) header("Location: ../../Public/shop/cart.php");

        $quantity = (int)$item['quantity'];
        $stock = (int)($item['stock'] ?? 999);

        if ($quantity_action === 'increase') {
            $quantity = min($quantity + 1, $stock);
            if ($quantity === $stock) $_SESSION['stock_limit'] = true;
        } elseif ($quantity_action === 'decrease') {
            $quantity = max(1, $quantity - 1);
        }

        $cartDAO->updateQuantity($item_id, $quantity);
        $_SESSION['update_success'] = true;
        header("Location: ../../Public/shop/cart.php");
        exit;

    case 'remove':
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id) $cartDAO->removeFromCart($item_id);
        header("Location: ../../Public/shop/cart.php");
        exit;

    default:
        header("Location: ../../Public/shop/cart.php");
        exit;
}
