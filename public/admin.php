<?php
session_start();
require_once "../includes/auth.php";
require_once "../includes/csrf.php";
require_once "../includes/messages.php";
require_once "../includes/db.php";

requireLogin();
requireAdmin();

$csrf_token = generateCSRFToken();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Admin Dashboard</title>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <h1><i class="bi bi-shield-check"></i> Admin Dashboard</h1>
                <nav class="main-nav">
                    <span class="user-info">
                        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    </span>
                    <a href="products.php">
                        <i class="bi bi-shop"></i> Store
                    </a>
                    <a href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
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

        <div class="form-container">
            <h2><i class="bi bi-plus-circle"></i> Add New Product</h2>
            
            <form action="../src/admin/process_add_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter product name" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Enter product description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="filter-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" placeholder="0" required>
                    </div>
                </div>
                
                <div class="image-options">
                    <h3><i class="bi bi-image"></i> Product Image</h3>
                    
                    <div class="form-group">
                        <label for="image_url">Image URL</label>
                        <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                        <small class="url-help">Direct link to an image file online (.jpg, .png, .gif, .webp)</small>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="bi bi-plus-lg"></i> Add Product
                </button>
            </form>
        </div>
    </main>
</body>
</html>

