<?php
session_start();
require_once "../../includes/auth.php";
require_once "../../includes/csrf.php";
require_once "../../includes/db.php";
require_once "../../includes/Product.php";

requireLogin();
requireAdmin();

if (isDemoAdmin()) {
    header("Location: ../../admin?error=demo_mode_disabled");
    exit();
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    header('Location: ../../admin');
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    header('Location: ../../admin?error=invalid_token');
    exit();
}

$product_id = intval($_POST['product_id']);
$product = new Product($pdo);
$productData = $product->getProductById($product_id);

if (!$productData) {
    header('Location: ../../admin?error=product_not_found');
    exit();
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$stock_quantity = intval($_POST['stock_quantity'] ?? 0);

if (empty($name) || $category_id <= 0 || $price <= 0 || $stock_quantity < 0) {
    header('Location: ../../edit_product?id=' . $product_id . '&error=missing_fields');
    exit();
}

$updateData = [
    'name' => $name,
    'description' => $description,
    'category_id' => $category_id,
    'price' => $price,
    'stock_quantity' => $stock_quantity
];

// Handle image upload if provided
if (!empty($_FILES['image_file']['name'])) {
    $uploader = new ImageUploader($pdo);
    $imageResult = $uploader->uploadImage($_FILES['image_file']);
    
    if ($imageResult['success']) {
        $updateData['image_url'] = $imageResult['path'];
    } else {
        header('Location: ../../edit_product?id=' . $product_id . '&error=image-upload-failed');
        exit();
    }
} elseif (!empty($_POST['image_url'])) {
    $updateData['image_url'] = $_POST['image_url'];
}

if ($product->updateProduct($product_id, $updateData)) {
    header('Location: ../../admin?success=product_updated');
} else {
    header('Location: ../../edit_product?id=' . $product_id . '&error=update_failed');
}
exit();
?>
