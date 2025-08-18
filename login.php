<?php
session_start();
require "includes/auth.php";
require "includes/csrf.php";
require "includes/messages.php";
redirectIfLoggedIn(); 
$csrf_token = generateCSRFToken();

$form_data = $_SESSION['form_data'] ?? [];
$field_errors = $_SESSION['field_errors'] ?? [];
unset($_SESSION['form_data'], $_SESSION['field_errors']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="css/login-register.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Login</title>
</head>
<body>
    <div class="form-container">
        <h1><i class="bi bi-box-arrow-in-right"></i> Welcome Back</h1>
        
        <div class="demo-accounts-notice">
            <h3><i class="bi bi-info-circle"></i> Demo Accounts Available</h3>
            <div class="demo-account">
                <strong>User:</strong> username: <code>user</code> password: <code>user</code>
            </div>
            <div class="demo-account">
                <strong>Admin:</strong> username: <code>admin</code> password: <code>admin</code>
            </div>
        </div>
        
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
        
        <form action="src/process_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" 
                       value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
                       class="<?php echo isset($field_errors['username']) ? 'error' : ''; ?>" required>
                <?php if (isset($field_errors['username'])): ?>
                    <small class="field-error"><?php echo $field_errors['username']; ?></small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" 
                       class="<?php echo isset($field_errors['password']) ? 'error' : ''; ?>" required>
                <?php if (isset($field_errors['password'])): ?>
                    <small class="field-error"><?php echo $field_errors['password']; ?></small>
                <?php endif; ?>
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
