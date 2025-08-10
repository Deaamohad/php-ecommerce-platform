<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/Cart.php";
require_once "../includes/auth.php";

requireLogin();

$cart = new Cart($pdo);
$user_id = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($user_id);
$cartTotal = $cart->getCartTotal($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $cart->removeFromCart($user_id, $product_id);
        header('Location: cart');
        exit();
    }
    
    if (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        $cart->updateQuantity($user_id, $product_id, $quantity);
        header('Location: cart');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/cart.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Shopping Cart - Store</title>
</head>
<body>
    <div class="site-header">
        <div class="container">
            <div class="header-content">
                <h1><a href="products">Store</a></h1>
                <nav class="main-nav">
                    <a href="products"><i class="bi bi-shop"></i> Products</a>
                    
                    <div class="cart-nav">
                        <a href="cart" class="active">
                            <i class="bi bi-cart"></i> Cart
                            <?php if (count($cartItems) > 0): ?>
                                <span class="cart-badge"><?php echo array_sum(array_column($cartItems, 'quantity')); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <a href="profile"><i class="bi bi-person"></i> Profile</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin"><i class="bi bi-gear"></i> Admin</a>
                    <?php endif; ?>
                    <a href="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
        </div>
    </div>

    <div class="cart-container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="bi bi-cart-x"></i>
                <h2>Your cart is empty</h2>
                <p>Start shopping to add items to your cart!</p>
                <a href="products" class="browse-btn">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="cart-items-section">
                <h1><i class="bi bi-cart"></i> Shopping Cart (<?php echo count($cartItems); ?> items)</h1>
                
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin-right: 20px; border-radius: 8px;">
                                <i class="bi bi-image" style="font-size: 30px; color: #ccc;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="price">Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <p class="subtotal">Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                    <button type="submit" name="update_quantity" class="quantity-btn">−</button>
                                </form>
                                
                                <div class="quantity-display"><?php echo $item['quantity']; ?></div>
                                
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                    <button type="submit" name="update_quantity" class="quantity-btn">+</button>
                                </form>
                                
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_item" class="remove-btn" onclick="return confirm('Remove this item from cart?')">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-summary">
                <h2><i class="bi bi-receipt"></i> Order Summary</h2>
                
                <div class="summary-row">
                    <span>Items (<?php echo count($cartItems); ?>):</span>
                    <span>$<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>$5.99</span>
                </div>
                
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($cartTotal * 0.08, 2); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($cartTotal + 5.99 + ($cartTotal * 0.08), 2); ?></span>
                </div>
                
                <div class="address-section">
                    <h4><i class="bi bi-geo-alt"></i> Shipping Address</h4>
                    <p>John Doe</p>
                    <p>123 Main Street</p>
                    <p>City, State 12345</p>
                    <a href="#" class="edit-link">Edit Address</a>
                </div>
                
                <div class="payment-section">
                    <h4><i class="bi bi-credit-card"></i> Payment Method</h4>
                    <p>Visa ending in 1234</p>
                    <p>Expires 12/25</p>
                    <a href="#" class="edit-link">Change Payment</a>
                </div>
                
                <div class="checkout-section">
                    <button class="checkout-btn" onclick="alert('Checkout functionality coming soon!')">
                        <i class="bi bi-lock"></i> Secure Checkout
                    </button>
                    <p style="text-align: center; font-size: 12px; color: #666; margin: 10px 0 0 0;">
                        <i class="bi bi-shield-check"></i> Your payment information is secure
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
