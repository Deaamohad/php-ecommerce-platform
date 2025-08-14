
<?php
require_once 'db.php';
require_once "Cart.php";

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$isProductsPage = ($currentPage === 'products');
$isProfilePage = ($currentPage === 'profile');
$isCartPage = ($currentPage === 'cart');
$isAdminPage = ($currentPage === 'admin');

$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cart = new Cart($pdo);
    $cartCount = $cart->getCartCount($_SESSION['user_id']);
}
?>

    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <?php if (!$isAdminPage): ?>
                    <h1>
                        <a href="products" class="store-name" style="text-decoration: none; color: inherit;">
                            Store
                        </a>
                    </h1>
                <?php else: ?>
                    <h1><i class="bi bi-shield-check"></i> Admin Dashboard</h1>
                <?php endif; ?>

                <div class="search-container" style="visibility: <?php echo $isProductsPage ? 'visible' : 'hidden'; ?>; height: 48px;">
                    <?php if ($isProductsPage): ?>
                        <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
                        <i class="bi bi-search search-icon"></i>
                    <?php endif; ?>
                </div>
                <nav class="main-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>

                         <a href="products" class="<?php echo $isProductsPage ? 'active' : ''; ?>"><i class="bi bi-shop"></i> Products</a>
                        <div class="cart-nav">
                            <a href="cart" class="<?php echo $isCartPage ? 'active' : ''; ?>">
                                <i class="bi bi-cart"></i> Cart
                                <?php if ($cartCount > 0): ?>
                                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="admin" class="<?php echo ($currentPage === 'admin') ? ' active' : ''; ?>"><i class="bi bi-gear"></i> Admin</a>
                        <?php endif; ?>
                        <a href="profile" class="<?php echo $isProfilePage ? 'active' : ''; ?>"><i class="bi bi-person"></i> Profile</a>
                        <a href="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    <?php else: ?>
                        <a href="login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <a href="register" class="primary"><i class="bi bi-person-plus"></i> Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

