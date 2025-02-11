<?php

class Admin
{
    public static function usernameExists($conn, $username, $adminId = null)
    {
        $sql = "SELECT id FROM admins WHERE username = ?";
        if ($adminId) {
            $sql .= " AND id != ?";
        }
        $stmt = $conn->prepare($sql);

        if ($adminId) {
            $stmt->bind_param("si", $username, $adminId);
        } else {
            $stmt->bind_param("s", $username);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0;
    }
    
    public static function insertAdmin($conn, $username, $password)
    {
        $sql = "INSERT INTO admins (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password); 
        return $stmt->execute();
    }

    public static function getAdminByUsername($conn, $username)
    {
        $sql = "SELECT * FROM admins WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function storeAccessToken($conn, $adminId, $accessToken)
    {
        $sql = "INSERT INTO admin_access_tokens (admin_id, access_token) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $adminId, $accessToken);
        return $stmt->execute();
    }

    public static function getAdminIdByAccessToken($conn, $token)
    {
        $sql = "SELECT admin_id FROM admin_access_tokens WHERE access_token = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            die("Error in preparing query: " . $conn->error);
        }
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($conn->more_results()) {
            $conn->next_result();
        }
    
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $stmt->close();
            return $admin['admin_id'];
        }
        $stmt->close();
        return false;
    }
    
    public static function updateCredentials($conn, $adminId, $newUsername, $hashedPassword)
    {
        $sql = "UPDATE admins SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
    
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("ssi", $newUsername, $hashedPassword, $adminId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            echo "No rows were updated. Admin ID: $adminId, Username: $newUsername\n";
            return false;
        }
    }
    
    public static function deleteToken($conn, $adminId)
    {
        $sql = "DELETE FROM admin_access_tokens WHERE admin_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $adminId);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
?>
