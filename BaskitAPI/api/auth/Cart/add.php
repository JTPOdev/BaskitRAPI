<?php
require_once '../../../config/database.php';
require_once '../../../controller/CartController.php';

$accessToken = AuthMiddleware::checkAuth();
$data = json_decode(file_get_contents("php://input"), true);
echo json_encode(CartController::addToCart($accessToken, $data, $conn));
