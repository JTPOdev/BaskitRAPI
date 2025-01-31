<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/ProductController.php';

$accessToken = AuthMiddleware::checkAuth($conn);

$response = ProductController::getProductsByCategoryFruit($conn);
$response = ProductController::getProductsByCategoryVegetable($conn);
$response = ProductController::getProductsByCategoryMeat($conn);
$response = ProductController::getProductsByCategoryFish($conn);
$response = ProductController::getProductsByCategoryFrozen($conn);
$response = ProductController::getProductsByCategorySpice($conn);

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>