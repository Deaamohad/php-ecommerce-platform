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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Login</title>
</head>
<body>
    <div class="form-container">
        <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
        
        <?php if (isset($_GET['error'])) : ?>
            <div class="error-message">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo getErrorMessage($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])) : ?>
            <div class="success-message">
                <i class="bi bi-check-circle"></i>
                <?php echo getSuccessMessage($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <form action="../src/process_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px; font-weight: normal;">
                    <input type="checkbox" name="remember_me" id="remember" style="width: auto;">
                    Remember me for 30 days
                </label>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit" class="submit-btn">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="register">Sign up here</a>
        </div>
    </div>
</body>
</html>