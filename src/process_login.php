<?php 

session_start();

require "../includes/db.php";
require "../includes/csrf.php";
require "../includes/rate_limiting.php";

if (isIpBlocked(getUserIP(), $pdo)) {
    header("Location: ../public/login?error=too-many-requests");
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'])) {
    recordFailedAttempt(getUserIP(), $pdo);
    header("Location: ../public/login?error=csrf-token-invalid");
    exit();
}

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    recordFailedAttempt(getUserIP(), $pdo);
    header("Location: ../public/login?error=missing-data");
    exit();
}

if (empty(trim($_POST['username'])) || empty($_POST['password'])) {
    recordFailedAttempt(getUserIP(), $pdo);
    header("Location: ../public/login?error=empty-fields");
    exit();
}

$username = trim($_POST["username"]);
$password = $_POST["password"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);

if ($stmt->rowCount() == 0) {
    recordFailedAttempt(getUserIP(), $pdo);
    header("Location: ../public/login?invalid-credentials");
    exit();

} else {
    $user = $stmt->fetch();
    if (!password_verify($password, $user['hashed_password'])) {
        recordFailedAttempt(getUserIP(), $pdo);
        header("Location: ../public/login?invalid-credentials");
        exit();
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time();
        regenerateCSRFToken();
        resetAttempts(getUserIP(), $pdo);
        header("Location: ../public/dashboard");
        exit();
    }
}

