<?php
require_once __DIR__ . '/../dao/CategoryDAO.php';

class CategoryService {
    private $categoryDAO;

    public function __construct() {
        $this->categoryDAO = new CategoryDAO();
    }

    public function getAllCategories() {
        return $this->categoryDAO->readAll();
    }

    public function createCategory($data) {
        return $this->categoryDAO->create($data['name'], $data['description']);
    }

    public function updateCategory($id, $data) {
        return $this->categoryDAO->update($id, $data['name'], $data['description']);
    }

    public function deleteCategory($id) {
        return $this->categoryDAO->delete($id);
    }
}
?>