<?php

require_once 'ImageUploader.php';

class Product {
    private $pdo;
    private $imageUploader;

    public function __construct($database) {
        $this->pdo = $database;
        $this->imageUploader = new ImageUploader();
    }

    public function getAllProducts() {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFeaturedProducts($limit = 6) {
        $stmt = $this->pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function addProduct($name, $description, $price, $stock_quantity, $category_id, $image_url = null, $image_file = null) {
        try {
            if ($image_file && $image_file['error'] === UPLOAD_ERR_OK) {
                $image_url = $this->imageUploader->uploadImage($image_file);
            } elseif (!$image_url || !$this->imageUploader->isValidImageUrl($image_url)) {
                $image_url = 'assets/placeholder.jpg';
            }

            $stmt = $this->pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url]);
            
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log('Add product error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProduct($id, $name, $description, $price, $stock_quantity, $category_id, $image_url = null, $image_file = null) {
        try {
            $currentProduct = $this->getProductById($id);
            if (!$currentProduct) {
                throw new Exception('Product not found');
            }

            $newImageUrl = $currentProduct['image_url'];

            if ($image_file && $image_file['error'] === UPLOAD_ERR_OK) {
                $newImageUrl = $this->imageUploader->uploadImage($image_file, $id);
                
                if ($currentProduct['image_url'] !== $newImageUrl) {
                    $this->imageUploader->deleteImage($currentProduct['image_url']);
                }
            } elseif ($image_url && $image_url !== $currentProduct['image_url']) {
                if ($this->imageUploader->isValidImageUrl($image_url)) {
                    if (strpos($currentProduct['image_url'], 'uploads/') === 0) {
                        $this->imageUploader->deleteImage($currentProduct['image_url']);
                    }
                    $newImageUrl = $image_url;
                }
            }

            $stmt = $this->pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $newImageUrl, $id]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log('Update product error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteProduct($id) {
        try {
            $product = $this->getProductById($id);
            if ($product) {
                $this->imageUploader->deleteImage($product['image_url']);
            }

            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log('Delete product error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getImageUploader() {
        return $this->imageUploader;
    }
}
