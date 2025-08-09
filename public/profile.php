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
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <title>Profile</title>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <h1><i class="bi bi-person-circle"></i> Your Profile</h1>
                <nav class="main-nav">
                    <a href="products">
                        <i class="bi bi-shop"></i> Store
                    </a>
                    <form method="POST" action="logout.php" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <button type="submit" style="background: none; border: 1px solid #475569; color: #94a3b8; text-decoration: none; padding: 8px 16px; border-radius: 8px; transition: all 0.2s ease; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
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

        <div class="profile-card">
            <h2><i class="bi bi-info-circle"></i> Account Information</h2>
            <div class="profile-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($userData['username']); ?></p>
                <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($userData['created_at'])); ?></p>
            </div>
        </div>

        <div class="form-container">
            <h2><i class="bi bi-pencil"></i> Update Username</h2>
            <form action="../src/update_profile.php" method="POST">
                <div class="form-group">
                    <label for="update_username">New Username</label>
                    <input type="text" id="update_username" name="update_username" placeholder="Enter new username" required>
                </div>
                <div class="form-group">
                    <label for="current_password1">Current Password</label>
                    <input type="password" id="current_password1" name="current_password" placeholder="Enter current password for security" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" name="update_username_btn" class="submit-btn">
                    <i class="bi bi-check-lg"></i> Update Username
                </button>
            </form>
        </div>

        <div class="form-container">
            <h2><i class="bi bi-shield-lock"></i> Change Password</h2>
            <form action="../src/update_profile.php" method="POST">
                <div class="form-group">
                    <label for="current_password2">Current Password</label>
                    <input type="password" id="current_password2" name="current_password" placeholder="Enter current password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" name="change_password_btn" class="submit-btn">
                    <i class="bi bi-shield-check"></i> Change Password
                </button>
            </form>
        </div>
    </main>
</body>
</html>
