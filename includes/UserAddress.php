<?php

class UserAddress {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addAddress($user_id, $title, $full_name, $street_address, $city, $phone, $is_default = false) {
        if ($is_default) {
            $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO user_addresses (user_id, title, full_name, street_address, city, phone, is_default) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $title, $full_name, $street_address, $city, $phone, $is_default]);
    }

    public function getUserAddresses($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function getDefaultAddress($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_addresses 
            WHERE user_id = ? AND is_default = TRUE 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            $stmt = $this->pdo->prepare("
                SELECT * FROM user_addresses 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
        }
        
        return $result;
    }

    public function updateAddress($address_id, $user_id, $title, $full_name, $street_address, $city, $phone, $is_default = false) {
        if ($is_default) {
            $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }

        $stmt = $this->pdo->prepare("
            UPDATE user_addresses 
            SET title = ?, full_name = ?, street_address = ?, city = ?, phone = ?, is_default = ? 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$title, $full_name, $street_address, $city, $phone, $is_default, $address_id, $user_id]);
    }

    public function deleteAddress($address_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        return $stmt->execute([$address_id, $user_id]);
    }

    public function setDefaultAddress($address_id, $user_id) {
        $this->pdo->beginTransaction();
        
        try {
            $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $stmt = $this->pdo->prepare("UPDATE user_addresses SET is_default = TRUE WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollback();
            return false;
        }
    }

    public function getAddressById($address_id, $user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$address_id, $user_id]);
        return $stmt->fetch();
    }
}
?>
