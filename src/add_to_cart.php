<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/Cart.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $cart = new Cart($pdo);
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    
    $cart->addToCart($user_id, $product_id);
    
    header('Location: ../public/products.php?added=1');
    exit();
}

header('Location: ../public/products.php');
exit();
?>
