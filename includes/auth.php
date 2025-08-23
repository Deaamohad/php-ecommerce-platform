<?php

function getBasePath() {
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($scriptDir === '' || $scriptDir === '/') {
        return '';
    }
    if (preg_match('#/(src|includes)(/|$)#', $scriptDir)) {
        $base = rtrim(dirname($scriptDir), '/');
        return ($base === '' || $base === '/') ? '' : $base;
    }
    return $scriptDir;
}

function requireLogin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        header("Location: " . getBasePath() . "/login?error=please-login");
        exit();
    }

    if ((time() - $_SESSION['last_activity']) >= 1800) {
        session_destroy();
        header("Location: " . getBasePath() . "/login?error=session-timed-out");
        exit();

    }

    $_SESSION['last_activity'] = time();
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header("Location: " . getBasePath() . "/products");
        exit();
    }
}

function requireAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {    
        header("Location: " . getBasePath() . "/login");
        exit();
    }
}

function isDemoAdmin() {
    return isset($_SESSION['username']) && $_SESSION['username'] === 'admin';
}

function isDemoUser() {
    return isset($_SESSION['username']) && $_SESSION['username'] === 'user';
}

function getDemoMessage() {
    return "This is a demo account for portfolio viewing. Product editing is disabled to maintain the demo environment for other visitors.";
}

function getDemoUserMessage() {
    return "This is a demo user account for portfolio viewing. Feel free to browse products, add items to cart, and test the checkout process.";
}
