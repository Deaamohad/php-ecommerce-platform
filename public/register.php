<?php


session_start();
require "../includes/auth.php";
redirectIfLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        input {
            display: block;
            margin: 15px 0 0 0;
            padding: 6px 20px;

        }
        button {
            display: inline-block;
            margin: 15px 0 0 0;
            padding: 5px 10px;
        }
        #login-button:hover {
            text-decoration: solid;
            color: cyan;
        }
    </style>
</head>
<body>
    <form action="../src/process_register.php" method="POST">
        <input type="text" name="username" placeholder="username" required>
        <input type="password" name="password" placeholder="password" required>
        <input type="password" name="confirm-password" placeholder="confirm password" required>
        <button type="submit">Register</button>
        <a id="login-button" style="font-size: 14px; color: blue; text-decoration: 0;  margin-left: 22px" href="login.php">Already have an account?</a>
    </form>

</body>
</html>