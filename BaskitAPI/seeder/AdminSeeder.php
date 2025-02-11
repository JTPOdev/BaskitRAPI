<?php
require_once __DIR__ . '../../config/database.php';

class AdminSeeder
{
    public static function seedAdminAccount($conn)
    {
        $username = 'admin';
        $password = 'adminpassword';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $checkStmt->execute([$username]);

        if ($checkStmt->fetch()) {
            echo "Admin account already exists.\n";
            return;
        }

        $insertStmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        if ($insertStmt->execute([$username, $hashedPassword])) {
            echo "Admin account created successfully!\n";
            echo "Username: $username\n";
            echo "Password: $password\n";
        } else {
            echo "Failed to create admin account.\n";
        }
    }
}

$conn = Database::getConnection();
AdminSeeder::seedAdminAccount($conn);
?>
