<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controller/ProductController.php';

$accessToken = AuthMiddleware::checkAuth();

$category = $_GET['product_category'] ?? '';
$response = ProductController::getProductsByCategory($conn, $category);

header('Content-Type: application/json');
echo json_encode($response);
?>