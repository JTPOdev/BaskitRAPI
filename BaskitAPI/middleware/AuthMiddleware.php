<?php

class AuthMiddleware
{
    public static function checkAuth()
    {
        $headers = apache_request_headers();

        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            $accessToken = trim(str_replace('Bearer', '', $headers['Authorization']));
            return $accessToken;
        }

        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['error' => 'Unauthorized access.']);
        exit;
    }
}
?>