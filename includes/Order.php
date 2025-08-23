<?php

class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }



    public function generateOrderNumber() {
        $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        return $order_number;
    }
}