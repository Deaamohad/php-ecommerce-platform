<?php

session_start();

include "../includes/auth.php";
include "../includes/User.php";
include "../includes/csrf.php";
include "../includes/db.php";
include "../includes/messages.php";

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
	<link rel="stylesheet" href="css/dashboard.css">
	<title>Profile</title>
</head>
<body>
	<div class="container">
		<h1>User Profile</h1>
		
		<div class="nav">
			<a href="dashboard.php">Dashboard</a>
			<form method="POST" action="logout.php" style="display: inline;">
				<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
				<button type="submit">Logout</button>
			</form>
		</div>

		<?php if (isset($_GET['error'])) : ?>
		<div class="error"><p><?php echo getErrorMessage($_GET['error']); ?></p></div>
		<?php endif; ?>
		
		<?php if (isset($_GET['success'])) : ?>
		<div class="success"><p><?php echo getSuccessMessage($_GET['success']); ?></p></div>
		<?php endif; ?>

		<div class="info">
			<h2>Account Information</h2>
			<p><strong>Username:</strong> <?php echo htmlspecialchars($userData['username']); ?></p>
			<p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($userData['created_at'])); ?></p>
		</div>

		<div class="update-form">
			<h2>Update Username</h2>
			<form action="../src/update_profile.php" method="POST">
				<input type="text" name="update_username" placeholder="Enter new username" required>
				<input type="password" name="current_password" placeholder="Current password for security" required>
				<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
				<button type="submit" name="update_username_btn">Update Username</button>
			</form>
		</div>

		<div class="update-form">
			<h2>Change Password</h2>
			<form action="../src/update_profile.php" method="POST">
				<input type="password" name="current_password" placeholder="Current password" required>
				<input type="password" name="new_password" placeholder="New password" required>
				<input type="password" name="confirm_password" placeholder="Confirm new password" required>
				<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
				<button type="submit" name="change_password_btn">Change Password</button>
			</form>
		</div>
	</div>
</body>
</html>
