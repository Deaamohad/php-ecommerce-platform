<?php 

session_start();

require_once "../includes/db.php";
require_once "../includes/csrf.php";
require_once "../includes/validation.php";

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../public/register?error=csrf-token-invalid");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['confirm-password'])) {
    header("Location: ../public/register?error=missing-data");
    exit();
}

if (empty(trim($_POST['username'])) || empty($_POST['password']) || empty($_POST['confirm-password'])) {
    header("Location: ../public/register?error=empty-fields");
    exit();
}

if (!validateUsername($_POST['username'])) {
    header("Location: ../public/register?error=invalid-username");
    exit();
}

if (!validatePassword($_POST['password'])) {
    header("Location: ../public/register?error=invalid-password");
    exit();
}

if (!hash_equals($_POST['password'], $_POST['confirm-password'])) {
    header("Location: ../public/register?error=password-mismatch");
    exit();
}

$username = $_POST["username"];
$password = $_POST["password"];
$confirmPassword = $_POST["confirm-password"];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() > 0) {
    header("Location: ../public/register?error=user-exists");
    exit();
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, hashed_password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    
    header("Location: ../public/login?success=registration-complete");
    exit();
}
?>
