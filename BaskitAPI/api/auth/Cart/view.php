<?php

require_once '../../../config/database.php';
require_once '../../../controller/CartController.php';

header('Content-Type: application/json');
$accessToken = AuthMiddleware::checkAuth();
if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

$userId = intval($_GET['user_id']);
$response = CartController::viewCart($accessToken, $conn);
echo json_encode($response);
