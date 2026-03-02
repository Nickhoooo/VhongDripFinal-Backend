<?php
require_once __DIR__ . '/../models/NewDrop.php';


class NewDropController
{

    public function index()
    {
        try {
            $newDrop = new NewDrops();
            $result = $newDrop->getNewDrop();

            if (!$result) {
                $err = $newDrop->getLastError();
                error_log("[NewDropController] query error: " . $err);
                http_response_code(500);
                echo json_encode(["error" => "Query failed: " . $err]);
                return;
            }

            $drops = [];

            while ($row = $result->fetch_assoc()) {
                $drops[] = $row;
            }

            echo json_encode($drops);
        } catch (Exception $e) {
            error_log("[NewDropController] exception: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Server error: " . $e->getMessage()]);
        }
    }
}
