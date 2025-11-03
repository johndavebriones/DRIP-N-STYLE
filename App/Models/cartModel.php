<?php

class Cart {
    private $cart_id;
    private $user_id;
    private $created_at;
    private $updated_at;

    public function __construct($cart_id = null, $user_id = null, $created_at = null, $updated_at = null) {
        $this->cart_id = $cart_id;
        $this->user_id = $user_id;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    // Getters
    public function getCartId() { return $this->cart_id; }
    public function getUserId() { return $this->user_id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Setters
    public function setUserId($user_id) { $this->user_id = $user_id; }
}

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
