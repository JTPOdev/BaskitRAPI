<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/StoreController.php';

header('Content-Type: application/json');

$accessToken = AuthMiddleware::checkAuth($conn);
$data = json_decode(file_get_contents("php://input"), true);
$response = StoreController::create($accessToken, $data, $conn);
echo json_encode($response);
?>