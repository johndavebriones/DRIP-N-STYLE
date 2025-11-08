<?php
class Order {
    public $order_id;
    public $user_id;
    public $total_amount;
    public $order_status;
    public $pickup_date;
    public $payment_id;

    public function __construct($user_id, $total_amount, $order_status = 'Pending', $pickup_date = null, $payment_id = null) {
        $this->user_id = $user_id;
        $this->total_amount = $total_amount;
        $this->order_status = $order_status;
        $this->pickup_date = $pickup_date;
        $this->payment_id = $payment_id;
    }
}
