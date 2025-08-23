<?php

session_start();

require_once "../includes/db.php";
require_once "../includes/csrf.php";
require_once "../includes/auth.php";
require_once "../includes/UserAddress.php";
require_once "../includes/Order.php";
require_once "../includes/Cart.php";

$order = new Order($pdo);
$userAddress = new UserAddress($pdo);
$cart = new Cart($pdo);

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart?error=invalid_request');
    exit;
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../cart?error=csrf_invalid');
    exit;
}

if (!isset($_POST['address_id']) || !isset($_POST['payment_method']) || !isset($_POST['total_amount'])) {
    header('Location: ../cart?error=missing_data');
    exit;
}

$user_id = $_SESSION['user_id'];
$total_amount = $_POST['total_amount'];
$address_id = $_POST['address_id'];
$payment_method = $_POST['payment_method'];

$cartItems = $cart->getCartItems($user_id);
if (empty($cartItems)) {
    header('Location: ../cart?error=empty_cart');
    exit;
}

$address = $userAddress->getAddressById($address_id, $user_id);
if (!$address) {
    header('Location: ../cart?error=invalid_address');
    exit;
}

$shipping_address = $address['full_name'] . ', ' . $address['street_address'] . ', ' . $address['city'] . ', ' . $address['phone'];

$order_number = $order->generateOrderNumber();

try {
    $pdo->beginTransaction();
    
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, payment_method) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $orderStmt->execute([$order_number, $user_id, $total_amount, 'pending', $shipping_address, $payment_method]);
    
    $order_id = $pdo->lastInsertId();
    
    $insertStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        SELECT 
            ?, 
            sc.product_id, 
            sc.quantity, 
            p.price
        FROM shopping_cart sc
        JOIN products p ON sc.product_id = p.id
        WHERE sc.user_id = ?
    ");
    $insertStmt->execute([$order_id, $user_id]);
    
    $stockStmt = $pdo->prepare("
        UPDATE products p
        JOIN order_items oi ON oi.product_id = p.id
        SET p.stock_quantity = CASE WHEN p.stock_quantity >= oi.quantity THEN p.stock_quantity - oi.quantity ELSE 0 END
        WHERE oi.order_id = ?
    ");
    $stockStmt->execute([$order_id]);
    
    $clearStmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
    $clearStmt->execute([$user_id]);
    
    $pdo->commit();
    
    header('Location: ../order_success?order=' . urlencode($order_number));
    exit;
    
} catch (Exception $e) {
    $pdo->rollback();
    error_log('Checkout error: ' . $e->getMessage());
    error_log('Error file: ' . $e->getFile() . ':' . $e->getLine());
    error_log('Error trace: ' . $e->getTraceAsString());
    header('Location: ../cart?error=checkout_failed');
    exit;
}
?>