<?php
session_start();
require_once "includes/auth.php";
require_once "includes/csrf.php";
require_once "includes/messages.php";
require_once "includes/db.php";
require_once "includes/Product.php";

requireLogin();
requireAdmin();

if (isDemoAdmin()) {
    header('Location: admin?error=demo_mode_edit_disabled');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin?error=invalid_product');
    exit();
}

$product_id = $_GET['id'];
$product = new Product($pdo);
$productData = $product->getProductById($product_id);

if (!$productData) {
    header('Location: admin?error=product_not_found');
    exit();
}

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
    <title>Edit Product - Admin</title>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <h1><i class="bi bi-pencil-square"></i> Edit Product</h1>
                <nav class="main-nav">
                                    <a href="admin">
                    <i class="bi bi-arrow-left"></i> Back to Admin
                </a>
                <a href="products">
                    <i class="bi bi-shop"></i> Store
                </a>
                <a href="logout">
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
        
        <div class="form-container">
            <h2><i class="bi bi-pencil"></i> Edit: <?php echo htmlspecialchars($productData['name']); ?></h2>
            
                            <form action="src/admin/process_edit_product.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="product_id" value="<?php echo $productData['id']; ?>">
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($productData['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($productData['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="filter-select" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($productData['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (JOD)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" value="<?php echo $productData['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $productData['stock_quantity']; ?>" required>
                    </div>
                </div>
                
                <div class="image-options">
                    <h3><i class="bi bi-image"></i> Product Image</h3>
                    
                    <div class="image-tabs">
                        <button type="button" class="tab-btn active" onclick="switchImageTab('upload')">
                            <i class="bi bi-upload"></i> Upload New File
                        </button>
                        <button type="button" class="tab-btn" onclick="switchImageTab('url')">
                            <i class="bi bi-link-45deg"></i> Use URL
                        </button>
                    </div>
                    
                    <?php if (!empty($productData['image_url'])): ?>
                        <div class="current-image-display">
                            <small>Current image:</small>
                            <img src="<?php echo htmlspecialchars($productData['image_url']); ?>" alt="Current product image" style="max-width: 200px; margin-top: 10px; border-radius: 4px;">
                        </div>
                    <?php endif; ?>
                    
                    <div id="upload-tab" class="image-tab active">
                        <div class="form-group">
                            <label for="image_file">Choose New Image File</label>
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
                
                <button type="submit" class="submit-btn">
                    <i class="bi bi-pencil"></i> Update Product
                </button>
            </form>
        </div>
    </div>

    <script>
        function switchImageTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.image-tab').forEach(tabContent => tabContent.classList.remove('active'));
            
            if (tab === 'upload') {
                document.querySelector('[onclick="switchImageTab(\'upload\')"]').classList.add('active');
                document.getElementById('upload-tab').classList.add('active');
                removePreview();
                removeUrlPreview();
            } else {
                document.querySelector('[onclick="switchImageTab(\'url\')"]').classList.add('active');
                document.getElementById('url-tab').classList.add('active');
                removePreview();
                removeUrlPreview();
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
                img.onload = function() {
                    document.getElementById('url-preview').style.display = 'block';
                };
                img.onerror = function() {
                    document.getElementById('url-preview').style.display = 'none';
                    alert('Could not load image from URL. Please check the URL and try again.');
                };
                img.src = url;
            } else {
                removeUrlPreview();
            }
        }

        function removeUrlPreview() {
            const preview = document.getElementById('url-preview');
            if (preview) {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
