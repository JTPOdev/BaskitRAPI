<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/StoreController.php';

header('Content-Type: application/json');

$accessToken = AuthMiddleware::checkAuth($conn);
$response = StoreController::list($accessToken, $conn);
echo json_encode($response);
?>