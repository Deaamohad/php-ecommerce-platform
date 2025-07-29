<?php
session_start();
require "../includes/auth.php";
requireLogin(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome <?php echo $_SESSION['username']; ?>!</h1>
    <form method="POST" action="logout.php" style="display: inline;">
        <button type="submit">Logout</button>
    </form>
</body>
</html>