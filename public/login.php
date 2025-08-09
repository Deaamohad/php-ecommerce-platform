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
    <link rel="stylesheet" href="css/login-register.css">
    <title>Login</title>
</head>
<body>
    <form action="../src/process_login.php" method="POST">
        <h1 class="form-title">Welcome Back</h1>
        <p class="form-subtitle">Sign in to your account</p>
        
        <?php if (isset($_GET['error'])) : ?>
        <div class="error"><p><?php echo getErrorMessage($_GET['error']); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])) : ?>
        <div class="success"><p><?php echo getSuccessMessage($_GET['success']); ?></p></div>
        <?php endif; ?>
        
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <div class="remember-me">
            <input type="checkbox" name="remember_me" id="remember">
            <label for="remember">Remember me for 30 days</label>
        </div>
        
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit">Sign In</button>
        <a id="register-button" href="register">Don't have an account?</a>
    </form>

</body>
</html>