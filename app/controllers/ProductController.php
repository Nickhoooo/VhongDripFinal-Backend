<?php
require_once __DIR__ . '/../models/Product.php';


class ProductController
{

    public function index()
    {
        try {
            error_log("[ProductController] index start");

            $product = new Product();
            error_log("[ProductController] Product constructed");

            $result = $product->getAllProducts();
            error_log("[ProductController] query executed");

            if (!$result) {
                // query failed, log and return error
                $err = $product->getLastError();
                error_log("[ProductController] query error: " . $err);
                http_response_code(500);
                $out = json_encode(["error" => "Query failed: " . $err]);
                error_log("[ProductController] output (error) length: " . strlen($out));
                echo $out;
                return;
            }

            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }

            $out = json_encode($products);
            error_log("[ProductController] products count: " . count($products));
            if ($out === false) {
                error_log("[ProductController] json_encode returned false, error: " . json_last_error_msg());
                $len = 'false';
            } else {
                $len = strlen($out);
            }
            error_log("[ProductController] output length: " . $len);
            echo $out;
        } catch (Exception $e) {
            error_log("[ProductController] exception: " . $e->getMessage());
            http_response_code(500);
            $out = json_encode(["error" => "Server error: " . $e->getMessage()]);
            if ($out === false) {
                error_log("[ProductController] json_encode(exception) returned false: " . json_last_error_msg());
            }
            error_log("[ProductController] output (exception) length: " . strlen($out));
            echo $out;
        }
    }
}
