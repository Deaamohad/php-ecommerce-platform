<?php
session_start();
require_once "includes/db.php";
require_once "includes/Cart.php";
require_once "includes/UserAddress.php";
require_once "includes/Settings.php";
require_once "includes/auth.php";
require_once "includes/csrf.php";

requireLogin();

$csrf_token = generateCSRFToken();

$cart = new Cart($pdo);
$userAddress = new UserAddress($pdo);
$settings = new Settings($pdo);
$user_id = $_SESSION['user_id'];
$cartItems = $cart->getCartItems($user_id);
$cartTotal = $cart->getCartTotal($user_id);
$taxRate = $settings->getTaxRate();
$shippingFee = $settings->getShippingFee();
$taxAmount = $settings->calculateTax($cartTotal);
$totalWithTaxAndShipping = $settings->calculateTotal($cartTotal);

$defaultAddress = $userAddress->getDefaultAddress($user_id);
$userAddresses = $userAddress->getUserAddresses($user_id);

if (!isset($_SESSION['payment_method'])) {
    $_SESSION['payment_method'] = [
        'method' => 'cod',
        'title' => 'Cash on Delivery',
        'description' => 'Pay when your order arrives'
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header('Location: cart');
        exit();
    }
    
    $_SESSION['payment_method'] = [
        'method' => $_POST['payment_method'],
        'title' => $_POST['payment_title'],
        'description' => $_POST['payment_description']
    ];
    
    // Save bank card details if bank card is selected
    if ($_POST['payment_method'] === 'bank_card' && isset($_POST['card_number'])) {
        $_SESSION['bank_card_details'] = [
            'card_number' => $_POST['card_number'],
            'expiry_date' => $_POST['expiry_date'],
            'cvv' => $_POST['cvv'],
            'card_holder_name' => $_POST['card_holder_name']
        ];
        // Clear CliQ details if switching from CliQ
        unset($_SESSION['cliq_details']);
    } elseif ($_POST['payment_method'] === 'cliq' && isset($_POST['cliq_username'])) {
        // Save CliQ details if CliQ is selected
        $_SESSION['cliq_details'] = [
            'username' => $_POST['cliq_username']
        ];
        // Clear bank card details if switching from bank card
        unset($_SESSION['bank_card_details']);
    } else {
        // Clear both if using other payment methods
        unset($_SESSION['bank_card_details']);
        unset($_SESSION['cliq_details']);
    }
    
    header('Location: cart');
    exit();
}

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

    <?php include 'includes/header.php'; ?>

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
                <h1><i class="bi bi-cart"></i> Shopping Cart (<?php echo $cart->getCartCount($user_id); ?> items)</h1>
                
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
                            <p class="price">Price: JOD <?php echo number_format($item['price'], 2); ?></p>
                            <p class="subtotal">Subtotal: JOD <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: contents;" id="quantityForm<?php echo $item['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="update_quantity" value="1">
                                    <div class="quantity-wrapper">
                                        <span class="quantity-prefix">QTY:</span>
                                        <select name="quantity" id="quantity<?php echo $item['id']; ?>" class="quantity-dropdown" onchange="this.form.submit()">
                                            <?php for($i = 1; $i <= 15; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $item['quantity'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
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
            
            <div class="cart-summary-section">
                <h2><i class="bi bi-receipt"></i> Order Summary</h2>
                
                <div class="summary-row">
                    <span>Items (<?php echo $cart->getCartCount($user_id); ?>):</span>
                    <span>JOD <?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>JOD <?php echo number_format($shippingFee, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Tax (<?php echo round($taxRate * 100, 1); ?>%):</span>
                    <span>JOD <?php echo number_format($taxAmount, 2); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>JOD <?php echo number_format($totalWithTaxAndShipping, 2); ?></span>
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
                            <a href="profile?tab=addresses" class="edit-address-btn">Manage Addresses</a>
                        </div>
                    <?php else: ?>
                        <div class="no-address-warning">
                            <p><i class="bi bi-exclamation-triangle"></i> No shipping address added</p>
                        </div>
                        <a href="profile?tab=addresses" class="add-address-link">Add Address</a>
                    <?php endif; ?>
                </div>
                
                <div class="payment-section">
                    <h4><i class="bi bi-credit-card"></i> Payment Method</h4>
                    <div class="selected-payment-display">
                        <div class="payment-content">
                            <?php if (isset($_SESSION['payment_method']) && $_SESSION['payment_method']['method'] === 'bank_card' && isset($_SESSION['bank_card_details'])): ?>
                                <p><strong><?= htmlspecialchars($_SESSION['payment_method']['title']) ?></strong></p>
                                <p><?= htmlspecialchars($_SESSION['payment_method']['description']) ?></p>
                                <p class="bank-card-details">
                                    <small>Card ending in <?= htmlspecialchars(substr($_SESSION['bank_card_details']['card_number'], -4)) ?></small>
                                </p>
                            <?php elseif (isset($_SESSION['payment_method']) && $_SESSION['payment_method']['method'] === 'cliq' && isset($_SESSION['cliq_details'])): ?>
                                <p><strong><?= htmlspecialchars($_SESSION['payment_method']['title']) ?></strong></p>
                                <p><?= htmlspecialchars($_SESSION['payment_method']['description']) ?></p>
                                <p class="cliq-details">
                                    <small>To: <?= htmlspecialchars($_SESSION['cliq_details']['username']) ?></small>
                                </p>
                            <?php else: ?>
                                <p><strong id="selectedPaymentMethod"><?= htmlspecialchars($_SESSION['payment_method']['title'] ?? 'Not selected') ?></strong></p>
                                <p id="selectedPaymentDescription"><?= htmlspecialchars($_SESSION['payment_method']['description'] ?? 'Please select a payment method') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a class="edit-address-btn" onclick="openPaymentModal()">Change Payment</a>
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
                                <span class="item-price">JOD <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-modal">
                        <div class="modal-summary-row">
                            <span>Subtotal:</span>
                            <span>JOD <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        <div class="modal-summary-row">
                            <span>Shipping:</span>
                            <span>JOD <?= number_format($shippingFee, 2) ?></span>
                        </div>
                        <div class="modal-summary-row">
                            <span>Tax (<?= round($taxRate * 100, 1) ?>%):</span>
                            <span>JOD <?= number_format($taxAmount, 2) ?></span>
                        </div>
                        <div class="modal-summary-row total">
                            <strong>Total:</strong>
                            <strong>JOD <?= number_format($totalWithTaxAndShipping, 2) ?></strong>
                        </div>
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
                        <a href="profile?tab=addresses" class="edit-address-btn">Manage Addresses</a>
                        

                    <?php else: ?>
                        <div class="no-address">
                            <p class="no-address-warning"><i class="bi bi-exclamation-triangle"></i> No shipping address found</p>
                        </div>
                        <a href="profile?tab=addresses" style="margin-top: 0px;" class="add-address-link">Add Address</a>
                    <?php endif; ?>
                </div>

                <div class="payment-info-modal">
                    <h3><i class="bi bi-credit-card"></i> Payment Method</h3>
                    <div class="payment-display">
                        <?php if (isset($_SESSION['payment_method']) && $_SESSION['payment_method']['method'] === 'bank_card' && isset($_SESSION['bank_card_details'])): ?>
                            <p><strong><?= htmlspecialchars($_SESSION['payment_method']['title']) ?></strong></p>
                            <p><?= htmlspecialchars($_SESSION['payment_method']['description']) ?></p>
                            <p class="bank-card-details">
                                <small>Card ending in <?= htmlspecialchars(substr($_SESSION['bank_card_details']['card_number'], -4)) ?></small>
                            </p>
                        <?php elseif (isset($_SESSION['payment_method']) && $_SESSION['payment_method']['method'] === 'cliq' && isset($_SESSION['cliq_details'])): ?>
                            <p><strong><?= htmlspecialchars($_SESSION['payment_method']['title']) ?></strong></p>
                            <p><?= htmlspecialchars($_SESSION['payment_method']['description']) ?></p>
                            <p class="cliq-details">
                                <small>To: <?= htmlspecialchars($_SESSION['cliq_details']['username']) ?></small>
                            </p>
                        <?php else: ?>
                            <p><strong id="modalPaymentMethod"><?= htmlspecialchars($_SESSION['payment_method']['title'] ?? 'Not selected') ?></strong></p>
                            <p id="modalPaymentDescription"><?= htmlspecialchars($_SESSION['payment_method']['description'] ?? 'Please select a payment method') ?></p>
                        <?php endif; ?>
                    </div>
                    <a class="edit-address-btn" onclick="openPaymentModal()">Change Payment</a>
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


    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="bi bi-credit-card"></i> Select Payment Method</h2>
                <span class="close" onclick="closePaymentModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <div class="payment-options">
                    <div class="payment-option" onclick="selectPayment('cod', 'Cash on Delivery', 'Pay when your order arrives')">
                        <div class="payment-option-icon">
                            <i class="bi bi-cash"></i>
                        </div>
                        <div class="payment-option-info">
                            <h4>Cash on Delivery</h4>
                            <p>Pay when your order arrives</p>
                        </div>
                        <div class="payment-option-radio">
                            <input type="radio" name="payment_method" value="cod" <?= ($_SESSION['payment_method']['method'] ?? 'cod') === 'cod' ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('bank_card', 'Bank Card', 'Pay with your debit or credit card')">
                        <div class="payment-option-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="payment-option-info">
                            <h4>Bank Card</h4>
                            <p>Pay with your debit or credit card</p>
                        </div>
                        <div class="payment-option-radio">
                            <input type="radio" name="payment_method" value="bank_card" <?= ($_SESSION['payment_method']['method'] ?? 'cod') === 'bank_card' ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="payment-option" onclick="selectPayment('cliq', 'CliQ', 'Instant payment via CliQ')">
                        <div class="payment-option-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="payment-option-info">
                            <h4>CliQ</h4>
                            <p>Instant payment via CliQ</p>
                        </div>
                        <div class="payment-option-radio">
                            <input type="radio" name="payment_method" value="cliq" <?= ($_SESSION['payment_method']['method'] ?? 'cod') === 'cliq' ? 'checked' : '' ?>>
                        </div>
                    </div>
                </div>

                <div id="bankCardForm" class="bank-card-form" style="display: none;">
                    <h4>Card Details</h4>
                    <?php if (isset($_SESSION['bank_card_details'])): ?>
                        <p class="existing-card-notice">
                            <i class="bi bi-info-circle"></i> 
                            You have existing card details. You can edit them below.
                        </p>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="cardNumber">Card Number</label>
                        <input type="text" id="cardNumber" name="card_number" maxlength="19" placeholder="1234 5678 9012 3456" oninput="formatCardNumber(this)" onblur="validateCardNumber(this)" value="<?= isset($_SESSION['bank_card_details']['card_number']) ? htmlspecialchars($_SESSION['bank_card_details']['card_number']) : '' ?>">
                        <span class="error-message" id="cardNumberError"></span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="text" id="expiryDate" name="expiry_date" maxlength="5" placeholder="MM/YY" oninput="formatExpiryDate(this)" onblur="validateExpiryDate(this)" value="<?= isset($_SESSION['bank_card_details']['expiry_date']) ? htmlspecialchars($_SESSION['bank_card_details']['expiry_date']) : '' ?>">
                            <span class="error-message" id="expiryDateError"></span>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" maxlength="4" placeholder="123" oninput="formatCVV(this)" onblur="validateCVV(this)" value="<?= isset($_SESSION['bank_card_details']['cvv']) ? htmlspecialchars($_SESSION['bank_card_details']['cvv']) : '' ?>">
                            <span class="error-message" id="cvvError"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cardHolderName">Card Holder Name</label>
                        <input type="text" id="cardHolderName" name="card_holder_name" placeholder="John Doe" onblur="validateCardHolderName(this)" value="<?= isset($_SESSION['bank_card_details']['card_holder_name']) ? htmlspecialchars($_SESSION['bank_card_details']['card_holder_name']) : '' ?>">
                        <span class="error-message" id="cardHolderNameError"></span>
                    </div>
                </div>

                <div id="cliqForm" class="cliq-form" style="display: none;">
                    <h4>CliQ Payment Request</h4>
                    <div class="cliq-instructions">
                        <p><i class="bi bi-info-circle"></i> We'll send a payment request to your CliQ account:</p>
                        <ol>
                            <li>Enter your CliQ username or phone number</li>
                            <li>We'll send you a payment request</li>
                            <li>Open your bank app and approve the payment</li>
                            <li>Payment will be processed instantly</li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label for="cliqUsername">CliQ Username or Phone Number</label>
                        <input type="text" id="cliqUsername" name="cliq_username" placeholder="username or 07XXXXXXXX" onblur="validateCliQUsername(this)">
                        <span class="error-message" id="cliqUsernameError"></span>
                    </div>
                    
                    <div class="cliq-amount">
                        <label>Payment Amount:</label>
                        <div class="amount-display">
                            <span class="amount">JOD <?= number_format($totalWithTaxAndShipping, 2) ?></span>
                        </div>
                    </div>
                    
                    <div class="cliq-note">
                        <p><i class="bi bi-shield-check"></i> Your payment request will be sent securely</p>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="confirmPaymentSelection()">
                        Confirm Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedPaymentMethod = '<?= $_SESSION['payment_method']['method'] ?? 'cod' ?>';
        let selectedPaymentTitle = '<?= addslashes($_SESSION['payment_method']['title'] ?? 'Cash on Delivery') ?>';
        let selectedPaymentDesc = '<?= addslashes($_SESSION['payment_method']['description'] ?? 'Pay when your order arrives') ?>';
        
        // Initialize bank card form visibility and visual selection
        document.addEventListener('DOMContentLoaded', function() {
            if (selectedPaymentMethod === 'bank_card') {
                document.getElementById('bankCardForm').style.display = 'block';
                document.getElementById('cliqForm').style.display = 'none';
                
                // Format existing bank card values if they exist
                const cardNumber = document.getElementById('cardNumber');
                const expiryDate = document.getElementById('expiryDate');
                const cvv = document.getElementById('cvv');
                
                if (cardNumber.value && cardNumber.value.length >= 4) {
                    formatCardNumber(cardNumber);
                }
                if (expiryDate.value && expiryDate.value.length >= 2) {
                    formatExpiryDate(expiryDate);
                }
                if (cvv.value && cvv.value.length >= 3) {
                    formatCVV(cvv);
                }
            } else if (selectedPaymentMethod === 'cliq') {
                document.getElementById('bankCardForm').style.display = 'none';
                document.getElementById('cliqForm').style.display = 'block';
            }
            
            // Set visual selection for current payment method
            const currentOption = document.querySelector(`.payment-option input[value="${selectedPaymentMethod}"]`);
            if (currentOption) {
                currentOption.closest('.payment-option').classList.add('selected');
            }
        });

        function openPaymentModal() {
            document.getElementById('paymentModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Format existing bank card values if they exist
            if (selectedPaymentMethod === 'bank_card') {
                const cardNumber = document.getElementById('cardNumber');
                const expiryDate = document.getElementById('expiryDate');
                const cvv = document.getElementById('cvv');
                const cardHolderName = document.getElementById('cardHolderName');
                
                // Format card number with spaces if it exists
                if (cardNumber.value && cardNumber.value.length >= 4) {
                    formatCardNumber(cardNumber);
                }
                
                // Format expiry date if it exists
                if (expiryDate.value && expiryDate.value.length >= 2) {
                    formatExpiryDate(expiryDate);
                }
                
                // Format CVV if it exists
                if (cvv.value && cvv.value.length >= 3) {
                    formatCVV(cvv);
                }
            }
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function selectPayment(method, title, description) {
            selectedPaymentMethod = method;
            selectedPaymentTitle = title;
            selectedPaymentDesc = description;
            
            // Update radio button
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.checked = radio.value === method;
            });
            
            // Update visual selection
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            if (method === 'bank_card') {
                document.getElementById('bankCardForm').style.display = 'block';
                document.getElementById('cliqForm').style.display = 'none';
                
                // Format existing values if they exist
                const cardNumber = document.getElementById('cardNumber');
                const expiryDate = document.getElementById('expiryDate');
                const cvv = document.getElementById('cvv');
                
                if (cardNumber.value && cardNumber.value.length >= 4) {
                    formatCardNumber(cardNumber);
                }
                if (expiryDate.value && expiryDate.value.length >= 2) {
                    formatExpiryDate(expiryDate);
                }
                if (cvv.value && cvv.value.length >= 3) {
                    formatCVV(cvv);
                }
            } else if (method === 'cliq') {
                document.getElementById('bankCardForm').style.display = 'none';
                document.getElementById('cliqForm').style.display = 'block';
            } else {
                document.getElementById('bankCardForm').style.display = 'none';
                document.getElementById('cliqForm').style.display = 'none';
            }
        }

        function confirmPaymentSelection() {
            if (selectedPaymentMethod === 'bank_card') {
                const cardNumber = document.getElementById('cardNumber');
                const expiryDate = document.getElementById('expiryDate');
                const cvv = document.getElementById('cvv');
                const cardHolderName = document.getElementById('cardHolderName');
                
                // Show error messages for empty required fields
                if (cardNumber.value.trim() === '') {
                    const errorElement = document.getElementById('cardNumberError');
                    errorElement.textContent = 'Card number is required';
                    errorElement.style.display = 'block';
                    cardNumber.classList.add('error');
                }
                if (expiryDate.value.trim() === '') {
                    const errorElement = document.getElementById('expiryDateError');
                    errorElement.textContent = 'Expiry date is required';
                    errorElement.style.display = 'block';
                    expiryDate.classList.add('error');
                }
                if (cvv.value.trim() === '') {
                    const errorElement = document.getElementById('cvvError');
                    errorElement.textContent = 'CVV is required';
                    errorElement.style.display = 'block';
                    cvv.classList.add('error');
                }
                if (cardHolderName.value.trim() === '') {
                    const errorElement = document.getElementById('cardHolderNameError');
                    errorElement.textContent = 'Card holder name is required';
                    errorElement.style.display = 'block';
                    cardHolderName.classList.add('error');
                }
                
                const isCardNumberValid = validateCardNumber(cardNumber);
                const isExpiryDateValid = validateExpiryDate(expiryDate);
                const isCVVValid = validateCVV(cvv);
                const isCardHolderNameValid = validateCardHolderName(cardHolderName);
                
                if (!isCardNumberValid || !isExpiryDateValid || !isCVVValid || !isCardHolderNameValid) {
                    return;
                }
            } else if (selectedPaymentMethod === 'cliq') {
                const cliqUsername = document.getElementById('cliqUsername');
                
                if (cliqUsername.value.trim() === '') {
                    const errorElement = document.getElementById('cliqUsernameError');
                    errorElement.textContent = 'CliQ username or phone number is required';
                    errorElement.style.display = 'block';
                    cliqUsername.classList.add('error');
                    return;
                }
                
                const isCliQUsernameValid = validateCliQUsername(cliqUsername);
                if (!isCliQUsernameValid) {
                    return;
                }
            }
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?php echo $csrf_token; ?>';
            
            const updateInput = document.createElement('input');
            updateInput.type = 'hidden';
            updateInput.name = 'update_payment';
            updateInput.value = '1';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = 'payment_method';
            methodInput.value = selectedPaymentMethod;
            
            const titleInput = document.createElement('input');
            titleInput.type = 'hidden';
            titleInput.name = 'payment_title';
            titleInput.value = selectedPaymentTitle;
            
            const descInput = document.createElement('input');
            descInput.type = 'hidden';
            descInput.name = 'payment_description';
            descInput.value = selectedPaymentDesc;
            
            // Add bank card details if bank card is selected
            if (selectedPaymentMethod === 'bank_card') {
                const cardNumberInput = document.createElement('input');
                cardNumberInput.type = 'hidden';
                cardNumberInput.name = 'card_number';
                cardNumberInput.value = document.getElementById('cardNumber').value.replace(/\s/g, '');
                
                const expiryDateInput = document.createElement('input');
                expiryDateInput.type = 'hidden';
                expiryDateInput.name = 'expiry_date';
                expiryDateInput.value = document.getElementById('expiryDate').value;
                
                const cvvInput = document.createElement('input');
                cvvInput.type = 'hidden';
                cvvInput.name = 'cvv';
                cvvInput.value = document.getElementById('cvv').value;
                
                const cardHolderNameInput = document.createElement('input');
                cardHolderNameInput.type = 'hidden';
                cardHolderNameInput.name = 'card_holder_name';
                cardHolderNameInput.value = document.getElementById('cardHolderName').value;
                
                form.appendChild(cardNumberInput);
                form.appendChild(expiryDateInput);
                form.appendChild(cvvInput);
                form.appendChild(cardHolderNameInput);
            } else if (selectedPaymentMethod === 'cliq') {
                // Add CliQ username if CliQ is selected
                const cliqUsernameInput = document.createElement('input');
                cliqUsernameInput.type = 'hidden';
                cliqUsernameInput.name = 'cliq_username';
                cliqUsernameInput.value = document.getElementById('cliqUsername').value.trim();
                
                form.appendChild(cliqUsernameInput);
            }
            
            form.appendChild(csrfInput);
            form.appendChild(updateInput);
            form.appendChild(methodInput);
            form.appendChild(titleInput);
            form.appendChild(descInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        function formatCardNumber(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 16) value = value.slice(0, 16);
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            
            input.value = formatted;
        }

        function validateCardNumber(input) {
            const value = input.value.replace(/\s/g, '');
            const errorElement = document.getElementById('cardNumberError');
            
            if (value.length === 0) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return false;
            } else if (value.length < 13 || value.length > 19) {
                errorElement.textContent = 'Card number must be 13-19 digits';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else if (!/^\d+$/.test(value)) {
                errorElement.textContent = 'Card number must contain only digits';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
        }

        function formatExpiryDate(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 4) value = value.slice(0, 4);
            
            if (value.length >= 2) {
                const month = parseInt(value.slice(0, 2));
                if (month > 12) value = '12' + value.slice(2);
            }
            
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            
            input.value = value;
        }

        function validateExpiryDate(input) {
            const value = input.value;
            const errorElement = document.getElementById('expiryDateError');
            
            if (value.length === 0) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return false;
            } else if (!/^\d{2}\/\d{2}$/.test(value)) {
                errorElement.textContent = 'Use MM/YY format';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else {
                const [month, year] = value.split('/');
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear() % 100;
                const currentMonth = currentDate.getMonth() + 1;
                
                if (parseInt(month) < 1 || parseInt(month) > 12) {
                    errorElement.textContent = 'Invalid month';
                    errorElement.style.display = 'block';
                    input.classList.add('error');
                    return false;
                } else if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                    errorElement.textContent = 'Card has expired';
                    errorElement.style.display = 'block';
                    input.classList.add('error');
                    return false;
                } else {
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                    input.classList.remove('error');
                    return true;
                }
            }
        }

        function formatCVV(input) {
            const value = input.value.replace(/\D/g, '');
            if (value.length > 4) value = value.slice(0, 4);
            input.value = value;
        }

        function validateCVV(input) {
            const value = input.value.replace(/\D/g, '');
            if (value.length > 4) value = value.slice(0, 4);
            
            input.value = value;
            const errorElement = document.getElementById('cvvError');
            
            if (value.length === 0) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return false;
            } else if (value.length < 3 || value.length > 4) {
                errorElement.textContent = 'CVV must be 3-4 digits';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else if (!/^\d+$/.test(value)) {
                errorElement.textContent = 'CVV must contain only digits';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
        }

        function validateCardHolderName(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('cardHolderNameError');
            
            if (value.length === 0) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return false;
            } else if (value.length < 2) {
                errorElement.textContent = 'Name must be at least 2 characters';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else if (!/^[a-zA-Z\s]+$/.test(value)) {
                errorElement.textContent = 'Name can only contain letters and spaces';
                errorElement.style.display = 'block';
                input.classList.add('error');
                return false;
            } else {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
        }

        function validateCliQUsername(input) {
            const value = input.value.trim();
            const errorElement = document.getElementById('cliqUsernameError');
            
            if (value.length === 0) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
            
            // Check if it's a phone number (starts with 07 and has 10 digits)
            if (/^07\d{8}$/.test(value)) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
            
            // Check if it's a username (alphanumeric, 3-20 characters)
            if (/^[a-zA-Z0-9_]{3,20}$/.test(value)) {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
                input.classList.remove('error');
                return true;
            }
            
            errorElement.textContent = 'Please enter a valid CliQ username or phone number (07XXXXXXXX)';
            errorElement.style.display = 'block';
            input.classList.add('error');
            return false;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const paymentModal = document.getElementById('paymentModal');
            const checkoutModal = document.getElementById('checkoutModal');
            
            if (event.target === paymentModal) {
                closePaymentModal();
            } else if (event.target === checkoutModal) {
                closeCheckoutModal();
            }
        }
    </script>
</body>
</html>
