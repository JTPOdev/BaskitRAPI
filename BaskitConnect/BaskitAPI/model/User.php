<?php

class User
{
    // --------- CHECK IF USERNAME EXISTS PARA SA UNIQUE USERNAMES -------- //
    public static function usernameExists($conn, $username, $adminId = null)
    {
        $sql = "SELECT id FROM users WHERE username = ?";
    
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
    
    // --------- CHECKS IF NAG EEXISTS YUNG EMAIL FOR UNIQUE -------- //
    public static function emailExists($conn, $email)
    {
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // --------- CHECK MOBILE NUMBER/CONTACT NUMBER IF EXISTS -------- //
    public static function mobileNumberExists($conn, $mobilenumber)
    {
        $sql = "SELECT id FROM users WHERE mobile_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $mobilenumber);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0;
    }

    // --------- ADD NEW REGISTERED ACCOUNT -------- //
    public static function insertUser($conn, $username, $email, $mobilenumber, $hashedPassword, $firstname, $lastname, $age, $role)
    {
        $sql = "INSERT INTO users (username, email, mobile_number, password, firstname, lastname, age, role, is_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssis", $username, $email, $mobilenumber, $hashedPassword, $firstname, $lastname, $age, $role);
        return $stmt->execute();
    }

    // --------- 18 ABOVE PWEDE MAG REGISTER -------- //
    public static function validateAge($age)
    {
        return $age >= 18;
    }

    // --------- UPDATE KUNG VERIFIED NA -------- //
    public static function updateVerificationStatus($conn, $userId)
    {
        $update_sql = "UPDATE users SET is_verified = 'Verified' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $userId);
        return $update_stmt->execute();
    }

    // --------- PARA SA LOGIN PWEDE GAMITIN USERNAME OR EMAIL -------- //
    public static function getUserByUsernameOrEmail($conn, $usernameOrEmail)
    {
        $sql = "SELECT id, username, email, password, is_verified, role FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // --------- GET USER ID BY ACCESS TOKEN -------- //
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

    // --------- GET USER BY ACCESS TOKEN -------- //
    public static function getUserDetails($conn, $userId)
    {
        $sql = "SELECT id, username, email, mobile_number, firstname, lastname, age, role, is_verified FROM users WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    
        if ($user) {
            return [
                "status" => "success",
                "user" => $user
            ];
        } else {
            http_response_code(404);
            return [
                "status" => "error",
                "message" => "User not found."
            ];
        }
    }
    


    // --------- STORE TOKEN SA ACCESS_TOKEN TABLE WHICH IS PARA SA USERS -------- //
    public static function storeAccessToken($conn, $userId, $token)
    {
        $sql = "INSERT INTO access_tokens (user_id, access_token) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $token);
        return $stmt->execute();
    }

    // --------- DELETE TOKEN FOR LOGOUT -------- //
    public static function deleteAccessToken($conn, $userId)
    {   
        $conn = Database::getConnection();
        $sql = "DELETE FROM access_tokens WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    // --------- RESET PASSWORD -------- //
    public static function updatePassword($conn, $userId, $HashedPassword){

        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
    
        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param("si", $HashedPassword, $userId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return true;
        } else {
            echo "No rows were updated. User ID: $userId";
            return false;
        }

    }
}
?>
