<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/ProductController.php';


$accessToken = AuthMiddleware::checkAuth();
$data = json_decode(file_get_contents("php://input"), true);
$response = ProductController::create($data, $conn);

header('Content-Type: application/json');
echo json_encode($response);
?>