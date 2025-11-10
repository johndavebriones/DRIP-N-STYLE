<?php
require_once __DIR__ . '/../Models/cartModel.php';

class CartDAO {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // ✅ Get or create cart for a user
    public function getOrCreateCart($user_id) {
        $stmt = $this->conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['cart_id'];
        } else {
            $insert = $this->conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $insert->bind_param('i', $user_id);
            $insert->execute();
            return $insert->insert_id;
        }
    }

    // ✅ Get all cart items for a user (includes product info)
    public function getCartItems($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        $stmt = $this->conn->prepare("
            SELECT ci.item_id, ci.cart_id, ci.product_id, ci.quantity, ci.price_at_time,
                   p.name, p.image, p.stock, p.size, p.description
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->bind_param('i', $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ✅ Add item to cart
    public function addToCart($user_id, $product_id, $quantity, $price) {
        $cart_id = $this->getOrCreateCart($user_id);

        // Check if product exists in cart
        $stmt = $this->conn->prepare("SELECT item_id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->bind_param('ii', $cart_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Update quantity
            $newQty = $row['quantity'] + $quantity;
            $update = $this->conn->prepare("UPDATE cart_items SET quantity = ?, price_at_time = ? WHERE item_id = ?");
            $update->bind_param('idi', $newQty, $price, $row['item_id']);
            $update->execute();
            return $update->affected_rows;
        } else {
            // Insert new item
            $insert = $this->conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
            $insert->bind_param('iiid', $cart_id, $product_id, $quantity, $price);
            $insert->execute();
            return $insert->affected_rows;
        }
    }

    // ✅ Remove item from cart
    public function removeFromCart($item_id) {
        $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE item_id = ?");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        return $stmt->affected_rows;
    }

    // ✅ Update quantity of a cart item
    public function updateQuantity($item_id, $quantity) {
        $stmt = $this->conn->prepare("UPDATE cart_items SET quantity = ? WHERE item_id = ?");
        $stmt->bind_param('ii', $quantity, $item_id);
        $stmt->execute();
        return $stmt->affected_rows;
    }

    // ✅ Get single cart item by ID (for stock checking)
    public function getCartItemById($item_id) {
        $stmt = $this->conn->prepare("
            SELECT ci.item_id, ci.cart_id, ci.product_id, ci.quantity, ci.price_at_time, p.stock
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.item_id = ?
        ");
        $stmt->bind_param('i', $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ✅ Get total cart value
    public function getCartTotal($user_id) {
        $cart_id = $this->getOrCreateCart($user_id);
        $stmt = $this->conn->prepare("
            SELECT SUM(ci.quantity * ci.price_at_time) AS total
            FROM cart_items ci
            WHERE ci.cart_id = ?
        ");
        $stmt->bind_param('i', $cart_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0.0;
    }

    public function clearCart($user_id) {
        $stmt = $this->conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $cart_id = $row['cart_id'];

            $delItems = $this->conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $delItems->bind_param("i", $cart_id);
            $delItems->execute();
            $delItems->close();
        }
        $stmt->close();
    }
}
