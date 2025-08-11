<?php
session_start();

require_once "../../includes/auth.php";
require_once "../../includes/csrf.php";
require_once "../../includes/db.php";
require_once "../../includes/Product.php";

requireLogin();
requireAdmin();

if (isDemoAdmin()) {
    header("Location: ../../public/admin.php?error=demo_mode_disabled");
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../../public/admin.php?error=csrf-token-invalid");
    exit();
}

if (!isset($_POST['name']) || !isset($_POST['price']) || !isset($_POST['stock_quantity']) || !isset($_POST['category_id'])) {
    header("Location: ../../public/admin.php?error=missing-data");
    exit();
}

if (empty(trim($_POST['name'])) || empty($_POST['price']) || empty($_POST['stock_quantity']) || empty($_POST['category_id'])) {
    header("Location: ../../public/admin.php?error=empty-fields");
    exit();
}

$name = trim($_POST['name']);
$description = trim($_POST['description'] ?? '');
$price = floatval($_POST['price']);
$stock_quantity = intval($_POST['stock_quantity']);
$category_id = intval($_POST['category_id']);
$image_url = trim($_POST['image_url'] ?? '');

if ($price <= 0) {
    header("Location: ../../public/admin.php?error=invalid-price");
    exit();
}

if ($stock_quantity < 0) {
    header("Location: ../../public/admin.php?error=invalid-stock");
    exit();
}

if ($category_id <= 0) {
    header("Location: ../../public/admin.php?error=invalid-category");
    exit();
}

if (strlen($name) < 2 || strlen($name) > 100) {
    header("Location: ../../public/admin.php?error=invalid-name");
    exit();
}

try {
    $product = new Product($pdo);
    
    $image_file = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $image_file = $_FILES['image_file'];
    }
    
    $productId = $product->addProduct(
        $name, 
        $description, 
        $price, 
        $stock_quantity, 
        $category_id, 
        $image_url, 
        $image_file
    );
    
    header("Location: ../../public/admin.php?success=product-added");
    exit();
    
} catch (Exception $e) {
    error_log("Add product error: " . $e->getMessage());
    
    if (strpos($e->getMessage(), 'file') !== false || strpos($e->getMessage(), 'image') !== false) {
        header("Location: ../../public/admin.php?error=image-upload-failed");
    } else {
        header("Location: ../../public/admin.php?error=database-error");
    }
    exit();
}
?>
