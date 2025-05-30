<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
        echo json_encode(['message' => 'API fonctionne !']);
    }
}