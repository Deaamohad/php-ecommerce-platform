<?php

session_start();

include "../includes/auth.php";
include "../includes/User.php";
include "../includes/csrf.php";
include "../includes/db.php";

requireLogin();
$csrf_token = generateCSRFToken();


$userObj = new User($pdo);
$userData = $userObj->getUserbyId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="css/style.css">
	<title>Document</title>
</head>
<body>

	<p>Username: <b><?php echo $userData['username']; ?></b></p>
	<p>Account created at: <b><?php echo $userData['created_at']; ?></b></p>


	<form action="../src/update_user.php" method="POST">
		<input type="text" name="update_username" placeholder="New Username...">
		<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
		<button type="submit">Update</button>
	</form>

</body>
</html>
