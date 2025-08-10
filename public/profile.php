<?php
session_start();

include "../includes/auth.php";
include "../includes/User.php";
include "../includes/csrf.php";
include "../includes/db.php";
include "../includes/messages.php";
include "../includes/Cart.php";

requireLogin();
$csrf_token = generateCSRFToken();

$userObj = new User($pdo);
$userData = $userObj->getUserById($_SESSION['user_id']);

$cart = new Cart($pdo);
$cartCount = $cart->getCartCount($_SESSION['user_id']);

$activeTab = $_GET['tab'] ?? 'profile';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>My Account - Profile</title>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <h1><a href="products">Store</a></h1>
                <nav class="main-nav">
                    <a href="products"><i class="bi bi-shop"></i> Products</a>
                    
                    <div class="cart-nav">
                        <a href="cart">
                            <i class="bi bi-cart"></i> Cart
                            <?php if ($cartCount > 0): ?>
                                <span class="cart-badge"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <a href="profile" class="active"><i class="bi bi-person"></i> Profile</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin"><i class="bi bi-gear"></i> Admin</a>
                    <?php endif; ?>
                    <a href="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h2><?php echo htmlspecialchars($userData['username']); ?></h2>
                    <p class="user-email"><?php echo htmlspecialchars($userData['email'] ?? 'No email set'); ?></p>
                </div>
                
                <nav class="profile-nav">
                    <a href="?tab=profile" class="<?php echo $activeTab === 'profile' ? 'active' : ''; ?>">
                        <i class="bi bi-person"></i> Personal Info
                    </a>
                    <a href="?tab=orders" class="<?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <i class="bi bi-box"></i> Order History
                    </a>
                    <a href="?tab=addresses" class="<?php echo $activeTab === 'addresses' ? 'active' : ''; ?>">
                        <i class="bi bi-geo-alt"></i> Addresses
                    </a>
                    <a href="?tab=security" class="<?php echo $activeTab === 'security' ? 'active' : ''; ?>">
                        <i class="bi bi-shield-lock"></i> Security
                    </a>
                    <a href="?tab=settings" class="<?php echo $activeTab === 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </nav>
            </div>
            
            <div class="profile-content">
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        <i class="bi bi-exclamation-circle"></i>
                        <?php echo getErrorMessage($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <i class="bi bi-check-circle"></i>
                        <?php echo getSuccessMessage($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'profile'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-person"></i> Personal Information</h1>
                        
                        <form action="../src/update_profile.php" method="POST" class="profile-form">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($userData['username']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="full_name">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="submit-btn">
                                <i class="bi bi-check-lg"></i> Save Changes
                            </button>
                        </form>
                    </div>

                <?php elseif ($activeTab === 'orders'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-box"></i> Order History</h1>
                        
                        <div class="orders-list">
                            <div class="no-orders">
                                <i class="bi bi-box"></i>
                                <h3>No orders yet</h3>
                                <p>Start shopping to see your order history here!</p>
                                <a href="products" class="shop-btn">Browse Products</a>
                            </div>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'addresses'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-geo-alt"></i> Shipping Addresses</h1>
                        
                        <div class="addresses-section">
                            <div class="add-address-card">
                                <i class="bi bi-plus-circle"></i>
                                <h3>Add New Address</h3>
                                <p>Add a shipping address for faster checkout</p>
                                <button class="add-btn">Add Address</button>
                            </div>
                            
                            <div class="no-addresses">
                                <p>No saved addresses yet. Add your first address above!</p>
                            </div>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'security'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-shield-lock"></i> Security Settings</h1>
                        
                        <div class="security-section">
                            <div class="security-card">
                                <h3><i class="bi bi-key"></i> Change Password</h3>
                                <p>Keep your account secure with a strong password</p>
                                
                                <form action="../src/update_password.php" method="POST" class="password-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                    
                                    <div class="form-group">
                                        <label for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" class="submit-btn">
                                        <i class="bi bi-shield-check"></i> Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php elseif ($activeTab === 'settings'): ?>
                    <div class="tab-content">
                        <h1><i class="bi bi-gear"></i> Account Settings</h1>
                        
                        <div class="settings-grid">
                            <div class="setting-card">
                                <h3><i class="bi bi-bell"></i> Notifications</h3>
                                <p>Manage your email preferences</p>
                                
                                <div class="toggle-group">
                                    <label class="toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        Order updates
                                    </label>
                                    
                                    <label class="toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        Promotional emails
                                    </label>
                                    
                                    <label class="toggle">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                        SMS notifications
                                    </label>
                                </div>
                            </div>
                            
                            <div class="setting-card danger">
                                <h3><i class="bi bi-exclamation-triangle"></i> Danger Zone</h3>
                                <p>Irreversible actions</p>
                                <button class="danger-btn">Delete Account</button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
