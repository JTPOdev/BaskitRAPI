<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/User.php';
require_once __DIR__ . '../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController
{
    public static function register($data, $conn)
    {
        $requiredFields = ['username', 'email', 'mobile_number', 'password', 'confirm_password', 'firstname', 'lastname', 'birth_month', 'birth_day', 'birth_year'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                header('HTTP/1.1 400 Bad Request');
                return ['message' => "Missing required field: $field"];
            }
        }
        
        $username = $data['username'];
        $email = $data['email'];
        $password = $data['password'];
        $confirm_password = $data['confirm_password'];
        $firstname = $data['firstname'];
        $lastname = $data['lastname'];
        $birth_month = $data['birth_month'];
        $birth_day = $data['birth_day'];
        $birth_year = $data['birth_year'];
        $mobile_number = $data['mobile_number'];


        $isMobile = isset($data['is_mobile']) && ($data['is_mobile'] === true || $data['is_mobile'] === 'true' || $data['is_mobile'] === 1);
        $role = $isMobile ? 'consumer' : 'tagabili';

        // Mobile Number Validation
        if (!preg_match('/^(09|\+639)\d{9}$/', $mobile_number)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid mobile number format. Use 09123456789 or +639123456789.'];
        }

        if (User::mobileNumberExists($conn, $mobile_number)) {
            header('HTTP/1.1 409 Conflict');
            return ['message' => 'Mobile number already exists.'];
        }

        // Birth month (1-12)
        if (!is_numeric($birth_month) || $birth_month < 1 || $birth_month > 12) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid birth month. Please enter a valid month.'];
        }

        // Birth day (1-31)
        if (!is_numeric($birth_day) || $birth_day < 1 || $birth_day > 31) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid birth day. Please enter a valid day.'];
        }

        // Birth year (1900-Current year)
        if (!is_numeric($birth_year) || $birth_year < 1900 || $birth_year > (new DateTime())->format("Y")) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid birth year. Please enter a valid year.'];
        }
    
        // Age Validation
        $age = self::calculateAge($birth_year, $birth_month, $birth_day);
        if ($age < 18) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'You must be at least 18 years old to register.'];
        }
    
        // Password Validation
        if ($password !== $confirm_password) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Passwords do not match'];
        }
        if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Password must be at least 8 characters long, contain at least one number, and one special character'];
        }
    
        // Username and Email Validation
        if (!preg_match('/^[a-zA-Z]+$/', $username) || strlen($username) < 6) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Username must contain only letters and be at least 6 characters long'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid email format'];
        }
        if (User::usernameExists($conn, $username)) {
            header('HTTP/1.1 409 Conflict');
            return ['message' => 'Username is already taken'];
        }
        if (User::emailExists($conn, $email)) {
            header('HTTP/1.1 409 Conflict');
            return ['message' => 'Email is already registered'];
        }
    
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        if (User::insertUser($conn, $username, $email, $mobile_number, $hashed_password, $firstname, $lastname, $age, $role)) {
            self::sendVerificationEmail($email);
            header('HTTP/1.1 201 Created');
            return ['message' => 'User registered successfully. Check your email for verification.'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Registration failed.'];
    }
    
    public static function calculateAge($birth_year, $birth_month, $birth_day)
    {
        $birth_date = new DateTime("$birth_year-$birth_month-$birth_day");
        $current_date = new DateTime();
        return $current_date->diff($birth_date)->y;
    }

    public static function sendVerificationEmail($email)
    {
        $verification_link = "http://192.168.100.111/BaskitAPI/verification/verify_email.php?email=" . urlencode($email);
    
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

    public static function login($data, $conn)
    {
        $usernameOrEmail = $data['username_or_email'];
        $password = $data['password'];

        $user = User::getUserByUsernameOrEmail($conn, $usernameOrEmail);

        if (!$user || !password_verify($password, $user['password'])) {
            header('HTTP/1.1 401 Unauthorized');
            return ['message' => 'Invalid credentials'];
        }

        if ($user['is_verified'] !== 'Verified') {
            header('HTTP/1.1 403 Forbidden');
            return ['message' => 'Please verify your email before logging in'];
        }

        $accessToken = bin2hex(random_bytes(32));

        if (!User::storeAccessToken($conn, $user['id'], $accessToken)) {
            header('HTTP/1.1 500 Internal Server Error');
            return ['message' => 'Login failed. Please try again later.'];
        }

        header('HTTP/1.1 200 OK');
        return [
            'message' => 'Login successful',
            'access_token' => $accessToken,
            'role' => $user['role']
        ];
    }

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
    
    
    
    
}
?>