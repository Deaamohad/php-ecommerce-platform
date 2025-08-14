<?php
session_start();
require_once "includes/auth.php";
require_once "includes/csrf.php";
require_once "includes/db.php";
require_once "includes/Product.php";

requireLogin();
requireAdmin();

if (isDemoAdmin()) {
    header("Location: admin?error=demo_mode_disabled");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin');
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    header('Location: admin?error=invalid_token');
    exit();
}

$product_id = intval($_POST['product_id']);

if ($product_id <= 0) {
    header('Location: admin?error=invalid_product');
    exit();
}

$product = new Product($pdo);

if ($product->deleteProduct($product_id)) {
            header('Location: admin?success=product_deleted');
} else {
            header('Location: admin?error=delete_failed');
}
exit();
?>
