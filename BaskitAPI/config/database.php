<?php 
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'baskitdb';

$conn = new mysqli($host, $username,$password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>