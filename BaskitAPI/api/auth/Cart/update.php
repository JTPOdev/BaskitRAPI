<?php

require_once '../../../config/database.php';
require_once '../../../controller/CartController.php';

header('Content-Type: application/json');
$accessToken = AuthMiddleware::checkAuth();
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['product_quantity'])) {
    echo json_encode(['error' => 'Cart item ID and quantity are required']);
    exit;
}

$response = CartController::updateCart($data, $conn);
echo json_encode($response);
