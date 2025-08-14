<?php
session_start();
require_once "includes/db.php";
require_once "includes/Cart.php";
require_once "includes/UserAddress.php";
require_once "includes/auth.php";
require_once "includes/csrf.php";

requireLogin();

$csrf_token = generateCSRFToken();

$cart = new Cart($pdo);
$userAddress = new UserAddress($pdo);
$user_id = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($user_id);
$cartTotal = $cart->getCartTotal($user_id);

$defaultAddress = $userAddress->getDefaultAddress($user_id);
$userAddresses = $userAddress->getUserAddresses($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header('Location: cart');
        exit();
    }
    
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
    <link rel="stylesheet" type="text/css" href="css/cart-modal.css">
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
                            <div class="product-image-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="price">Price: $<?php echo number_format($item['price'], 2); ?></p>
                            <p class="subtotal">Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">
                                    <button type="submit" name="update_quantity" class="quantity-btn">−</button>
                                </form>
                                
                                <div class="quantity-display"><?php echo $item['quantity']; ?></div>
                                
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                    <button type="submit" name="update_quantity" class="quantity-btn">+</button>
                                </form>
                                
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
                    <?php if (!empty($userAddresses)): ?>
                        <div class="address-selection-sidebar">
                            <label for="sidebarAddressSelector">Choose Address:</label>
                            <select id="sidebarAddressSelector" class="address-selector-sidebar" onchange="updateSidebarAddress()">
                                <?php foreach ($userAddresses as $address): ?>
                                    <option value="<?= $address['id'] ?>" 
                                            data-full-name="<?= htmlspecialchars($address['full_name']) ?>"
                                            data-street="<?= htmlspecialchars($address['street_address']) ?>"
                                            data-city="<?= htmlspecialchars($address['city']) ?>"
                                            data-phone="<?= htmlspecialchars($address['phone']) ?>"
                                            <?= $address['is_default'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($address['title']) ?> - <?= htmlspecialchars($address['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="selected-address-display" id="sidebarSelectedAddress">
                            <?php 
                            $displayAddress = $defaultAddress ?: $userAddresses[0];
                            ?>
                            <p><strong><?= htmlspecialchars($displayAddress['full_name']) ?></strong></p>
                            <p><?= htmlspecialchars($displayAddress['street_address']) ?></p>
                            <p><?= htmlspecialchars($displayAddress['city']) ?></p>
                            <p><?= htmlspecialchars($displayAddress['phone']) ?></p>
                        </div>
                        
                        <div class="address-actions-sidebar">
                            <a href="profile?tab=addresses" class="edit-link">
                                <i class="bi bi-plus"></i> Manage Addresses
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="no-address-warning">
                            <p><i class="bi bi-exclamation-triangle"></i> No shipping address added</p>
                            <a href="profile?tab=addresses" class="add-address-link">
                                <i class="bi bi-plus"></i> Add Address
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-section">
                    <h4><i class="bi bi-credit-card"></i> Payment Method</h4>
                    <p>Visa ending in 1234</p>
                    <p>Expires 12/25</p>
                    <a href="#" class="edit-link">Change Payment</a>
                </div>
                
                <div class="checkout-section">
                    <button class="checkout-btn" onclick="openCheckoutModal()">
                        <i class="bi bi-credit-card"></i> Proceed to Checkout
                    </button>
                    <p class="modal-footer-text">
                        <i class="bi bi-shield-check"></i> Your payment information is secure
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="checkoutModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="bi bi-credit-card"></i> Confirm Your Order</h2>
                <span class="close" onclick="closeCheckoutModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <div class="order-summary-modal">
                    <h3><i class="bi bi-list-ul"></i> Order Summary</h3>
                    <div class="order-items-modal">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item-modal">
                                <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="item-qty">x<?= $item['quantity'] ?></span>
                                <span class="item-price">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-modal">
                        <strong>Total: $<?= number_format($cartTotal, 2) ?></strong>
                    </div>
                </div>

                <div class="shipping-info-modal">
                    <h3><i class="bi bi-truck"></i> Shipping To</h3>
                    <?php if (!empty($userAddresses)): ?>
                        <div class="address-selection">
                            <label for="selectedAddressId">Choose Shipping Address:</label>
                            <select id="selectedAddressId" class="address-selector" onchange="updateSelectedAddress()">
                                <?php foreach ($userAddresses as $address): ?>
                                    <option value="<?= $address['id'] ?>" 
                                            data-full-name="<?= htmlspecialchars($address['full_name']) ?>"
                                            data-street="<?= htmlspecialchars($address['street_address']) ?>"
                                            data-city="<?= htmlspecialchars($address['city']) ?>"
                                            data-phone="<?= htmlspecialchars($address['phone']) ?>"
                                            <?= $address['is_default'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($address['title']) ?> - <?= htmlspecialchars($address['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="address-display" id="selectedAddress">
                            <?php 
                            $displayAddress = $defaultAddress ?: $userAddresses[0];
                            ?>
                            <p><strong><?= htmlspecialchars($displayAddress['full_name']) ?></strong></p>
                            <p><?= htmlspecialchars($displayAddress['street_address']) ?></p>
                            <p><?= htmlspecialchars($displayAddress['city']) ?></p>
                            <p><?= htmlspecialchars($displayAddress['phone']) ?></p>
                        </div>
                        
                        <div class="address-actions">
                            <button type="button" class="edit-address-btn" onclick="showAddressForm()">
                                <i class="bi bi-pencil"></i> Edit Selected Address
                            </button>
                            <a href="profile?tab=addresses" class="edit-address-btn">
                                <i class="bi bi-plus"></i> Add New Address
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="no-address">
                            <p class="no-address-warning"><i class="bi bi-exclamation-triangle"></i> No shipping address found</p>
                            <a href="profile?tab=addresses" class="edit-address-btn">
                                <i class="bi bi-plus"></i> Add Address
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="payment-info-modal">
                    <h3><i class="bi bi-credit-card"></i> Payment Method</h3>
                    <div class="payment-display">
                        <p><i class="bi bi-credit-card"></i> Visa ending in 1234</p>
                        <p>Expires 12/25</p>
                    </div>
                    <button type="button" class="edit-payment-btn" onclick="showPaymentForm()">
                        <i class="bi bi-pencil"></i> Change Payment
                    </button>
                </div>

                <div id="addressForm" class="address-form-modal" style="display: none;">
                    <h3><i class="bi bi-geo-alt"></i> <?= $defaultAddress ? 'Update' : 'Add' ?> Shipping Address</h3>
                    <form id="addressFormElement">
                        <div class="form-group">
                            <label for="address_title">Address Title *</label>
                            <input type="text" id="address_title" value="<?= $defaultAddress ? htmlspecialchars($defaultAddress['title']) : 'Home' ?>" required placeholder="Home, Work, etc.">
                        </div>
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" value="<?= $defaultAddress ? htmlspecialchars($defaultAddress['full_name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="street_address">Street Address *</label>
                            <input type="text" id="street_address" value="<?= $defaultAddress ? htmlspecialchars($defaultAddress['street_address']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" value="<?= $defaultAddress ? htmlspecialchars($defaultAddress['city']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address_phone">Phone Number *</label>
                            <input type="tel" id="address_phone" value="<?= $defaultAddress ? htmlspecialchars($defaultAddress['phone']) : '' ?>" required>
                        </div>
                        <div class="form-actions">
                            <button type="button" onclick="cancelAddressEdit()">Cancel</button>
                            <button type="button" onclick="saveAddress()"><?= $defaultAddress ? 'Update' : 'Add' ?></button>
                        </div>
                    </form>
                </div>

                <div id="paymentForm" class="payment-form-modal" style="display: none;">
                    <h3><i class="bi bi-credit-card"></i> Update Payment Method</h3>
                    <form>
                        <div class="form-group">
                            <label for="card_number">Card Number *</label>
                            <input type="text" id="card_number" placeholder="1234 5678 9012 3456" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiry *</label>
                                <input type="text" id="expiry" placeholder="MM/YY" required>
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV *</label>
                                <input type="text" id="cvv" placeholder="123" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" onclick="cancelPaymentEdit()">Cancel</button>
                            <button type="button" onclick="savePayment()">Save</button>
                        </div>
                    </form>
                </div>

                <form method="POST" action="#" onsubmit="return false;" class="checkout-form-modal">
                    <?php 
                    $displayAddress = $defaultAddress ?: (!empty($userAddresses) ? $userAddresses[0] : null);
                    ?>
                    <?php if ($displayAddress): ?>
                        <input type="hidden" name="shipping_address" id="hiddenShippingAddress" value="<?= htmlspecialchars($displayAddress['full_name'] . ', ' . $displayAddress['street_address'] . ', ' . $displayAddress['city'] . ', ' . $displayAddress['phone']) ?>">
                        <input type="hidden" name="address_id" id="hiddenAddressId" value="<?= $displayAddress['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="shipping_address" id="hiddenShippingAddress" value="">
                        <input type="hidden" name="address_id" id="hiddenAddressId" value="">
                    <?php endif; ?>
                    <input type="hidden" name="payment_method" value="Cash on Delivery">
                    
                    <div class="form-group">
                        <label for="notes">Order Notes (Optional)</label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            rows="2" 
                            placeholder="Special delivery instructions or notes"
                        ></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeCheckoutModal()">
                            Cancel
                        </button>
                        <?php if (!empty($userAddresses)): ?>
                            <button type="button" class="btn btn-primary" disabled>
                                <i class="bi bi-info-circle"></i> Checkout Currently Disabled
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" onclick="window.location.href='profile?tab=addresses'" disabled>
                                <i class="bi bi-exclamation-triangle"></i> Add Address First
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        function openCheckoutModal() {
            const sidebarSelector = document.getElementById('sidebarAddressSelector');
            const modalSelector = document.getElementById('selectedAddressId');
            
            if (sidebarSelector && modalSelector) {
                modalSelector.value = sidebarSelector.value;
                updateSelectedAddress();
            }
            
            document.getElementById('checkoutModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeCheckoutModal() {
            document.getElementById('checkoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
            hideAllForms();
        }

        function hideAllForms() {
            document.getElementById('addressForm').style.display = 'none';
            document.getElementById('paymentForm').style.display = 'none';
            document.getElementById('addressSelector').style.display = 'none';
        }

        function showAddressForm() {
            hideAllForms();
            document.getElementById('addressForm').style.display = 'block';
        }

        function updateSelectedAddress() {
            const selector = document.getElementById('selectedAddressId');
            const selectedOption = selector.options[selector.selectedIndex];
            
            if (selectedOption) {
                const fullName = selectedOption.getAttribute('data-full-name');
                const streetAddress = selectedOption.getAttribute('data-street');
                const city = selectedOption.getAttribute('data-city');
                const phone = selectedOption.getAttribute('data-phone');
                const addressId = selectedOption.value;
                
                document.getElementById('selectedAddress').innerHTML = `
                    <p><strong>${fullName}</strong></p>
                    <p>${streetAddress}</p>
                    <p>${city}</p>
                    <p>${phone}</p>
                `;
                
                document.getElementById('hiddenShippingAddress').value = `${fullName}, ${streetAddress}, ${city}, ${phone}`;
                document.getElementById('hiddenAddressId').value = addressId;
            }
        }

        function updateSidebarAddress() {
            const selector = document.getElementById('sidebarAddressSelector');
            const selectedOption = selector.options[selector.selectedIndex];
            
            if (selectedOption) {
                const fullName = selectedOption.getAttribute('data-full-name');
                const streetAddress = selectedOption.getAttribute('data-street');
                const city = selectedOption.getAttribute('data-city');
                const phone = selectedOption.getAttribute('data-phone');
                
                document.getElementById('sidebarSelectedAddress').innerHTML = `
                    <p><strong>${fullName}</strong></p>
                    <p>${streetAddress}</p>
                    <p>${city}</p>
                    <p>${phone}</p>
                `;
                
                const modalSelector = document.getElementById('selectedAddressId');
                if (modalSelector) {
                    modalSelector.value = selectedOption.value;
                    updateSelectedAddress();
                }
            }
        }

        function showPaymentForm() {
            hideAllForms();
            document.getElementById('paymentForm').style.display = 'block';
        }

        function cancelAddressEdit() {
            document.getElementById('addressForm').style.display = 'none';
        }

        function cancelPaymentEdit() {
            document.getElementById('paymentForm').style.display = 'none';
        }

        function saveAddress() {
            const title = document.getElementById('address_title').value;
            const fullName = document.getElementById('full_name').value;
            const streetAddress = document.getElementById('street_address').value;
            const city = document.getElementById('city').value;
            const phone = document.getElementById('address_phone').value;
            
            if (title && fullName && streetAddress && city && phone) {
                document.getElementById('selectedAddress').innerHTML = `
                    <p><strong>${fullName}</strong></p>
                    <p>${streetAddress}</p>
                    <p>${city}</p>
                    <p>${phone}</p>
                `;
                
                document.getElementById('hiddenShippingAddress').value = `${fullName}, ${streetAddress}, ${city}, ${phone}`;
                
                const submitBtn = document.querySelector('.btn-primary[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Place Order ($<?= number_format($cartTotal, 2) ?>)';
                }
                
                cancelAddressEdit();
                
                fetch('../src/save_address.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: title,
                        full_name: fullName,
                        street_address: streetAddress,
                        city: city,
                        phone: phone,
                        is_default: true
                    })
                });
            }
        }

        function savePayment() {
            const cardNumber = document.getElementById('card_number').value;
            const expiry = document.getElementById('expiry').value;
            
            if (cardNumber && expiry) {
                const lastFour = cardNumber.slice(-4);
                document.querySelector('.payment-display').innerHTML = `
                    <p><i class="bi bi-credit-card"></i> Card ending in ${lastFour}</p>
                    <p>Expires ${expiry}</p>
                `;
                document.querySelector('input[name="payment_method"]').value = `Card ending in ${lastFour}`;
                cancelPaymentEdit();
            }
        }

        window.onclick = function(event) {
            const modal = document.getElementById('checkoutModal');
            if (event.target === modal) {
                closeCheckoutModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeCheckoutModal();
            }
        });
    </script>
</body>
</html>
