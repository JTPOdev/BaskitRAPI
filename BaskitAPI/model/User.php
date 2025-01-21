<?php

class User
{
    public static function usernameExists($conn, $username)
    {
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
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

    // Insert a new user
    public static function insertUser($conn, $username, $email, $hashedPassword, $verificationToken)
    {
        $sql = "INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $verificationToken);
        return $stmt->execute();
    }

    public static function verifyEmail($conn, $verificationToken)
    {
        $sql = "SELECT id, username, is_verified FROM users WHERE verification_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $verificationToken);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Update user verification status
    public static function updateVerificationStatus($conn, $userId)
    {
        $update_sql = "UPDATE users SET is_verified = 'Verified' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $userId);
        return $update_stmt->execute();
    }

    // Retrieve a user by username or email for login
    public static function getUserByUsernameOrEmail($conn, $usernameOrEmail)
    {
        $sql = "SELECT id, username, email, password, is_verified, remember_token FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Store the access token when the user logs in
    public static function storeAccessToken($conn, $userId, $token)
    {
        $sql = "INSERT INTO access_tokens (user_id, access_token) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $token);
        return $stmt->execute(); // Store the token in the database
    }

    // Store the remember token when the user logs in with remember_me
    public static function storeRememberToken($conn, $userId, $rememberToken)
    {
        $sql = "INSERT INTO remember_tokens (user_id, remember_token) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $rememberToken);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update the remember token for the user (users table)
    public static function updateRememberToken($conn, $userId, $rememberToken)
    {
        $sql = "UPDATE users SET remember_token = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $rememberToken, $userId);
    
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete the access token when the user logs out
    public static function deleteAccessToken($conn, $userId)
    {
        $sql = "DELETE FROM access_tokens WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute(); // Delete the access token
    }

    public static function deleteRememberTokenFromTable($conn, $rememberToken)
    {
        // Prepare and execute the SQL query to delete the remember_token from the remember_tokens table
        $sql = "DELETE FROM remember_tokens WHERE remember_token = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $rememberToken);
    
        return $stmt->execute();
    }

    // Clear the remember token from the users table
    public static function clearRememberToken($conn, $userId)
    {
        $sql = "UPDATE users SET remember_token = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute(); // Clear the remember token in the users table
    }
}
?>