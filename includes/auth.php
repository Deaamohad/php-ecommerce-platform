<?php

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=please-login");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
}
