<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/User.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '../../vendor/autoload.php';

class UserController
{
    public static function register($data, $conn)
    {
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];

        if ($password !== $confirm_password) {
            return ['error' => 'Passwords do not match'];
        }

        if (strlen($password) < 8) {
            return ['error' => 'Password must be at least 8 characters long'];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return ['error' => 'Password must contain at least one number'];
        }

        if (!preg_match('/[\W_]/', $password)) { // Special character check
            return ['error' => 'Password must contain at least one special character'];
        }

        // Check if username or email already exists
        if (User::usernameExists($conn, $username)) {
            return ['error' => 'Username is already taken'];
        }

        if (User::emailExists($conn, $email)) {
            return ['error' => 'Email is already registered'];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32)); // Generate a unique token for email verification

        // Insert the new user into the database
        if (User::insertUser($conn, $username, $email, $hashed_password, $verification_token)) {
            self::sendVerificationEmail($email, $verification_token);
            return ['success' => 'User registered successfully. Check your email for verification.'];
        }

        return ['error' => 'Registration failed.'];
    }

    public static function sendVerificationEmail($email, $verification_token)
    {
        // Generate the verification link
        $verification_link = "http://192.168.100.111/BaskitAPI/api/auth/verify_email.php?token=" . $verification_token;
    
        // Load the HTML template
        $htmlContent = file_get_contents(__DIR__ . '/../view/email_verification.html');
    
        // Replace the placeholder with the actual verification link
        $htmlContent = str_replace('{{verification_link}}', $verification_link, $htmlContent);
    
        // Setup PHPMailer
        $mail = new PHPMailer(true);
    
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'baskitofficial@gmail.com';
            $mail->Password = 'wmdxtnwkcgohtdqh';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $mail->setFrom('baskitofficial@gmail.com', 'Baskit');
            $mail->addAddress($email);
            $mail->Subject = 'Email Verification';
    
            // Set the body to the HTML content
            $mail->isHTML(true);
            $mail->Body    = $htmlContent;
    
            // Send the email
            $mail->send();
        } catch (Exception $e) {
            return ['error' => 'Failed to send verification email. Mailer Error: ' . $mail->ErrorInfo];
        }
    }

    public static function verifyEmail($verification_token, $conn)
    {
        // Check if the token is valid
        $user = User::verifyEmail($conn, $verification_token);

        if ($user) {
            if ($user['is_verified'] === 'Verified') {
                return ['success' => 'Your email is already verified!'];
            } else {
                // Update the verification status if the token is valid and not verified yet
                $update_status = User::updateVerificationStatus($conn, $user['id']);
                
                if ($update_status) {
                    return ['success' => 'Your email has been successfully verified!'];
                } else {
                    return ['error' => 'Error verifying email. Please try again later.'];
                }
            }
        }

        return ['error' => 'Invalid verification token.'];
    }

    public static function login($data, $conn)
    {
        // Extract the submitted data (username/email, password, remember me flag)
        $usernameOrEmail = $data['username_or_email'];
        $password = $data['password'];
        $rememberMe = isset($data['remember_me']) ? $data['remember_me'] : false;
    
        // Check if user exists by username or email
        $user = User::getUserByUsernameOrEmail($conn, $usernameOrEmail);
    
        if (!$user) {
            return ['error' => 'Invalid credentials']; // If user not found
        }
    
        // Verify the password using password_verify
        if (!password_verify($password, $user['password'])) {
            return ['error' => 'Invalid credentials']; // Invalid password
        }
    
        // Check if the user is verified
        if ($user['is_verified'] !== 'Verified') {
            return ['error' => 'Please verify your email before logging in']; // Email not verified
        }
    
        // Generate a secure access token (JWT or custom token) for the session
        $accessToken = bin2hex(random_bytes(32)); // Generate a secure session token
    
        // Store the access token in the access_tokens table (for session management)
        if (!User::storeAccessToken($conn, $user['id'], $accessToken)) {
            return ['error' => 'Login failed. Please try again later.']; // If storing token failed
        }
    
        // If "remember me" is checked, generate and store a remember token
        if ($rememberMe) {
            // Generate a unique remember token
            $rememberToken = bin2hex(random_bytes(32)); // Generate a random remember token
            // Store the remember token in the remember_tokens table (not the access_tokens table)
            if (!User::storeRememberToken($conn, $user['id'], $rememberToken)) {
                return ['error' => 'Failed to store remember token. Please try again.']; // Error storing remember token
            }
    
            // Update the remember_token field in the users table
            if (!User::updateRememberToken($conn, $user['id'], $rememberToken)) {
                return ['error' => 'Failed to update remember token in user table.']; // Error updating remember token
            }
        }
    
        // Return the generated access token for the current session
        return [
            'success' => 'Login successful',
            'access_token' => $accessToken
        ];
    }

    public static function logout($data, $conn)
{
    // Extract the user ID and remember_token (if any) from the input data
    $userId = $data['user_id'];
    $rememberToken = isset($data['remember_token']) ? $data['remember_token'] : null;

    // Delete the user's access token from the access_tokens table (for session logout)
    if (!User::deleteAccessToken($conn, $userId)) {
        return ['error' => 'Logout failed. Please try again later.']; // If token deletion failed
    }

    // If a remember token exists, clear it from the remember_tokens table
    if ($rememberToken) {
        if (!User::deleteRememberTokenFromTable($conn, $rememberToken)) {
            return ['error' => 'Failed to remove remember token. Please try again.']; // Error removing remember token
        }

        // Clear the remember_token field in the users table (if applicable)
        User::clearRememberToken($conn, $userId);
    }

    // Return success if logout was successful
    return ['success' => 'Logged out successfully'];
}
}
?>