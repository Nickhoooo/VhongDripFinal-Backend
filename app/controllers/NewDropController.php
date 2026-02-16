<?php
require_once __DIR__ . '/../models/NewDrop.php';


class NewDropController {

    public function index() {
        try {
            $newDrop = new NewDrops();
            $result = $newDrop->getNewDrop();

            if (!$result) {
                http_response_code(500);
                echo json_encode(["error" => "Query failed: " . $newDrop->conn->error]);
                return;
            }

            $drops = [];

            while ($row = $result->fetch_assoc()) {
                $drops[] = $row;
            }

            echo json_encode($drops);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Server error: " . $e->getMessage()]);
        }
    }
}
