<?php

class User
{
    public static function usernameExists($conn, $username, $adminId = null)
    {
        $sql = "SELECT id FROM admins WHERE username = ?";
    
        if ($adminId) {
            $sql .= " AND id != ?";  // Exclude the current admin's username from the check
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
    

    public static function emailExists($conn, $email)
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public static function mobileNumberExists($conn, $mobilenumber)
{
    $sql = "SELECT id FROM users WHERE mobile_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mobilenumber);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

    public static function insertUser($conn, $username, $email, $mobilenumber, $hashedPassword, $firstname, $lastname, $age, $role)
    {
        $sql = "INSERT INTO users (username, email, mobile_number, password, firstname, lastname, age, role, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssis", $username, $email, $mobilenumber, $hashedPassword, $firstname, $lastname, $age, $role);
        return $stmt->execute();
    }

    public static function validateAge($age)
    {
        return $age >= 18;
    }

    public static function updateVerificationStatus($conn, $userId)
    {
        $update_sql = "UPDATE users SET is_verified = 'Verified' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $userId);
        return $update_stmt->execute();
    }

    public static function getUserByUsernameOrEmail($conn, $usernameOrEmail)
    {
        $sql = "SELECT id, username, email, password, is_verified, role FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function getUserIdByAccessToken($conn, $accessToken)
    {
        $sql = "SELECT user_id FROM access_tokens WHERE access_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $accessToken);
        $stmt->execute();
        $stmt->store_result();
        $userId = null;
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId);
            $stmt->fetch();
            return $userId;
        }
        return null;
    }

    public static function storeAccessToken($conn, $userId, $token)
    {
        $sql = "INSERT INTO access_tokens (user_id, access_token) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $token);
        return $stmt->execute();
    }
    
    public static function deleteAccessToken($conn, $userId)
    {   
        $conn = Database::getConnection();
        $sql = "DELETE FROM access_tokens WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
}
?>
