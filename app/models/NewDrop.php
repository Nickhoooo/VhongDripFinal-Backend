<?php

require_once __DIR__ . "/../config/database.php";

class NewDrops
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function getNewDrop()
    {
        $sql = "SELECT * FROM newdrop";
        return $this->conn->query($sql);
    }

    public function getLastError()
    {
        return $this->conn ? $this->conn->error : 'No connection';
    }

    public function addNewDrop($name,$price,$image,$category,$details)
{
    $query = "INSERT INTO newdrop 
    (name,price,image,category,details)
    VALUES (?,?,?,?,?)";

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param(
        "sdsss",
        $name,
        $price,
        $image,
        $category,
        $details
    );

    return $stmt->execute();
}
}
