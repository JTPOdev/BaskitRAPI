<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/Admin.php';
require_once __DIR__ . '/../../controller/AdminController.php';

$data = json_decode(file_get_contents("php://input"), true);
$response = AdminController::login($data, $conn);

header('Content-Type: application/json');
echo json_encode($response);
?>