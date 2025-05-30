<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['message' => 'Page d\'accueil']);
}
else
{
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}
