<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/ProductController.php';

$accessToken = AuthMiddleware::checkAuth();

$response = ProductController::getSpecificProductByid($id, $conn);

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>