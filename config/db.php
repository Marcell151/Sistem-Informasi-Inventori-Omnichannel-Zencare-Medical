<?php
// File: config/db.php
require_once __DIR__ . '/config.php';

class DB {
    private static $pdo = null;

    public static function connect() {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // If database does not exist, connect without database to run setup
                try {
                    $dsnWithoutDb = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
                    self::$pdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, $options);
                } catch (PDOException $ex) {
                    die("Database connection failed: " . $ex->getMessage());
                }
            }
        }
        return self::$pdo;
    }
}
?>
