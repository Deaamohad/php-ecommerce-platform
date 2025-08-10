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
    <title>Register</title>
</head>
<body>
    <div class="form-container">
        <h1><i class="bi bi-person-plus"></i> Create Account</h1>
        
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
        
        <form action="../src/process_register.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password" required>
            </div>
            
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <button type="submit" class="submit-btn">
                <i class="bi bi-person-check"></i> Create Account
            </button>
        </form>
        
        <div class="login-link">
                            Already have an account? <a href="login">Sign in here</a>
        </div>
    </div>
</body>
</html>
