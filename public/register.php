<?php

session_start();
require "../includes/auth.php";
require "../includes/csrf.php";
require "../includes/messages.php";
redirectIfLoggedIn();
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <form action="../src/process_register.php" method="POST">
        <h1 class="form-title">Create Account</h1>
        <p class="form-subtitle">Join us today</p>
        
        <?php if (isset($_GET['error'])) : ?>
        <div class="error"><p><?php echo getErrorMessage($_GET['error']); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])) : ?>
        <div class="success"><p><?php echo getSuccessMessage($_GET['success']); ?></p></div>
        <?php endif; ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm-password" placeholder="Confirm Password" required>
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit">Register</button>
        <a id="login-button" href="login">Already have an account?</a>
    </form>

</body>
</html>