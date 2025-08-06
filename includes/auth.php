<?php

function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        header("Location: ../public/login?error=please-login");
        exit();
    }

    if ((time() - $_SESSION['last_activity']) >= 1800) {
        session_destroy();
        header("Location: ../public/login?error=session-timed-out");
        exit();

    }

    $_SESSION['last_activity'] = time();
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard");
        exit();
    }
}
