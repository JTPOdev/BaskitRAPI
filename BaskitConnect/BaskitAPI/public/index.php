<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, Accept');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug: Check if router.php path is correct
$routerPath = __DIR__ . '/../route/router.php';
if (!file_exists($routerPath)) {
    die(json_encode(["error" => "router.php not found at $routerPath"]));
}

// Require the router file
require_once $routerPath;
?>
