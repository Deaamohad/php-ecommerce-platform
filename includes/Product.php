<?php


class Product {
	private $pdo;

	public function __construct($database) {
		$this->pdo = $database;
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
	public function addProduct(
		$name,
		$description,
		$price,
		$stock_quantity,
		$category_id,
		$image_url
	) {
		$stmt = $this->pdo->prepare("INSERT INTO products (name, description, price, stock_quantity, category_id, image_url) VALUES 
			(?, ?, ?, ?, ?, ?)");
		$stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url]);
	}
}