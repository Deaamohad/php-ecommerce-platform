<?php

session_start();
require "../includes/csrf.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        header("Location: products?error=csrf-token-invalid");
        exit();
    }
    session_destroy();
    header("Location: login?message=logged-out");
    exit();
} else {
    header("Location: products");
    exit();
}
?>