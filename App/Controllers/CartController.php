<?php
session_start();
require_once __DIR__ . '/../DAO/cartDAO.php';
require_once __DIR__ . '/../config/database_connect.php';

class CartController {
    private $cartDAO;

    public function __construct() {
        $db = new Database();
        $conn = $db->connect();
        $this->cartDAO = new CartDAO($conn);
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $this->addToCart();
                break;

            case 'update':
                $this->updateCart();
                break;

            case 'remove':
                $this->removeFromCart();
                break;

            default:
                $this->redirectCart();
        }
    }

    public function addToCart() {
        $user_id = $_SESSION['user_id'] ?? 0;
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        $price = floatval($_POST['price'] ?? 0);

        if (!$user_id) return $this->jsonResponse(false, 'You must be logged in.');
        if ($product_id < 1 || $quantity < 1) return $this->jsonResponse(false, 'Invalid product or quantity.');

        $added = $this->cartDAO->addToCart($user_id, $product_id, $quantity, $price);
        return $this->jsonResponse($added, $added ? 'Product added to cart!' : 'Failed to add product.');
    }

    public function updateCart() {
        $item_id = intval($_POST['item_id'] ?? 0);
        $quantity_action = $_POST['quantity_action'] ?? '';

        $item = $this->cartDAO->getCartItemById($item_id);
        if (!$item) return $this->jsonResponse(false, 'Item not found.');

        $quantity = intval($item['quantity']);
        $stock = intval($item['stock'] ?? 999);

        if ($quantity_action === 'increase') $quantity = min($quantity + 1, $stock);
        elseif ($quantity_action === 'decrease') $quantity = max(1, $quantity - 1);

        $this->cartDAO->updateQuantity($item_id, $quantity);
        return $this->jsonResponse(true, 'Quantity updated!');
    }

    public function removeFromCart() {
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id) $this->cartDAO->removeFromCart($item_id);
        return $this->jsonResponse(true, 'Item removed from cart.');
    }

    private function redirectCart() {
        header("Location: /DRIP-N-STYLE/Public/shop/cart.php");
        exit;
    }

    private function jsonResponse($success, $message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}

/* MAIN */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CartController();
    $controller->handleRequest();
}
