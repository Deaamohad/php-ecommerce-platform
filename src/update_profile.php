<?php

session_start();

require "../includes/db.php";
require "../includes/csrf.php";
require "../includes/User.php";
require "../includes/auth.php";
require "../includes/validation.php";

requireLogin();

$userObj = new User($pdo);

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: ../public/profile?error=csrf-token-invalid");
    exit();
}

if (isset($_POST['update_username_btn'])) {
    if (!isset($_POST["update_username"]) || !isset($_POST["current_password"])) {
        header("Location: ../public/profile?error=missing-data");
        exit();
    }

    if (empty(trim($_POST['update_username'])) || empty(trim($_POST['current_password']))) {
        header("Location: ../public/profile?error=empty-fields");
        exit();
    }

    $stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['hashed_password'])) {
        header("Location: ../public/profile?error=incorrect-password");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['update_username']]);

    if ($stmt->rowCount() > 0) {
        header("Location: ../public/profile?error=username-already-exists");
        exit();
    }

    if (!validateUsername($_POST['update_username'])) {
        header("Location: ../public/profile?error=invalid-username");
        exit();
    }

    if ($userObj->updateUser($_SESSION['user_id'], ['username' => $_POST['update_username']])) {
        $_SESSION['username'] = $_POST['update_username'];
        header("Location: ../public/profile?success=username-updated");
    } else {
        header("Location: ../public/profile?error=update-failed");
    }
    exit();
}

if (isset($_POST['change_password_btn'])) {
    if (!isset($_POST["current_password"]) || !isset($_POST["new_password"]) || !isset($_POST["confirm_password"])) {
        header("Location: ../public/profile?error=missing-data");
        exit();
    }

    if (empty(trim($_POST['current_password'])) || empty(trim($_POST['new_password'])) || empty(trim($_POST['confirm_password']))) {
        header("Location: ../public/profile?error=empty-fields");
        exit();
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        header("Location: ../public/profile?error=passwords-dont-match");
        exit();
    }

    if (strlen($_POST['new_password']) < 8) {
        header("Location: ../public/profile?error=password-too-short");
        exit();
    }

    $stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['hashed_password'])) {
        header("Location: ../public/profile?error=incorrect-current-password");
        exit();
    }

    if (password_verify($_POST['new_password'], $user['hashed_password'])) {
        header("Location: ../public/profile?error=same-password");
        exit();
    }

    $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET hashed_password = ? WHERE id = ?");
    if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
        header("Location: ../public/profile?success=password-changed");
    } else {
        header("Location: ../public/profile?error=update-failed");
    }
    exit();
}

header("Location: ../public/profile?error=missing-data");
exit();
?>
