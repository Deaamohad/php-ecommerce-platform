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
        $fields = [];
        $values = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $values[] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $values[] = $data['full_name'];
        }
        
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $values[] = $data['phone'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount() > 0;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();
        return $users;
    }
}
