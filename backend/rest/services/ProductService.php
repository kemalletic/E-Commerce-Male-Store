<?php
require_once __DIR__ . '/../dao/ProductDAO.php';

class ProductService {
    private $productDAO;

    public function __construct() {
        $this->productDAO = new ProductDAO();
    }

    public function getAllProducts() {
        try {
            return $this->productDAO->readAll();
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function getProductById($id) {
        try {
            if (!$id) {
                return ["success" => false, "message" => "Product ID is required"];
            }
            return $this->productDAO->readById($id);
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function createProduct($data) {
        try {
            if (!isset($data['name']) || !isset($data['description']) || !isset($data['price']) || 
                !isset($data['stock']) || !isset($data['category_id'])) {
                return ["success" => false, "message" => "Missing required fields"];
            }
            
            $result = $this->productDAO->create(
                $data['name'],
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['category_id']
            );
            
            return ["success" => true, "message" => "Product created successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function updateProduct($id, $data) {
        try {
            if (!$id) {
                return ["success" => false, "message" => "Product ID is required"];
            }
            
            if (!isset($data['name']) || !isset($data['description']) || !isset($data['price']) || 
                !isset($data['stock']) || !isset($data['category_id'])) {
                return ["success" => false, "message" => "Missing required fields"];
            }
            
            $result = $this->productDAO->update(
                $id,
                $data['name'],
                $data['description'],
                $data['price'],
                $data['stock'],
                $data['category_id']
            );
            
            return ["success" => true, "message" => "Product updated successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function deleteProduct($id) {
        try {
            if (!$id) {
                return ["success" => false, "message" => "Product ID is required"];
            }
            
            $result = $this->productDAO->delete($id);
            return ["success" => true, "message" => "Product deleted successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
?>