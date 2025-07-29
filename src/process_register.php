<?php 

session_start();

require "../includes/db.php";

$username = $_POST["username"];
$password = $_POST["password"];
$confirmPassword = $_POST["confirm-password"];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($password != $confirmPassword) {
    header("Location: register.php?error=password-mismatch");
    exit();
}

if (strlen($password) < 5 || strlen($username) < 5) {
    header("Location: register.php?short-credentials");
    exit(); 
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() > 0) {
    header("Location: register.php?user-exists");
    exit();
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    
    header("Location: dashboard.php");
}
