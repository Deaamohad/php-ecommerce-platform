<?php

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login?error=please-login");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard");
        exit();
    }
}
