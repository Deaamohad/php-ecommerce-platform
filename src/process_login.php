<?php 

session_start();

require_once "../includes/db.php";
require_once "../includes/csrf.php";
require_once "../includes/rate_limiting.php";

function redirectWithLoginError($error, $field = null, $message = null) {
    $_SESSION['form_data'] = [
        'username' => $_POST['username'] ?? ''
    ];
    
    if ($field && $message) {
        $_SESSION['field_errors'] = [$field => $message];
    }
    
    recordFailedAttempt(getUserIP(), $GLOBALS['pdo']);
            header("Location: ../login?error=$error");
    exit();
}

if (isIpBlocked(getUserIP(), $pdo)) {
    header("Location: ../login?error=too-many-requests");
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    redirectWithLoginError('csrf-token-invalid');
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    redirectWithLoginError('missing-data');
}

if (empty(trim($_POST['username'])) || empty($_POST['password'])) {
    redirectWithLoginError('empty-fields');
}

$username = trim($_POST["username"]);
$password = $_POST["password"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() == 0) {
    redirectWithLoginError('invalid-credentials', 'username', 'Username not found');
} else {
    $user = $stmt->fetch();
    if (!password_verify($password, $user['hashed_password'])) {
        redirectWithLoginError('invalid-credentials', 'password', 'Incorrect password');
    } else {
        unset($_SESSION['form_data'], $_SESSION['field_errors']);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        $_SESSION['is_admin'] = $user['is_admin'];
        regenerateCSRFToken();
        resetAttempts(getUserIP(), $pdo);
        header("Location: ../products");
        exit();
    }
}

