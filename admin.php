<?php
session_start();
require_once "includes/auth.php";
require_once "includes/csrf.php";
require_once "includes/messages.php";
require_once "includes/db.php";
require_once "includes/Product.php";

requireLogin();
requireAdmin();

$csrf_token = generateCSRFToken();

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$product = new Product($pdo);
$products = $product->getAllProducts();
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

    <?php include 'includes/header.php'; ?>

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
            
            <?php if (isDemoAdmin()): ?>
                <div class="demo-notice">
                    <i class="bi bi-info-circle"></i>
                    <strong>Demo Account Notice:</strong> <?php echo getDemoMessage(); ?>
                </div>
            <?php endif; ?>
            
                            <form action="src/admin/process_add_product.php" method="POST" enctype="multipart/form-data" <?php echo isDemoAdmin() ? 'style="pointer-events: none; opacity: 0.6;"' : ''; ?>>
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
                    
                    <div class="image-tabs">
                        <button type="button" class="tab-btn active" onclick="switchImageTab('upload')">
                            <i class="bi bi-upload"></i> Upload File
                        </button>
                        <button type="button" class="tab-btn" onclick="switchImageTab('url')">
                            <i class="bi bi-link-45deg"></i> Use URL
                        </button>
                    </div>
                    
                    <div id="upload-tab" class="image-tab active">
                        <div class="form-group">
                            <label for="image_file">Choose Image File</label>
                            <div class="custom-file-upload">
                                <input type="file" id="image_file" name="image_file" accept="image/*" onchange="previewImage(event)">
                                <div class="file-upload-btn">
                                    <i class="bi bi-cloud-upload"></i>
                                    Choose File
                                </div>
                            </div>
                            <div id="file-name-display" class="file-name-display" style="display: none;"></div>
                            <small class="file-help">Maximum 5MB. Supports JPEG, PNG, GIF, WebP</small>
                        </div>
                        
                        <div id="image-preview" class="image-preview" style="display: none;">
                            <img id="preview-img" src="" alt="Preview">
                            <button type="button" class="remove-preview" onclick="removePreview()">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div id="url-tab" class="image-tab">
                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="url" id="image_url" name="image_url" placeholder="https://example.com/image.jpg" onchange="previewUrl(event)">
                            <small class="url-help">Direct link to an image file online (.jpg, .png, .gif, .webp)</small>
                        </div>
                        
                        <div id="url-preview" class="image-preview" style="display: none;">
                            <img id="url-preview-img" src="" alt="URL Preview">
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn" <?php echo isDemoAdmin() ? 'disabled title="Demo mode - editing disabled"' : ''; ?>>
                    <i class="bi bi-plus-lg"></i> Add Product
                </button>
            </form>
        </div>

        <div class="settings-section">
            <h2><i class="bi bi-gear"></i> Store Settings</h2>
            
            <?php if (isDemoAdmin()): ?>
                <div class="demo-notice">
                    <i class="bi bi-info-circle"></i>
                    <strong>Demo Account Notice:</strong> <?php echo getDemoMessage(); ?>
                </div>
            <?php endif; ?>
            
            <?php
            require_once "includes/Settings.php";
            $settings = new Settings($pdo);
            $currentTaxRate = $settings->getTaxRate();
            $currentShippingFee = $settings->getShippingFee();
            ?>
            
            <form action="src/admin/process_settings.php" method="POST" class="settings-form" <?php echo isDemoAdmin() ? 'style="pointer-events: none; opacity: 0.6;"' : ''; ?>>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tax_rate">Tax Rate (%)</label>
                        <div class="input-with-icon">
                            <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100" 
                                   value="<?php echo round($currentTaxRate * 100, 2); ?>" required>
                            <span class="input-suffix">%</span>
                        </div>
                        <small class="form-help">Enter as percentage (e.g., 8 for 8%)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="shipping_fee">Shipping Fee (JOD)</label>
                        <div class="input-with-icon">
                            <input type="number" id="shipping_fee" name="shipping_fee" step="0.01" min="0" 
                                   value="<?php echo $currentShippingFee; ?>" required>
                            <span class="input-suffix">JOD</span>
                        </div>
                        <small class="form-help">Fixed shipping cost for all orders</small>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn settings-submit" <?php echo isDemoAdmin() ? 'disabled title="Demo mode - editing disabled"' : ''; ?>>
                    <i class="bi bi-save"></i> Update Settings
                </button>
            </form>
        </div>

        <div class="products-list">
            <h2><i class="bi bi-box-seam"></i> Manage Products</h2>
            
            <?php if (isDemoAdmin()): ?>
                <div class="demo-notice">
                    <i class="bi bi-info-circle"></i>
                    <strong>Demo Account Notice:</strong> <?php echo getDemoMessage(); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($products)): ?>
                <p class="no-products">No products found. Add your first product above!</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card">
                            <?php if (!empty($prod['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($prod['image_url']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="bi bi-image"></i>
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($prod['name']); ?></h3>
                                <p class="price">$<?php echo number_format($prod['price'], 2); ?></p>
                                <p class="stock">Stock: <?php echo $prod['stock_quantity']; ?></p>
                                <p class="category"><?php echo htmlspecialchars($prod['category_name'] ?? 'No Category'); ?></p>
                            </div>
                            
                            <div class="product-actions">
                                <?php if (isDemoAdmin()): ?>
                                    <span class="demo-disabled-btn edit-btn" title="Demo mode - editing disabled">
                                        <i class="bi bi-pencil"></i> Edit
                                    </span>
                                    <span class="demo-disabled-btn delete-btn" title="Demo mode - editing disabled">
                                        <i class="bi bi-trash"></i> Delete
                                    </span>
                                <?php else: ?>
                                    <a href="edit_product.php?id=<?php echo $prod['id']; ?>" class="edit-btn">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" action="src/admin/delete_product.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                        <button type="submit" class="delete-btn">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function switchImageTab(tabName) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.image-tab').forEach(tabEl => tabEl.classList.remove('active'));
            
            document.querySelector(`[onclick="switchImageTab('${tabName}')"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');
            
            if (tabName === 'upload') {
                document.getElementById('image_url').value = '';
                const urlPreview = document.getElementById('url-preview');
                if (urlPreview) urlPreview.style.display = 'none';
            } else {
                document.getElementById('image_file').value = '';
                removePreview();
            }
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const fileNameDisplay = document.getElementById('file-name-display');
            
            if (file) {
                fileNameDisplay.textContent = `Selected: ${file.name}`;
                fileNameDisplay.style.display = 'block';
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size too large. Maximum 5MB allowed.');
                    event.target.value = '';
                    fileNameDisplay.style.display = 'none';
                    return;
                }
                
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPEG, PNG, GIF, and WebP allowed.');
                    event.target.value = '';
                    fileNameDisplay.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                fileNameDisplay.style.display = 'none';
            }
        }

        function removePreview() {
            const preview = document.getElementById('image-preview');
            if (preview) {
                preview.style.display = 'none';
            }
            const fileInput = document.getElementById('image_file');
            if (fileInput) {
                fileInput.value = '';
            }
            const fileNameDisplay = document.getElementById('file-name-display');
            if (fileNameDisplay) {
                fileNameDisplay.style.display = 'none';
            }
        }

        function previewUrl(event) {
            const url = event.target.value;
            if (url) {
                const img = document.getElementById('url-preview-img');
                const preview = document.getElementById('url-preview');
                
                img.onload = function() {
                    preview.style.display = 'block';
                };
                img.onerror = function() {
                    preview.style.display = 'none';
                    alert('Could not load image from URL. Please check the URL and try again.');
                };
                img.src = url;
            } else {
                const preview = document.getElementById('url-preview');
                if (preview) {
                    preview.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            switchImageTab('upload');
        });
    </script>
</body>
</html>
