<?php

class User {
    private $pdo;
    
    public function __construct($database) {
        $this->pdo = $database;
    }
    
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user;
    }

    public function updateUser($id, $data) {
        $stmt = $this->pdo->prepare
        ("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$data['username'], $id]);
        return $stmt->rowCount();
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users");
        $users = $stmt->fetchAll();
        return $users;
    }
}