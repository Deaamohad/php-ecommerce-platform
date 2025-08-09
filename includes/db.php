<?php

$host = 'localhost';
$db = 'ecommerce_system';
$user = 'root';
$pass = '';

try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
} catch (\PDOException $e){
        die("DB connection failed". $e->getMessage());
}





