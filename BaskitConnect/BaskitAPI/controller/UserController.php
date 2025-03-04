<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/User.php';
require_once __DIR__ . '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController
{   

    // --------- REGISTER CONSUMER AND TAGABILI -------- //
    public static function register($data, $conn)
    {
        $requiredFields = ['username', 'email', 'mobile_number', 'password', 'confirm_password', 'birth_month', 'birth_day', 'birth_year'];
        $errors = [];
   
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "Missing required field: $field";
            }
        }
   
        // Mobile Number Validation
        $mobile_number = $data['mobile_number'];
        if (!preg_match('/^(09|\+639)\d{9}$/', $mobile_number)) {
            $errors['mobile_number'] = 'Invalid mobile number format. Use 09123456789 or +639123456789.';
        }
        if (User::mobileNumberExists($conn, $mobile_number)) {
            $errors['mobile_number'] = 'Mobile number already exists.';
        }
   
        // Birth month (1-12)
        $birth_month = $data['birth_month'];
        if (!is_numeric($birth_month) || $birth_month < 1 || $birth_month > 12) {
            $errors['birth_month'] = 'Invalid birth month. Please enter a valid month.';
        }
   
        // Birth day (1-31)
        $birth_day = $data['birth_day'];
        if (!is_numeric($birth_day) || $birth_day < 1 || $birth_day > 31) {
            $errors['birth_day'] = 'Invalid birth day. Please enter a valid day.';
        }
   
        // Birth year (1900-Current year)
        $birth_year = $data['birth_year'];
        if (!is_numeric($birth_year) || $birth_year < 1900 || $birth_year > (new DateTime())->format("Y")) {
            $errors['birth_year'] = 'Invalid birth year. Please enter a valid year.';
        }
   
        // Age Validation
        $age = self::calculateAge($birth_year, $birth_month, $birth_day);
        if ($age < 18) {
            $errors['age'] = 'You must be at least 18 years old to register.';
        }
   
        // Password Validation
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];
        if ($password !== $confirm_password) {
            $errors['password'] = 'Passwords do not match';
        }
        if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
            $errors['password'] = 'Must be at least 8 characters long, Contain at least one number, Contain one special character';
        }
   
        // Username and Email Validation
        $username = ucfirst(strtolower($data['username']));
        $email = strtolower($data['email']);
        if (!preg_match('/^[a-zA-Z]+$/', $username) || strlen($username) < 6) {
            $errors['username'] = 'Must be at least 6 characters long';
        }
   
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        if (User::usernameExists($conn, $username)) {
            $errors['username'] = 'Username is already taken';
        }
        if (User::emailExists($conn, $email)) {
            $errors['email'] = 'Email is already registered';
        }
   
        if (!empty($errors)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Registration failed', 'errors' => $errors]);
            exit;
        }
   
        $firstname = ucfirst(strtolower($data['firstname']));
        $lastname = ucfirst(strtolower($data['lastname']));
        $isMobile = isset($data['is_mobile']) && ($data['is_mobile'] === true || $data['is_mobile'] === 'true' || $data['is_mobile'] === 1);
        $role = $isMobile ? 'consumer' : 'tagabili';
   
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
   
        if (User::insertUser($conn, $username, $email, $mobile_number, $hashed_password, $firstname, $lastname, $age, $role)) {
            self::sendVerificationEmail($email);
            header('HTTP/1.1 201 Created');
            header('Content-Type: application/json');
            echo json_encode(['message' => 'User registered successfully. Check your email for verification.']);
            exit;
        }
   
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Registration failed.']);
        exit;
    }

    
    public static function calculateAge($birth_year, $birth_month, $birth_day)
    {
        $birth_date = new DateTime("$birth_year-$birth_month-$birth_day");
        $current_date = new DateTime();
        return $current_date->diff($birth_date)->y;
    }

    // --------- SEND VERIFICATION SA EMAIL -------- // 
    public static function sendVerificationEmail($email)
    {
        $verification_link = "http://10.40.97.123/BaskitAPI/verification/verify_email.php?email=" . urlencode($email);
    
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
            return ['message' => 'Failed to send verification email: ' . $mail->ErrorInfo];
        }
        return ['message' => 'Verification email sent.'];
    }
    
    // --------- VERY EMAIL -------- //
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
                header('HTTP/1.1 200 OK');
                return ['message' => 'Your email is already verified!'];
            }

            $update_sql = "UPDATE users SET is_verified = 'Verified' WHERE email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("s", $email);

            if ($update_stmt->execute()) {
                header('HTTP/1.1 200 OK');
                return ['message' => 'Your email has been successfully verified!'];
            } else {
                header('HTTP/1.1 500 Internal Server Error');
                return ['message' => 'Error verifying email. Please try again later.'];
            }
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'Invalid email address.'];
    }

    // --------- USER & TAGABILI LOGIN -------- //
    public static function login($data, $conn)
    {
        $errors = [];
        $usernameOrEmail = strtolower($data['username_or_email'] ?? '');
        $password = $data['password'] ?? '';
    
        if (empty($usernameOrEmail)) {
            $errors['username_or_email'] = "Username or Email is required.";
        }
    
        if (empty($password)) {
            $errors['password'] = "Password is required.";
        }
    
        if (!empty($errors)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'Login failed',
                'errors' => $errors
            ]);
            exit;
        }
    
        $user = User::getUserByUsernameOrEmail($conn, $usernameOrEmail);
    
        // Check if username/email exists first
        if (!$user) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'Invalid credentials',
                'errors' => ['username_or_email' => 'Username or email not found.']
            ]);
            exit;
        }
    
        // If user exists but password is incorrect
        if (!password_verify($password, $user['password'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'Invalid credentials',
                'errors' => ['password' => 'Incorrect password.']
            ]);
            exit;
        }
    
        // Check if user is verified
        if ($user['is_verified'] !== 'Verified') {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode([
                'message' => 'Please verify your email before logging in',
                'errors' => ['verification' => 'Your email is not verified.']
            ]);
            exit;
        }
    
        // Generate and store access token
        $accessToken = bin2hex(random_bytes(32));
    
        if (!User::storeAccessToken($conn, $user['id'], $accessToken)) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Login failed. Please try again later.']);
            exit;
        }
    
        // Successful login response
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Login successful',
            'access_token' => $accessToken,
            'role' => $user['role']
        ]);
        exit;
    }
    
        
    
    // --------- USER & TAGABILI LOGOUT -------- //
    public static function logout($conn)
    {
        $userId = AuthMiddleware::checkAuth();
    
        if (!$userId) {
            header('HTTP/1.1 401 Unauthorized');
            return ['message' => 'No access token provided.'];
        }
   
        if (!User::deleteAccessToken($conn, $userId)) {
            header('HTTP/1.1 500 Internal Server Error');
            return ['message' => 'Logout failed. Please try again later.'];
        }
    
        header('HTTP/1.1 200 OK');
        return ['message' => 'Logged out successfully.'];
    }
    
    // --------- USER & TAGABILI RESETPASSWORD -------- //
    public static function resetPassword($data, $conn) {
        $errors = [];
    
        $userId = AuthMiddleware::checkAuth();

        if (!$userId) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'User not authenticated']);
            exit;
        }
    
        if (!isset($data['new_password']) || !isset($data['confirm_password'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Missing password fields']);
            exit;
        }
    
        $newPassword = $data['new_password'];
        $confirm_password = $data['confirm_password'];
    
        if ($newPassword !== $confirm_password) {
            $errors['password'] = 'Passwords do not match';
        }
    
        if (strlen($newPassword) < 8 || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W_]/', $newPassword)) {
            $errors['password'] = 'Must be at least 8 characters long, contain at least one number, and contain one special character';
        }
    
        if (!empty($errors)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['errors' => $errors]);
            exit;
        }
    
        $HashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
        if (User::updatePassword($conn, $userId, $HashedPassword)) {
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode(['message' => 'Password updated successfully']);
            exit;
        } else {
            header('Content-Type: application/json');
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update password. Please try again later.']);
            exit;
        }
    }
}   
?>  