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

if (!isset($_POST["update_username"])) {
	header("Location: ../public/profile?error=missing-data");
    exit();
}

if (empty(trim($_POST['update_username']))) {
    header("Location: ../public/profile?error=empty-fields");
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

} else {  
	$userObj->updateUser($_SESSION['user_id'], ["username" => $_POST['update_username']]);
	header("Location: ../public/profile?success=username-updated");
	exit();
}