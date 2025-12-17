<?php
namespace App\Controllers;

use App\configs\Database;
use PDO;

class ProductController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    public function getAllProducts() {
        try {
            $query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getProductById($id) {
        try {
            $query = "SELECT * FROM products WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return null;
        }
    }

    public function createProduct($data) {
        try {
            $query = "INSERT INTO products (title, description, price, image, category_id, stock) 
                      VALUES (:title, :description, :price, :image, :category_id, :stock)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':stock', $data['stock']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Product created successfully', 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to create product'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function updateProduct($id, $data) {
        try {
            $query = "UPDATE products SET 
                      title = :title, 
                      description = :description, 
                      price = :price, 
                      image = :image, 
                      category_id = :category_id, 
                      stock = :stock 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':image', $data['image']);
            $stmt->bindParam(':category_id', $data['category_id']);
            $stmt->bindParam(':stock', $data['stock']);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Product updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update product'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function deleteProduct($id) {
        try {
            $query = "DELETE FROM products WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Product deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete product'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function searchProducts($searchTerm) {
        try {
            $query = "SELECT * FROM products WHERE title LIKE :search OR description LIKE :search ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $searchParam = "%{$searchTerm}%";
            $stmt->bindParam(':search', $searchParam);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getFeaturedProducts($limit = 6) {
        try {
            $query = "SELECT p.*, c.name as category_name 
                      FROM products p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      ORDER BY p.created_at DESC 
                      LIMIT " . intval($limit);
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getHeroStats() {
        try {
            $query = "SELECT 
                (SELECT COUNT(*) FROM products WHERE stock > 0) as products_count,
                (SELECT COUNT(*) FROM users) as customers_count,
                (SELECT COUNT(*) FROM orders WHERE status = 'Completed') as orders_count
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return ['products_count' => 0, 'customers_count' => 0, 'orders_count' => 0];
        }
    }
}
