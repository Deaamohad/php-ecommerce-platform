<?php

class Cart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addToCart($user_id, $product_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM shopping_cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        if ($stmt->rowCount() === 0) {
            $stmt = $this->pdo->prepare("INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, 1]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE shopping_cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        }
    }

    public function getCartItems($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                products.id, 
                products.name, 
                products.price, 
                products.image_url, 
                shopping_cart.quantity 
            FROM shopping_cart 
            JOIN products ON shopping_cart.product_id = products.id 
            WHERE shopping_cart.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $cartItems = $stmt->fetchAll();
        return $cartItems;
    }

    public function removeFromCart($user_id, $product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        return $stmt->rowCount() > 0;
    }

    public function getCartTotal($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(products.price * shopping_cart.quantity) as total 
            FROM shopping_cart 
            JOIN products ON shopping_cart.product_id = products.id 
            WHERE shopping_cart.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function updateQuantity($user_id, $product_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($user_id, $product_id);
        }
        
        $stmt = $this->pdo->prepare("UPDATE shopping_cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $user_id, $product_id]);
        return $stmt->rowCount() > 0;
    }

    public function getCartCount($user_id) {
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) as count FROM shopping_cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}
