<?php

session_start();

require "../includes/db.php";
require "../includes/csrf.php";
require "../includes/auth.php";

requireLogin();

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../profile?tab=security&error=csrf-token-invalid");
    exit();
}

if (!isset($_POST["current_password"]) || !isset($_POST["new_password"]) || !isset($_POST["confirm_password"])) {
    header("Location: ../profile?tab=security&error=missing-data");
    exit();
}

if (empty(trim($_POST['current_password'])) || empty(trim($_POST['new_password'])) || empty(trim($_POST['confirm_password']))) {
    header("Location: ../profile?tab=security&error=empty-fields");
    exit();
}

if ($_POST['new_password'] !== $_POST['confirm_password']) {
    header("Location: ../profile?tab=security&error=passwords-dont-match");
    exit();
}

if (strlen($_POST['new_password']) < 8) {
    header("Location: ../profile?tab=security&error=password-too-short");
    exit();
}

$stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!password_verify($_POST['current_password'], $user['hashed_password'])) {
            header("Location: ../profile?tab=security&error=incorrect-current-password");
    exit();
}

if (password_verify($_POST['new_password'], $user['hashed_password'])) {
            header("Location: ../profile?tab=security&error=same-password");
    exit();
}

$hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET hashed_password = ? WHERE id = ?");
if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
    header("Location: ../profile?tab=security&success=password-changed");
} else {
    header("Location: ../profile?tab=security&error=update-failed");
}
exit();
?>
