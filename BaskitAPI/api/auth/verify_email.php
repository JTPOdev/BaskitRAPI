<?php
require_once '../../config/database.php';
require_once '../../model/User.php';
require_once '../../controller/UserController.php';

$email = $_GET['email'] ?? null;

$message = '';
$imageSrc = '../../image/Check.png';

if ($email) {
    $result = UserController::verifyEmail($conn, $email);
    $message = $result['success'] ?? $result['error'];
} else {
    $message = 'No email address provided.';
}

include '../../view/email_verified.html';
?>
