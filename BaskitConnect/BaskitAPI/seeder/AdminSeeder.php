<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../model/Admin.php';

class AdminSeeder
{
    public static function seedAdminAccount($conn)
    {
        $username = 'admin1';
        $password = 'adminpassword';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (Admin::insertAdmin($conn, $username, $hashedPassword)) {
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