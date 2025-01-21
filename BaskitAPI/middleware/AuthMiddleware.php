<?php

class AuthMiddleware
{
    public static function checkAuth()
    {
        //authentication check
        $headers = apache_request_headers();

        if (isset($headers['Authorization']) && strpos($headers['Authorization'], 'Bearer ') === 0) {
            return true;
        }

        // Return an error message if no token is found
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['error' => 'Unauthorized access.']);
        exit;
    }
}
?>