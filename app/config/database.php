<?php
class Database
{
    private $host = "sql103.infinityfree.com";
    private $user = "if0_40869155";
    private $pass = "VhongDrip1122";
    private $db   = "if0_40869155_vhongsdrip";

    public function connect()
    {
        $conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->db
        );

        if ($conn->connect_error) {
            throw new Exception("Database Connection Failed: " . $conn->connect_error);
        }

        if (! $conn->set_charset('utf8mb4')) {
            error_log("[Database] unable to set charset: " . $conn->error);
        }

        return $conn;
    }
}
