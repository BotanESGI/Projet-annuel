<?php
header("Content-Type: application/json");

$products = [
    ["id" => 1, "name" => "Produit 1", "price" => 100],
    ["id" => 2, "name" => "Produit 2", "price" => 200],
];

echo json_encode($products);
