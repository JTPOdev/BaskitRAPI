<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserController.php';

// Get the POST data (login credentials and remember me flag)
$data = json_decode(file_get_contents("php://input"), true);

// Call the login function from the UserController
$response = UserController::login($data, $conn);

// Return the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
?>