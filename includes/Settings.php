<?php

class Settings {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getSetting($name, $default = null) {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE name = ?");
        $stmt->execute([$name]);
        $result = $stmt->fetch();
        
        return $result ? $result['value'] : $default;
    }
    
    public function setSetting($name, $value) {
        $stmt = $this->pdo->prepare("INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        return $stmt->execute([$name, $value, $value]);
    }
    
    public function getAllSettings() {
        $stmt = $this->pdo->query("SELECT * FROM settings ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function getTaxRate() {
        $taxRate = $this->getSetting('tax_rate', '0.08');
        return floatval($taxRate);
    }
    
    public function getShippingFee() {
        $shippingFee = $this->getSetting('shipping_fee', '5.99');
        return floatval($shippingFee);
    }
    
    public function getTaxRatePercentage() {
        $taxRate = $this->getTaxRate();
        return round($taxRate * 100, 2);
    }
    
    public function calculateTax($subtotal) {
        return $subtotal * $this->getTaxRate();
    }
    
    public function calculateTotal($subtotal) {
        $tax = $this->calculateTax($subtotal);
        $shipping = $this->getShippingFee();
        return $subtotal + $tax + $shipping;
    }
}
