<?php 

session_start();

require_once "../includes/db.php";
require_once "../includes/csrf.php";
require_once "../includes/validation.php";

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../public/register?error=csrf-token-invalid");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['confirm-password']) || !isset($_POST['email'])) {
    header("Location: ../public/register?error=missing-data");
    exit();
}

if (empty(trim($_POST['username'])) || empty($_POST['password']) || empty($_POST['confirm-password']) || empty(trim($_POST['email']))) {
    header("Location: ../public/register?error=empty-fields");
    exit();
}

if (!validateUsername($_POST['username'])) {
    header("Location: ../public/register?error=invalid-username");
    exit();
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    header("Location: ../public/register?error=invalid-email");
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

$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);

if ($stmt->rowCount() > 0) {
    $existing_user = $stmt->fetch();
    if ($existing_user['username'] === $username) {
        header("Location: ../public/register?error=user-exists");
    } else {
        header("Location: ../public/register?error=email-exists");
    }
    exit();
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, hashed_password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);
    
    header("Location: ../public/login?success=registration-complete");
    exit();
}
?>
