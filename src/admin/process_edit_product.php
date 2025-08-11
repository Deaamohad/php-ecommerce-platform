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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../public/admin.php');
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    header('Location: ../../public/admin.php?error=invalid_token');
    exit();
}

$product_id = $_POST['product_id'];
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$price = floatval($_POST['price']);
$stock_quantity = intval($_POST['stock_quantity']);
$category_id = intval($_POST['category_id']);
$image_url = trim($_POST['image_url'] ?? '');

if (empty($name) || $price <= 0 || $stock_quantity < 0 || empty($category_id)) {
    header('Location: ../../public/edit_product.php?id=' . $product_id . '&error=missing_fields');
    exit();
}

try {
    $product = new Product($pdo);
    
    $image_file = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $image_file = $_FILES['image_file'];
    }
    
    if ($product->updateProduct($product_id, $name, $description, $price, $stock_quantity, $category_id, $image_url, $image_file)) {
        header('Location: ../../public/admin.php?success=product_updated');
    } else {
        header('Location: ../../public/edit_product.php?id=' . $product_id . '&error=update_failed');
    }
} catch (Exception $e) {
    error_log("Edit product error: " . $e->getMessage());
    
    if (strpos($e->getMessage(), 'file') !== false || strpos($e->getMessage(), 'image') !== false) {
        header('Location: ../../public/edit_product.php?id=' . $product_id . '&error=image-upload-failed');
    } else {
        header('Location: ../../public/edit_product.php?id=' . $product_id . '&error=update_failed');
    }
}
exit();
?>
