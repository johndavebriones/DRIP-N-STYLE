<?php
session_start();
require_once __DIR__ . '/../DAO/cartDAO.php';
require_once __DIR__ . '/../config/database_connect.php';

class CartController {
    private $cartDAO;

    public function __construct() {
        $db = new Database();
        $this->cartDAO = new CartDAO($db->connect());
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'add':
                $this->addToCart(); // AJAX
                break;

            case 'update':
                $this->updateCart(); // Reload page
                break;

            case 'remove':
                $this->removeFromCart(); // Reload page
                break;

            default:
                header("Location: /DRIP-N-STYLE/Public/shop/cart.php");
                exit;
        }
    }

    // ------------------- ADD TO CART (AJAX) -------------------
    public function addToCart() {
        header("Content-Type: application/json");

        $user_id    = $_SESSION['user_id'] ?? 0;
        $product_id = intval($_POST['product_id'] ?? 0);
        $quantity   = intval($_POST['quantity'] ?? 1);
        $price      = floatval($_POST['price'] ?? 0);

        if (!$user_id || $product_id < 1 || $quantity < 1) {
            echo json_encode(["success" => false, "message" => "Invalid request"]);
            exit;
        }

        $product = $this->cartDAO->getProduct($product_id);
        if (!$product) {
            echo json_encode(["success" => false, "message" => "Product not found"]);
            exit;
        }

        $stock = intval($product['stock']);

        // Check existing quantity in cart
        $cartItems = $this->cartDAO->getCartItems($user_id);
        $existingQty = 0;
        foreach ($cartItems as $item) {
            if ($item['product_id'] == $product_id) {
                $existingQty = intval($item['quantity']);
                break;
            }
        }

        // Total quantity requested
        $totalQty = $existingQty + $quantity;

        if ($totalQty > $stock) {
            $available = $stock - $existingQty;
            $msg = $available > 0 
                ? "You can only add $available more item(s) of this product"
                : "You already have the maximum available stock in your cart";
            echo json_encode(["success" => false, "message" => $msg]);
            exit;
        }

        $added = $this->cartDAO->addToCart($user_id, $product_id, $quantity, $price);

        if ($added) {
            echo json_encode(["success" => true, "message" => "Product added to cart"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to add product"]);
        }
        exit;
    }

    // ------------------- UPDATE QUANTITY (Reload) -------------------
    public function updateCart() {
        header("Content-Type: application/json");

        $item_id = intval($_POST['item_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);

        if (!$item_id || $quantity < 1) {
            echo json_encode(["success" => false, "message" => "Invalid request"]);
            exit;
        }

        $item = $this->cartDAO->getCartItemById($item_id);
        if (!$item) {
            echo json_encode(["success" => false, "message" => "Item not found"]);
            exit;
        }

        $stock = intval($item['stock']);
        if ($quantity > $stock) {
            echo json_encode(["success" => false, "message" => "Only $stock items available"]);
            exit;
        }

        $updated = $this->cartDAO->updateQuantity($item_id, $quantity);

        if ($updated) {
            echo json_encode(["success" => true, "message" => "Quantity updated", "new_quantity" => $quantity]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to update quantity"]);
        }
        exit;
    }

    // ------------------- REMOVE ITEM (Reload) -------------------
    public function removeFromCart() {
        $item_id = intval($_POST['item_id'] ?? 0);

        if ($item_id) {
            $this->cartDAO->removeFromCart($item_id);
            $_SESSION['flash_message'] = "Item removed from cart.";
        }

        header("Location: /DRIP-N-STYLE/Public/shop/cart.php");
        exit;
    }
}

/* MAIN */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new CartController())->handleRequest();
} else {
    header("Location: /DRIP-N-STYLE/Public/shop/cart.php");
    exit;
}
