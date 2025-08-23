<?php
session_start();
require_once "includes/db.php";
require_once "includes/auth.php";

requireLogin();

$order_number = $_GET['order'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/order-success.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Order Confirmed - Store</title>
</head>
<body>

    <?php require_once "includes/header.php"; ?>
    
    <div class="container">
        <?php require_once "includes/messages.php"; ?>
        
        <main class="main-content">
            <div class="success-container">
                <div class="order-success-message">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h1>Order Confirmed!</h1>
                    <p>Thank you for your order. Your order has been successfully placed.</p>
                    
                    <?php if (!empty($order_number)): ?>
                        <div class="order-details">
                            <h2>Order Number: <span class="order-number"><?= htmlspecialchars($order_number) ?></span></h2>
                            <p>You will receive an email confirmation shortly.</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="next-steps">
                        <h3>What's Next?</h3>
                        <ul>
                            <li><i class="bi bi-envelope"></i> You'll receive an email confirmation</li>
                            <li><i class="bi bi-box"></i> We'll prepare your order for shipping</li>
                            <li><i class="bi bi-truck"></i> You'll get tracking information once shipped</li>
                        </ul>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="products" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Continue Shopping
                        </a>
                        <a href="profile?tab=orders" class="btn btn-secondary">
                            <i class="bi bi-list-ul"></i> View My Orders
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
