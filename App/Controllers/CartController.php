<?php
session_start();
require_once __DIR__ . '/../DAO/CartDAO.php';
require_once __DIR__ . '/../config/database_connect.php';

// Initialize DB connection and CartDAO
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
        break;

    case 'update':
        $item_id = intval($_POST['item_id'] ?? 0);
        $quantity_action = $_POST['quantity_action'] ?? '';

        // Get the current quantity and stock from DAO
        $item = $cartDAO->getCartItemById($item_id);
        if (!$item) {
            header("Location: ../../Public/shop/cart.php");
            exit;
        }

        $quantity = (int)$item['quantity'];
        $stock = (int)($item['stock'] ?? 999); // fallback if no stock info

        // Apply increase/decrease
        if ($quantity_action === 'increase') {
            $quantity++;
            if ($quantity > $stock) {
                $quantity = $stock;
                $_SESSION['stock_limit'] = true;
            }
        } elseif ($quantity_action === 'decrease') {
            $quantity = max(1, $quantity - 1); // cannot go below 1
        }

        // Update quantity in DB
        $cartDAO->updateQuantity($item_id, $quantity);
        $_SESSION['update_success'] = true;

        header("Location: ../../Public/shop/cart.php");
        exit;
        break;

    case 'remove':
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id) {
            $cartDAO->removeFromCart($item_id);
        }

        header("Location: ../../Public/shop/cart.php");
        exit;
        break;

    default:
        header("Location: ../../Public/shop/cart.php");
        exit;
        break;
}
