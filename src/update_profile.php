<?php

session_start();

require "includes/db.php";
require "includes/csrf.php";
require "includes/User.php";
require "includes/auth.php";
require "includes/validation.php";

requireLogin();

$userObj = new User($pdo);

if (!validateCSRFToken($_POST['csrf_token'])) {
    header("Location: profile?error=csrf-token-invalid");
    exit();
}

if (isset($_POST['username']) && isset($_POST['email'])) {
    if (empty(trim($_POST['username'])) || empty(trim($_POST['email']))) {
        header("Location: profile?error=empty-required-fields");
        exit();
    }

    $updateData = [];
    
    if ($_POST['username'] !== $_SESSION['username']) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$_POST['username'], $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: profile?error=username-already-exists");
            exit();
        }
        
        if (!validateUsername($_POST['username'])) {
            header("Location: profile?error=invalid-username");
            exit();
        }
        
        $updateData['username'] = $_POST['username'];
        $_SESSION['username'] = $_POST['username'];
    }
    
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        header("Location: profile?error=invalid-email");
        exit();
    }
    
    $updateData['email'] = $_POST['email'];
    
    if (!empty(trim($_POST['full_name']))) {
        $updateData['full_name'] = trim($_POST['full_name']);
    }
    
    if (!empty(trim($_POST['phone']))) {
        $updateData['phone'] = trim($_POST['phone']);
    }
    
    if ($userObj->updateUser($_SESSION['user_id'], $updateData)) {
        header("Location: profile?success=profile-updated");
    } else {
        header("Location: profile?error=update-failed");
    }
    exit();
}

if (isset($_POST['update_username_btn'])) {
    if (!isset($_POST["update_username"]) || !isset($_POST["current_password"])) {
        header("Location: profile?error=missing-data");
        exit();
    }

    if (empty(trim($_POST['update_username'])) || empty(trim($_POST['current_password']))) {
        header("Location: profile?error=empty-fields");
        exit();
    }

    $stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['hashed_password'])) {
        header("Location: profile?error=incorrect-password");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['update_username']]);

    if ($stmt->rowCount() > 0) {
        header("Location: profile?error=username-already-exists");
        exit();
    }

    if (!validateUsername($_POST['update_username'])) {
        header("Location: profile?error=invalid-username");
        exit();
    }

    if ($userObj->updateUser($_SESSION['user_id'], ['username' => $_POST['update_username']])) {
        $_SESSION['username'] = $_POST['update_username'];
        header("Location: profile?success=username-updated");
    } else {
        header("Location: profile?error=update-failed");
    }
    exit();
}

if (isset($_POST['change_password_btn']) || (isset($_POST['current_password']) && isset($_POST['new_password']) && isset($_POST['confirm_password']))) {
    if (!isset($_POST["current_password"]) || !isset($_POST["new_password"]) || !isset($_POST["confirm_password"])) {
        header("Location: profile?error=missing-data");
        exit();
    }

    if (empty(trim($_POST['current_password'])) || empty(trim($_POST['new_password'])) || empty(trim($_POST['confirm_password']))) {
        header("Location: profile?error=empty-fields");
        exit();
    }

    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        header("Location: profile?error=passwords-dont-match");
        exit();
    }

    if (strlen($_POST['new_password']) < 8) {
        header("Location: profile?error=password-too-short");
        exit();
    }

    $stmt = $pdo->prepare("SELECT hashed_password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($_POST['current_password'], $user['hashed_password'])) {
        header("Location: profile?error=incorrect-current-password");
        exit();
    }

    if (password_verify($_POST['new_password'], $user['hashed_password'])) {
        header("Location: profile?error=same-password");
        exit();
    }

    $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET hashed_password = ? WHERE id = ?");
    if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
        header("Location: profile?success=password-changed");
    } else {
        header("Location: profile?error=update-failed");
    }
    exit();
}

header("Location: profile?error=missing-data");
exit();
?>
