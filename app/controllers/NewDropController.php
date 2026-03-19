<?php
require_once __DIR__ . '/../models/NewDrop.php';
move_uploaded_file($tmp, __DIR__ . "/../../images/" . $imageName);
error_log("Saving to: " . $uploadPath);
error_log("Dir exists: " . (is_dir(dirname($uploadPath)) ? 'YES' : 'NO'));
error_log("Dir writable: " . (is_writable(dirname($uploadPath)) ? 'YES' : 'NO'));
error_log("Upload success: " . (move_uploaded_file($tmp, $uploadPath) ? 'YES' : 'NO'));

$imageName = null;

if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
    $imageName = time() . "_" . $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $uploadPath = __DIR__ . "/../../images/" . $imageName;
    
    if(!move_uploaded_file($tmp, $uploadPath)){
        error_log("Upload failed. tmp: $tmp | dest: $uploadPath");
    }
}

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

    public function add()
    {
    header("Content-Type: application/json");

    try {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            return;
        }

        $name = $_POST['name'] ?? '';
        $price = $_POST['price'] ?? '';
        $category = $_POST['category'] ?? '';
        $details = $_POST['details'] ?? '';

        if(!$name || !$price || !$category || !$details){
            http_response_code(400);
            echo json_encode(["error"=>"All fields required"]);
            return;
        }

        // image upload
        $imageName = null;

        if(isset($_FILES['image'])){
            $imageName = time() . "_" . $_FILES['image']['name'];
            $tmp = $_FILES['image']['tmp_name'];

            move_uploaded_file($tmp, __DIR__ . "/../images/" . $imageName);
        }

        $newDrop = new NewDrops();

        $result = $newDrop->addNewDrop(
            $name,
            $price,
            $imageName,
            $category,
            $details
        );

        if(!$result){
            echo json_encode(["error"=>"Insert failed"]);
            return;
        }

        echo json_encode([
            "success"=>true,
            "message"=>"Product added successfully"
        ]);

    } catch(Exception $e){
        http_response_code(500);
        echo json_encode([
            "error"=>$e->getMessage()
        ]);
    }
    }

        public function delete()
         {
        try {

            $data = json_decode(file_get_contents("php://input"), true);
            $id = $data['id'] ?? 0;

            if (!$id) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid ID"]);
                return;
            }

            $conn = (new Database())->connect();
            $stmt = $conn->prepare("DELETE FROM newdrop WHERE id=?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["error" => $conn->error]);
            }

        } catch (Exception $e) {

            error_log("[newdropController delete] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["error" => "Server error"]);

        }
    }
}
