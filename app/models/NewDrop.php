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
}
