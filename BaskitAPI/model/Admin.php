<?php

class Admin
{
    public static function usernameExists($conn, $username)
    {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public static function insertAdmin($conn, $username, $password)
    {
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password); 
        return $stmt->execute();
    }

    public static function getAdminByUsername($conn, $username)
    {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function storeAccessToken($conn, $adminId, $accessToken)
    {
        $stmt = $conn->prepare("INSERT INTO admin_access_tokens (admin_id, access_token) VALUES (?, ?)");
        $stmt->bind_param("is", $adminId, $accessToken);
        return $stmt->execute();
    }
    public static function getAdminIdByAccessToken($conn, $token)
    {
        $stmt = $conn->prepare("SELECT id FROM admin_access_tokens WHERE access_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        return $admin ? $admin['id'] : false;
    }
}
?>
