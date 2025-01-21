<?php
require_once '../../config/database.php';
require_once '../../model/User.php';
require_once '../../controller/UserController.php';

// Get the token from the URL
$verification_token = $_GET['token'] ?? null;

$message = '';
$imageSrc = '../../image/Check.png'; // Path to your verification image

// Check if a token is provided
if ($verification_token) {
    // Verify the email using the token
    $result = UserController::verifyEmail($verification_token, $conn);

    // If the verification was successful, show a success message
    if (isset($result['success'])) {
        $message = $result['success'];
    } else {
        $message = $result['error'];
    }
} else {
    $message = 'No verification token provided.';
}

// Include the view and pass the variables
include '../../view/email_verified.html';
?>