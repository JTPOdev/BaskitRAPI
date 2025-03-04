<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

class AuthMiddleware
{
    public static function checkAuth($isAdminRoute = false)
    {
        $headers = apache_request_headers();

        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            $accessToken = trim(str_replace('Bearer', '', $headers['Authorization']));
            $conn = Database::getConnection();

            if ($isAdminRoute) {

                $adminId = Admin::getAdminIdByAccessToken($conn, $accessToken);
                
                if ($adminId){
                    return $adminId;
                }
            } else {
                $userId = User::getUserIdByAccessToken($conn, $accessToken);

                if ($userId) {
                    return $userId;
                }

                $adminId = Admin::getAdminIdByAccessToken($conn, $accessToken);
                if ($adminId) {
                    return $adminId;
                }
            }
        }
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['message' => 'Unauthorized access.']);
        exit;
    }
}
?>
