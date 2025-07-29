<?php 

session_start();

require "../includes/db.php";

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: login.php?error=missing-data");
    exit();
}

if (empty(trim($_POST['username'])) || empty($_POST['password'])) {
    header("Location: login.php?error=empty-fields");
    exit();
}

$username = trim($_POST["username"]);
$password = $_POST["password"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() == 0) {
    header("Location: login.php?user-doesnt-exist");
    exit();
} else {
    $user = $stmt->fetch();
    if (!password_verify($password, $user['password'])) {
        header("Location: login.php?wrong-password");
        exit();
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit();
    }
}

