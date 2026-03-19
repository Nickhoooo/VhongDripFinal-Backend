<?php
require_once __DIR__ . "/../config/database.php";
class UserModel {
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

    public function getAllUsers(){
    $query = "SELECT id, username, email, status, is_verified FROM users WHERE role='user' ORDER BY created_at DESC";
    $result = $this->conn->query($query);
    $users = [];
    while($row = $result->fetch_assoc()){
        $users[] = $row;
    }
    return $users;
}

    public function blockUser($id){
        $query = "UPDATE users SET status = CASE WHEN status='active' THEN 'blocked' ELSE 'active' END WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deleteUser($id){
        $query = "DELETE FROM users WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}