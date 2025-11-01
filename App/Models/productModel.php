<?php
class ProductModel {
    public $product_id;
    public $name;
    public $description;
    public $price;
    public $category_id;
    public $image;

    public function __construct($product_id, $name, $description, $price, $category_id, $image) {
        $this->product_id = $product_id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->category_id = $category_id;
        $this->image = $image;
    }
}
