<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $conn;

    public function __construct() {
        $this->conn = (new Database())->connect();
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM products";
        return $this->conn->query($sql);
    }
}
