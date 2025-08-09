<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/Product.php";

$product = new Product($pdo);
$products = $product->getAllProducts();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/products.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Products - Store</title>
</head>
<body>
    <div class="site-header">
        <div class="container">
            <div class="header-content">
                <h1>Store</h1>
                
                <div class="search-container">
                    <input type="text" id="searchInput" placeholder="Search products..." class="search-input">
                    <i class="bi bi-search search-icon"></i>
                </div>
                
                <nav class="main-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="user-info"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                            <a href="admin" class="admin"><i class="bi bi-gear"></i> Admin</a>
                        <?php endif; ?>
                        <a href="profile"><i class="bi bi-person"></i> Profile</a>
                        <a href="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                    <?php else: ?>
                        <a href="login"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <a href="register" class="primary"><i class="bi bi-person-plus"></i> Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </div>

    <div class="container">
        <main>
            <div class="main-content">
                <!-- Filter Sidebar -->
                <aside class="filter-sidebar">
                    <h3><i class="bi bi-funnel"></i> Filters</h3>
                    
                    <div class="filter-group">
                        <label for="categoryFilter">Category</label>
                        <select id="categoryFilter" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Price Range</label>
                        <div class="price-range">
                            <input type="number" id="minPrice" placeholder="Min" min="0" step="0.01">
                            <span>to</span>
                            <input type="number" id="maxPrice" placeholder="Max" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="stockFilter">Availability</label>
                        <select id="stockFilter" class="filter-select">
                            <option value="">All Items</option>
                            <option value="in-stock">In Stock Only</option>
                            <option value="out-of-stock">Out of Stock</option>
                        </select>
                    </div>
                    
                    <button onclick="clearFilters()" class="clear-filters-btn">
                        <i class="bi bi-x-circle"></i> Clear All
                    </button>
                </aside>
                
                <!-- Products Section -->
                <section class="products-section">
                    <div class="section-header">
                        <h2>Our Products</h2>
                        <p id="productCount">Discover amazing products at great prices</p>
                    </div>
                    
                    <?php if (empty($products)): ?>
                        <div class="no-products">
                            <p>No products available yet.</p>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                                <a href="admin.php" class="cta-button">Add Products</a>
                            <?php else: ?>
                                <p>Check back soon for new items!</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="products-grid" id="productsGrid">
                            <?php foreach ($products as $prod): ?>
                                <div class="product-card" 
                                     data-name="<?php echo htmlspecialchars(strtolower($prod['name'])); ?>"
                                     data-description="<?php echo htmlspecialchars(strtolower($prod['description'])); ?>"
                                     data-price="<?php echo $prod['price']; ?>"
                                     data-category="<?php echo htmlspecialchars($prod['category_name'] ?? ''); ?>"
                                     data-stock="<?php echo $prod['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                                    
                                    <?php if (!empty($prod['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="product-image">
                                    <?php else: ?>
                                        <div class="no-image"><i class="bi bi-image"></i> No Image</div>
                                    <?php endif; ?>
                                    
                                    <div class="product-info">
                                        <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                                        <p class="description"><?php echo htmlspecialchars($prod['description']); ?></p>
                                        <p class="price">$<?php echo number_format($prod['price'], 2); ?></p>
                                        <p class="stock">
                                            <?php if ($prod['stock_quantity'] > 0): ?>
                                                <span class="in-stock"><i class="bi bi-check-circle"></i> In Stock (<?php echo $prod['stock_quantity']; ?>)</span>
                                            <?php else: ?>
                                                <span class="out-of-stock"><i class="bi bi-x-circle"></i> Out of Stock</span>
                                            <?php endif; ?>
                                        </p>
                                        
                                        <?php if ($prod['stock_quantity'] > 0): ?>
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <button class="add-to-cart-btn" data-product-id="<?php echo $prod['id']; ?>">
                                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                                </button>
                                            <?php else: ?>
                                                <a href="login.php" class="login-to-buy-btn">Login to Buy</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="out-of-stock-btn" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div id="noResults" class="no-results" style="display: none;">
                            <i class="bi bi-search"></i>
                            <p>No products found matching your criteria.</p>
                            <button onclick="clearFilters()" class="clear-filters-btn">Clear Filters</button>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        const stockFilter = document.getElementById('stockFilter');
        const productsGrid = document.getElementById('productsGrid');
        const noResults = document.getElementById('noResults');
        const productCount = document.getElementById('productCount');

        function filterProducts() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedCategory = categoryFilter.value;
            const minPriceValue = parseFloat(minPrice.value) || 0;
            const maxPriceValue = parseFloat(maxPrice.value) || Infinity;
            const selectedStock = stockFilter.value;
            
            const productCards = productsGrid.querySelectorAll('.product-card');
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.dataset.name;
                const description = card.dataset.description;
                const price = parseFloat(card.dataset.price);
                const category = card.dataset.category;
                const stock = card.dataset.stock;

                let showCard = true;

                if (searchTerm && !name.includes(searchTerm) && !description.includes(searchTerm)) {
                    showCard = false;
                }

                if (selectedCategory && category !== selectedCategory) {
                    showCard = false;
                }

                if (price < minPriceValue || price > maxPriceValue) {
                    showCard = false;
                }

                if (selectedStock && stock !== selectedStock) {
                    showCard = false;
                }

                card.style.display = showCard ? 'block' : 'none';
                if (showCard) visibleCount++;
            });

            if (visibleCount === 0) {
                noResults.style.display = 'block';
                productCount.textContent = 'No products found';
            } else {
                noResults.style.display = 'none';
                productCount.textContent = `Showing ${visibleCount} product${visibleCount !== 1 ? 's' : ''}`;
            }
        }

        function clearFilters() {
            searchInput.value = '';
            categoryFilter.value = '';
            minPrice.value = '';
            maxPrice.value = '';
            stockFilter.value = '';
            filterProducts();
        }

        function addToCart(productId) {
            alert(`Product ${productId} added to cart! (Cart functionality coming soon)`);
        }

        searchInput.addEventListener('input', filterProducts);
        categoryFilter.addEventListener('change', filterProducts);
        minPrice.addEventListener('input', filterProducts);
        maxPrice.addEventListener('input', filterProducts);
        stockFilter.addEventListener('change', filterProducts);

        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                addToCart(productId);
            });
        });

        const totalProducts = document.querySelectorAll('.product-card').length;
        productCount.textContent = `Showing ${totalProducts} product${totalProducts !== 1 ? 's' : ''}`;
    </script>

</body>
</html>
