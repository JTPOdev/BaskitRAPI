<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/Admin.php';

class AdminController
{
    public static function changeCredentials($data, $conn)
    {
        $adminId = AuthMiddleware::checkAuth(true);
        
        $newUsername = $data['new_username'];
        $newPassword = $data['new_password'];
    
        if (empty($newUsername) || empty($newPassword)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Both username and password are required.'];
        }
    
        if (Admin::usernameExists($conn, $newUsername, $adminId)) {
            header('HTTP/1.1 409 Conflict');
            return ['message' => 'Username is already taken'];
        }
    
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        if (Admin::updateCredentials($conn, $adminId, $newUsername, $hashedPassword)) {
            header('HTTP/1.1 200 OK');
            return ['message' => 'Credentials updated successfully.'];
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            return ['message' => 'Failed to update credentials. Please try again later.'];
        }
    }

    public static function login($data, $conn)
    {
        $username = $data['username'];
        $password = $data['password'];

        $admin = Admin::getAdminByUsername($conn, $username);

        if (!$admin) {
            header('HTTP/1.1 401 Unauthorized');
            return ['message' => 'Invalid credentials'];
        }

        if (!password_verify($password, $admin['password'])) {
            header('HTTP/1.1 401 Unauthorized');
            return ['message' => 'Invalid credentials'];
        }

        $accessToken = bin2hex(random_bytes(32));

        if (!Admin::storeAccessToken($conn, $admin['id'], $accessToken)) {
            header('HTTP/1.1 500 Internal Server Error');
            return ['message' => 'Login failed. Please try again later.'];
        }

        header('HTTP/1.1 200 OK');
        return [
            'message' => 'Login successful',
            'access_token' => $accessToken
        ];
    }

    public static function logout($conn)
    {
        $adminId = AuthMiddleware::checkAuth(true);
        $conn = Database::getConnection();

        if ($adminId) {
            if (Admin::deleteToken($conn, $adminId)) {
                header('HTTP/1.1 200 OK');
                return ['message' => 'Successfully logged out'];
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                return ['message' => 'Failed to log out. Please try again later.'];
            }
        }

        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['message' => 'Unauthorized access.']);
        exit;
    }
}
?>
