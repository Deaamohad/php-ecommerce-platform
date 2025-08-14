<?php 

session_start();

require_once "../includes/db.php";
require_once "../includes/csrf.php";
require_once "../includes/validation.php";

function redirectWithError($error, $field = null, $message = null) {
    $_SESSION['form_data'] = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? ''
    ];
    
    if ($field && $message) {
        $_SESSION['field_errors'] = [$field => $message];
    }
    
    header("Location: ../register?error=$error");
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    redirectWithError('csrf-token-invalid');
}

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['confirm-password']) || !isset($_POST['email'])) {
    redirectWithError('missing-data');
}

if (empty(trim($_POST['username'])) || empty($_POST['password']) || empty($_POST['confirm-password']) || empty(trim($_POST['email']))) {
    redirectWithError('empty-fields');
}

if (!validateUsername($_POST['username'])) {
    redirectWithError('invalid-username', 'username', 'Username must be 3-20 characters, letters and numbers only');
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    redirectWithError('invalid-email', 'email', 'Please enter a valid email address');
}

if (!validatePassword($_POST['password'])) {
    redirectWithError('invalid-password', 'password', 'Password must be at least 8 characters with uppercase, lowercase, and number');
}

if (!hash_equals($_POST['password'], $_POST['confirm-password'])) {
    redirectWithError('password-mismatch', 'confirm-password', 'Passwords do not match');
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
        redirectWithError('user-exists', 'username', 'This username is already taken');
    } else {
        redirectWithError('email-exists', 'email', 'This email is already registered');
    }
} else {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, hashed_password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);
    
    unset($_SESSION['form_data'], $_SESSION['field_errors']);
    header("Location: ../login?success=registration-complete");
    exit();
}
?>
