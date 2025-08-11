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

$product_id = intval($_POST['product_id']);

if ($product_id <= 0) {
    header('Location: ../../public/admin.php?error=invalid_product');
    exit();
}

$product = new Product($pdo);

if ($product->deleteProduct($product_id)) {
    header('Location: ../../public/admin.php?success=product_deleted');
} else {
    header('Location: ../../public/admin.php?error=delete_failed');
}
exit();
?>
