<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/Cart.php";
require_once "../includes/csrf.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header('Location: ../products');
        exit();
    }
    
    $cart = new Cart($pdo);
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    
    $added = $cart->addToCart($user_id, $product_id);
    if (!$added) {
        header('Location: ../products?out_of_stock=1');
        exit();
    }
    header('Location: ../products?added=1');
    exit();
}

header('Location: ../products');
exit();
?>
