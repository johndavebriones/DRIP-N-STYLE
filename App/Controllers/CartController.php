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
        $user_id    = $_SESSION['user_id'] ?? 0;
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity   = intval($_POST['quantity'] ?? 1);
        $price      = floatval($_POST['price'] ?? 0);

        if (!$user_id || $product_id < 1 || $quantity < 1) {
            echo 'error|Invalid request';
            return;
        }

        // Get product to check stock
        $product = $this->cartDAO->getProduct($product_id);
        if (!$product) {
            echo 'error|Product not found';
            return;
        }

        $stock = intval($product['stock']);
        if ($quantity > $stock) {
            echo 'error|Only ' . $stock . ' items available';
            return;
        }

        $added = $this->cartDAO->addToCart($user_id, $product_id, $quantity, $price);

        if ($added) {
            echo 'success|Product added to cart';
        } else {
            echo 'error|Failed to add product';
        }
    }

    public function updateCart() {
        $item_id = intval($_POST['item_id'] ?? 0);
        $action  = $_POST['quantity_action'] ?? '';

        $item = $this->cartDAO->getCartItemById($item_id);
        if (!$item) {
            echo 'error|Item not found';
            return;
        }

        $quantity = intval($item['quantity']);
        $stock = intval($item['stock']);

        if ($action === 'increase') {
            if ($quantity >= $stock) {
                echo 'error|Max stock limit reached';
                return;
            }
            $quantity++;
        } elseif ($action === 'decrease') {
            $quantity = max(1, $quantity - 1);
        }

        $this->cartDAO->updateQuantity($item_id, $quantity);
        echo 'success|Quantity updated';
    }

    public function removeFromCart() {
        $item_id = intval($_POST['item_id'] ?? 0);

        if (!$item_id) {
            echo 'error|Invalid item';
            return;
        }

        $this->cartDAO->removeFromCart($item_id);
        echo 'success|Item removed';
    }

    private function redirectCart() {
        header("Location: /DRIP-N-STYLE/Public/shop/cart.php");
        exit;
    }
}

/* MAIN */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new CartController();
    $controller->handleRequest();
}
