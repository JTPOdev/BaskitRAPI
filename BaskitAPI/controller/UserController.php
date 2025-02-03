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
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $birth_month = $data['birth_month'];
        $birth_day = $data['birth_day'];
        $birth_year = $data['birth_year'];

        // Birth month (1-12)
        if (!is_numeric($birth_month) || $birth_month < 1 || $birth_month > 12) {
            return ['error' => 'Invalid birth month. Please enter a valid month.'];
        }

        // Birth day (1-31)
        if (!is_numeric($birth_day) || $birth_day < 1 || $birth_day > 31) {
            return ['error' => 'Invalid birth day. Please enter a valid day.'];
        }

        // Birth year (1900-Current year)
        if (!is_numeric($birth_year) || $birth_year < 1900 || $birth_year > (new DateTime())->format("Y")) {
            return ['error' => 'Invalid birth year. Please enter a valid year.'];
        }
    
        // Calculate the users age
        $age = self::calculateAge($birth_year, $birth_month, $birth_day);
    
        // Age
        if ($age < 18) {
            return ['error' => 'You must be at least 18 years old to register.'];
        }
    
        // Password
        if ($password !== $confirm_password) {
            return ['error' => 'Passwords do not match'];
        }
    
        if (strlen($password) < 8) {
            return ['error' => 'Password must be at least 8 characters long'];
        }
    
        if (!preg_match('/[0-9]/', $password)) {
            return ['error' => 'Password must contain at least one number'];
        }
    
        if (!preg_match('/[\W_]/', $password)) {
            return ['error' => 'Password must contain at least one special character'];
        }
    
        // Username and email (no spaces or special chars)
        if (!preg_match('/^[a-zA-Z]+$/', $username)) {
            return ['error' => 'Username can only contain letters'];
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Invalid email format'];
        }
    
        if (User::usernameExists($conn, $username)) {
            return ['error' => 'Username is already taken'];
        }
    
        if (User::emailExists($conn, $email)) {
            return ['error' => 'Email is already registered'];
        }
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        if (User::insertUser($conn, $username, $email, $hashed_password, $firstname, $lastname, $age)) {
            self::sendVerificationEmail($email);
            return ['success' => 'User registered successfully. Check your email for verification.'];
        }
        return ['error' => 'Registration failed.'];
    }
    
    public static function calculateAge($birth_year, $birth_month, $birth_day)
    {
        $birth_date = new DateTime("$birth_year-$birth_month-$birth_day");
        $current_date = new DateTime();
        $age = $current_date->diff($birth_date)->y;
        return $age;
    }


    public static function sendVerificationEmail($email)
    {
        $verification_link = "http://192.168.100.111/BaskitAPI/api/auth/verify_email.php?email=" . urlencode($email);
    
        $htmlContent = file_get_contents(__DIR__ . '/../view/email_verification.html');
        $htmlContent = str_replace('{{verification_link}}', $verification_link, $htmlContent);
    
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'baskitofficial@gmail.com';
            $mail->Password = 'dpbqebttludcaeqo';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
    
            $mail->setFrom('baskitofficial@gmail.com', 'Baskit');
            $mail->addAddress($email);
            $mail->Subject = 'Verify Your Email';
            $mail->isHTML(true);
            $mail->Body = $htmlContent;
    
            $mail->send();
        } catch (Exception $e) {
            return ['error' => 'Failed to send verification email: ' . $mail->ErrorInfo];
        }
        return ['success' => 'Verification email sent.'];
    }
    

    public static function verifyEmail($conn, $email)
    {
        $sql = "SELECT id, is_verified FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($user['is_verified'] == 'Verified') {
                return ['success' => 'Your email is already verified!'];
            }

            $update_sql = "UPDATE users SET is_verified = 'Verified' WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("s", $email);

            if ($update_stmt->execute()) {
                return ['success' => 'Your email has been successfully verified!'];
            } else {
                return ['error' => 'Error verifying email. Please try again later.'];
            }
        }
        return ['error' => 'Invalid email address.'];
    }

    
    public static function login($data, $conn)
    {
        $usernameOrEmail = $data['username_or_email'];
        $password = $data['password'];

        $user = User::getUserByUsernameOrEmail($conn, $usernameOrEmail);

        if (!$user) {
            return ['error' => 'Invalid credentials'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['error' => 'Invalid credentials'];
        }

        if ($user['is_verified'] !== 'Verified') {
            return ['error' => 'Please verify your email before logging in'];
        }

        $accessToken = bin2hex(random_bytes(32));

        if (!User::storeAccessToken($conn, $user['id'], $accessToken)) {
            return ['error' => 'Login failed. Please try again later.'];
        }
        return [
            'success' => 'Login successful',
            'access_token' => $accessToken
        ];
    }

    public static function logout($data, $conn)
{
    $accessToken = AuthMiddleware::checkAuth();

    if (!$accessToken) {
        return ['error' => 'No access token provided.'];
    }

    $userId = User::getUserIdByAccessToken($conn, $accessToken);
    if (!$userId) {
        return ['error' => 'Invalid or expired access token.'];
    }

    if (!User::deleteAccessToken($conn, $userId, $accessToken)) {
        return ['error' => 'Logout failed. Please try again later.'];
    }
    return ['success' => 'Logged out successfully.'];
}
}
