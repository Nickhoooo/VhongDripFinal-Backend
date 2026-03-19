<?php
header("Access-Control-Allow-Origin: https://vhongsdrip.great-site.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . "/../models/UserModel.php";

class UserController {
    private $model;

    public function __construct(){
        $db = new Database();
        $conn = $db->connect();
        $this->model = new UserModel($conn);
    }

    public function getUsers(){
        $users = $this->model->getAllUsers();
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    public function block(){
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? 0;
        $this->model->blockUser($id);
        echo json_encode(["ok"=>true]);
    }

    public function delete(){
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'] ?? 0;
        $this->model->deleteUser($id);
        echo json_encode(["ok"=>true]);
    }
}