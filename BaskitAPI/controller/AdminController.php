<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/Admin.php';

class AdminController
{
    public static function register($data, $conn)
    {
        $username = $data['username'];
        $password = $data['password'];

        if (empty($username) || empty($password)) {
            return ['error' => 'Username and password are required.'];
        }

        if (Admin::usernameExists($conn, $username)) {
            return ['error' => 'Username is already taken'];
        }

        if (Admin::insertAdmin($conn, $username, $password)) {
            return ['success' => 'Admin registered successfully'];
        } else {
            return ['error' => 'Admin registration failed'];
        }
    }

    public static function login($data, $conn)
    {
        $username = $data['username'];
        $password = $data['password'];

        $admin = Admin::getAdminByUsername($conn, $username);

        if (!$admin) {
            return ['error' => 'Invalid credentials'];
        }

        if ($password !== $admin['password']) {
            return ['error' => 'Invalid credentials'];
        }

        $accessToken = bin2hex(random_bytes(32));

        if (!Admin::storeAccessToken($conn, $admin['id'], $accessToken)) {
            return ['error' => 'Login failed. Please try again later.'];
        }

        return [
            'success' => 'Login successful',
            'access_token' => $accessToken
        ];
    }
}
?>
