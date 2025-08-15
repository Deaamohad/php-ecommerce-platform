<?php
session_start();
require_once "../../includes/auth.php";
require_once "../../includes/csrf.php";
require_once "../../includes/db.php";
require_once "../../includes/Settings.php";

requireLogin();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin');
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../../admin?error=settings_error&message=' . urlencode('Invalid request. Please try again.'));
    exit();
}

$settings = new Settings($pdo);

try {
    if (isset($_POST['tax_rate'])) {
        $taxRate = floatval($_POST['tax_rate']);
        if ($taxRate < 0 || $taxRate > 100) {
            throw new Exception("Tax rate must be between 0 and 100");
        }
        $taxRateDecimal = $taxRate / 100;
        $result = $settings->setSetting('tax_rate', $taxRateDecimal);
        if (!$result) {
            throw new Exception("Failed to update tax rate");
        }
    }
    
    if (isset($_POST['shipping_fee'])) {
        $shippingFee = floatval($_POST['shipping_fee']);
        if ($shippingFee < 0) {
            throw new Exception("Shipping fee cannot be negative");
        }
        $result = $settings->setSetting('shipping_fee', $shippingFee);
        if (!$result) {
            throw new Exception("Failed to update shipping fee");
        }
    }
    
    header('Location: ../../admin?success=settings_updated');
    
} catch (Exception $e) {
    header('Location: ../../admin?error=settings_error&message=' . urlencode($e->getMessage()));
}
exit();
