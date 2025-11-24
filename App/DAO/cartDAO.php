<?php
require_once __DIR__ . '/../Models/cartModel.php';

class CartDAO {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    /**
     * Get existing cart_id or create a new cart for a user
     */
    public function getOrCreateCart(int $user_id): int {
        // 1. Check if user already has a cart
        $stmt = $this->conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return intval($row['cart_id']);
        }

        // 2. Create new cart if none exists
        $insert = $this->conn->prepare("INSERT INTO carts (user_id, created_at) VALUES (?, NOW())");
        $insert->bind_param("i", $user_id);
        $insert->execute();

        return intval($this->conn->insert_id);
    }


    /**
     * Add item to cart, or update quantity if it already exists
     */
    public function addToCart(int $user_id, int $product_id, int $quantity, float $price): bool {
        $cart_id = $this->getOrCreateCart($user_id);

        // Check if product already in cart
        $stmt = $this->conn->prepare("SELECT item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $cart_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $newQty = $row['quantity'] + $quantity;
            $update = $this->conn->prepare("UPDATE cart_items SET quantity = ?, price_at_time = ? WHERE item_id = ?");
            $update->bind_param("idi", $newQty, $price, $row['item_id']);
            $update->execute();
            return $update->affected_rows > 0;
        }

        // Insert new cart item
        $insert = $this->conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iiid", $cart_id, $product_id, $quantity, $price);

        if (!$insert->execute()) {
            // return false so JS WILL SHOW ERROR
            return false;
        }

        return $insert->affected_rows > 0;
    }

    /**
     * Get all items in user's cart
     */
    public function getCartItems(int $user_id): array {
        $cart_id = $this->getOrCreateCart($user_id);
        $stmt = $this->conn->prepare("
            SELECT ci.item_id, ci.cart_id, ci.product_id, ci.quantity, ci.price_at_time,
                   p.name, p.image, p.stock, p.size, p.description, p.color
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC) ?: [];
    }

    /**
     * Get single cart item by item_id
     */
    public function getCartItemById(int $item_id): ?array {
        $stmt = $this->conn->prepare("
            SELECT ci.item_id, ci.cart_id, ci.product_id, ci.quantity, ci.price_at_time,
                   p.name, p.image, p.stock, p.size, p.description, p.color
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.product_id
            WHERE ci.item_id = ?
        ");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    /**
     * Update quantity of a cart item
     */
    public function updateQuantity(int $item_id, int $quantity): bool {
        $stmt = $this->conn->prepare("UPDATE cart_items SET quantity = ? WHERE item_id = ?");
        $stmt->bind_param("ii", $quantity, $item_id);

        return $stmt->execute(); // FIXED
    }


    /**
     * Remove item from cart
     */
    public function removeFromCart(int $item_id): bool {
        $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);

        return $stmt->execute(); // FIXED
    }

    /**
     * Get total amount for user's cart
     */
    public function getCartTotal(int $user_id): float {
        $cart_id = $this->getOrCreateCart($user_id);
        $stmt = $this->conn->prepare("
            SELECT SUM(quantity * price_at_time) AS total
            FROM cart_items
            WHERE cart_id = ?
        ");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (float)($row['total'] ?? 0);
    }

    public function clearCart(int $user_id): bool {
    $cart_id = $this->getOrCreateCart($user_id);
    $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    return $stmt->affected_rows > 0;
    }

    /**
     * Get a single product by product_id
     */
    public function getProduct(int $product_id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE product_id = ? LIMIT 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc() ?: null;
    }

    public function getCartItemsByIds($cart_id, $item_ids = []) {
        if (empty($item_ids)) return [];

        $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
        $types = 'i' . str_repeat('i', count($item_ids)); 

        $stmt = $this->conn->prepare("
            SELECT ci.item_id, ci.quantity, ci.price_at_time, p.name, p.image, p.size 
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
            AND ci.item_id IN ($placeholders)
        ");

        $stmt->bind_param($types, $cart_id, ...$item_ids);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
