<?php
require_once __DIR__ . "/../config/database.php";
class DashboardModel {

    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function totalUsers(){
        $query = "SELECT COUNT(*) AS total FROM users WHERE role='user'";
        $result = mysqli_query($this->conn,$query);
        return mysqli_fetch_assoc($result)['total'];
    }

    public function verifiedUsers(){
        $query = "SELECT COUNT(*) AS total FROM users WHERE is_verified=1";
        $result = mysqli_query($this->conn,$query);
        return mysqli_fetch_assoc($result)['total'];
    }

    public function pendingPayments(){
        $query = "SELECT COUNT(*) AS total FROM users WHERE payment_status='pending'";
        $result = mysqli_query($this->conn,$query);
        return mysqli_fetch_assoc($result)['total'];
    }

    public function blockedUsers(){
        $query = "SELECT COUNT(*) AS total FROM users WHERE status='blocked'";
        $result = mysqli_query($this->conn,$query);
        return mysqli_fetch_assoc($result)['total'];
    }
}