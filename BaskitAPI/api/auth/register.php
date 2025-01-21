<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserController.php';

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

require_once __DIR__ . '/../../config/database.php';

// Call the register function from the UserController
$response = UserController::register($data, $conn);

// Return the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
?>