<?php
session_start();

require_once "../../includes/auth.php";
require_once "../../includes/csrf.php";
require_once "../../includes/db.php";
require_once "../../includes/Product.php";
require_once "../../includes/ImageHandler.php";

requireLogin();
requireAdmin();

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

$image_url = '';
$imageHandler = new ImageHandler();

if (!empty($_POST['image_url'])) {
    $url = trim($_POST['image_url']);
    if ($imageHandler->validateImageUrl($url)) {
        $image_url = $url;
    } else {
        header("Location: ../../public/admin.php?error=invalid-image-url");
        exit();
    }
}

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
    $product->addProduct($name, $description, $price, $stock_quantity, $category_id, $image_url);
    header("Location: ../../public/admin.php?success=product-added");
    exit();
} catch (Exception $e) {
    header("Location: ../../public/admin.php?error=database-error");
    exit();
}
?>
