<?php
header("Access-Control-Allow-Origin: https://vhongsdrip.great-site.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once __DIR__ . "/../models/DashboardModel.php";

class DashboardController {

    public function getStats(){
        $db = new Database();
        $conn = $db->connect();

        $dashboard = new DashboardModel($conn);

        $data = [
            "totalUsers" => $dashboard->totalUsers(),
            "verifiedUsers" => $dashboard->verifiedUsers(),
            "pendingPayments" => $dashboard->pendingPayments(),
            "blockedUsers" => $dashboard->blockedUsers()
        ];

        // return JSON to React
        header('Content-Type: application/json');
        echo json_encode($data);
    }

}