<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_destroy();
    header("Location: login.php?message=logged-out");
    exit();
} else {
    header("Location: dashboard.php");
    exit();
}
?>