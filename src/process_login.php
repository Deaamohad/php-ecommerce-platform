<?php 

session_start();

require "../includes/db.php";
require "../includes/csrf.php";

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../public/login?error=csrf-token-invalid");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: ../public/login?error=missing-data");
    exit();
}

if (empty(trim($_POST['username'])) || empty($_POST['password'])) {
    header("Location: ../public/login?error=empty-fields");
    exit();
}

$username = trim($_POST["username"]);
$password = $_POST["password"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() == 0) {
    header("Location: ../public/login?invalid-credentials");
    exit();
} else {
    $user = $stmt->fetch();
    if (!password_verify($password, $user['password'])) {
        header("Location: ../public/login?invalid-credentials");
        exit();
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        regenerateCSRFToken();
        header("Location: ../public/dashboard");
        exit();
    }
}

