<?php
session_start();
require_once __DIR__ . '/../DAO/cartDAO.php';
require_once __DIR__ . '/../config/database_connect.php';

// Initialize DB connection and CartDAO
$db = new Database();
$conn = $db->connect();
$cartDAO = new CartDAO($conn);

$action = $_POST['action'] ?? '';

switch ($action) {

    // ✅ ADD ITEM
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

    // ✅ UPDATE QUANTITY
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

    // ✅ REMOVE ITEM
    case 'remove':
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id) {
            $cartDAO->removeFromCart($item_id);
        }

        header("Location: ../../Public/shop/cart.php");
        exit;
        break;

    /* ---------------------------------------
       🔹 NEW ACTIONS ADDED BELOW
    --------------------------------------- */

    // ✅ VIEW CART (for JSON or API use)
    case 'view':
        $user_id = $_SESSION['user_id'] ?? 0;
        if (!$user_id) {
            echo json_encode(['error' => 'User not logged in']);
            exit;
        }

        $cartData = $cartDAO->getCartDetails($user_id);
        header('Content-Type: application/json');
        echo json_encode($cartData);
        exit;
        break;

    // ✅ CLEAR CART
    case 'clear':
        $user_id = $_SESSION['user_id'] ?? 0;
        if ($user_id) {
            $cartDAO->clearCart($user_id);
            $_SESSION['cart_cleared'] = true;
        }

        header("Location: ../../Public/shop/cart.php");
        exit;
        break;

    // ✅ CHECKOUT (for PayMongo or similar)
    case 'checkout':
        $user_id = $_SESSION['user_id'] ?? 0;
        if (!$user_id) {
            echo json_encode(['error' => 'User not logged in']);
            exit;
        }

        $total = $cartDAO->getCartTotal($user_id);
        $cartItems = $cartDAO->getCartItems($user_id);

        // Return data for API integration
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'total' => number_format($total, 2, '.', ''),
            'items' => $cartItems
        ]);
        exit;
        break;

    // ✅ DEFAULT
    default:
        header("Location: ../../Public/shop/cart.php");
        exit;
        break;
}
