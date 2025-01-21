<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserController.php';

// Get the POST data (user ID and remember token)
$data = json_decode(file_get_contents("php://input"), true);

// Call the logout function from the UserController
$response = UserController::logout($data, $conn);

// Return the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
?>