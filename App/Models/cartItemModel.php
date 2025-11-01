<?php
class CartItem {
    private $item_id;
    private $cart_id;
    private $product_id;
    private $quantity;
    private $price_at_time;

    public function __construct($cart_id, $product_id, $quantity = 1, $price_at_time = 0, $item_id = null) {
        $this->cart_id = $cart_id;
        $this->product_id = $product_id;
        $this->quantity = $quantity;
        $this->price_at_time = $price_at_time;
        $this->item_id = $item_id;
    }

    // Getters
    public function getItemId() { return $this->item_id; }
    public function getCartId() { return $this->cart_id; }
    public function getProductId() { return $this->product_id; }
    public function getQuantity() { return $this->quantity; }
    public function getPrice() { return $this->price_at_time; }

    // Setters
    public function setQuantity($quantity) { $this->quantity = $quantity; }
}
