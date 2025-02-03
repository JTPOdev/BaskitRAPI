<?php
class Database
{
    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = '';
    private static $dbname = 'baskitdb';
    private static $conn = null;

    public static function getConnection()
    {
        if (self::$conn === null) {
            self::$conn = new mysqli(self::$host, self::$username, self::$password, self::$dbname);
            
            if (self::$conn->connect_error) {
                die("Database connection failed: " . self::$conn->connect_error);
            }
        }

        return self::$conn;
    }
}
?>
