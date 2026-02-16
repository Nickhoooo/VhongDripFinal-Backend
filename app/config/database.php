<?php

class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "vhongdrip_db";

    public function connect() {
        $conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if ($conn->connect_error) {
            throw new Exception("Database Connection Failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
