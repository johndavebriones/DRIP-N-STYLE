<?php

class Product {
    private $id;
    private $name;
    private $price;
    private $category_id;
    private $size;
    private $image;
    private $stock;
    private $status;
    private $description; // 🆕 Added description field
    private $date_added;
    private $deleted_at;

    public function __construct(
        $name,
        $price,
        $category_id,
        $size,
        $image = null,
        $stock = 0,
        $status = 'Available',
        $description = null, // 🆕 Added to constructor
        $id = null,
        $date_added = null,
        $deleted_at = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->category_id = $category_id;
        $this->size = $size;
        $this->image = $image;
        $this->stock = $stock;
        $this->status = $status;
        $this->description = $description; // 🆕
        $this->date_added = $date_added;
        $this->deleted_at = $deleted_at;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getPrice() { return $this->price; }
    public function getCategoryId() { return $this->category_id; }
    public function getSize() { return $this->size; }
    public function getImage() { return $this->image; }
    public function getStock() { return $this->stock; }
    public function getStatus() { return $this->status; }
    public function getDescription() { return $this->description; } // 🆕
    public function getDateAdded() { return $this->date_added; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Setters
    public function setImage($image) { $this->image = $image; }
    public function setDescription($description) { $this->description = $description; } // 🆕
}
