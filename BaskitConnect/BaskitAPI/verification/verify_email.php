<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../controller/UserController.php';

$conn = Database::getConnection();
$email = $_GET['email'] ?? null;

$message = '';
$imageSrc = '../image/Check.png';

if ($email) {
    $result = UserController::verifyEmail($conn, $email);
    $message = $result['message'] ?? $result['message'];
} else {
    $message = 'No email address provided.';
}

include '../view/email_verified.html';
?>
 