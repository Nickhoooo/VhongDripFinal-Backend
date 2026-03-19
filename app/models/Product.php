<?php
require_once __DIR__ . "/../config/database.php";

class Product
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function getAllProducts()
    {
        if (!$this->conn) {
            return false;
        }
        $sql = "SELECT * FROM products";
        return $this->conn->query($sql);
    }

    public function getLastError()
    {
        return $this->conn ? $this->conn->error : 'No connection';
    }
}
