<?php
require_once __DIR__ . '/../models/Product.php';


class ProductController {

    public function index() {
        $product = new Product();
        $result = $product->getAllProducts();

        $products = [];

        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        echo json_encode($products);
    }
}
