<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserController.php';

$data = json_decode(file_get_contents("php://input"), true);

$response = UserController::login($data, $conn);

header('Content-Type: application/json');
echo json_encode($response);
?>