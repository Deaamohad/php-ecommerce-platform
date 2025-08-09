<?php
session_start();
require "../includes/auth.php";
require "../includes/csrf.php";
requireLogin(); 
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/base.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        
        <div class="nav">
            <a href="profile">Profile</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin">Admin Panel</a>
            <?php endif; ?>
            <form method="POST" action="logout" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit">Logout</button>
            </form>
        </div>
        
        <div class="info">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>You are successfully logged in to your account.</p>
        </div>
    </div>
</body>
</html>
