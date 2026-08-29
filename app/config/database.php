<?php
/**
 * IRONCORE Fitness & Gym Management System
 * Database Connection Manager (PDO Singleton)
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get Singleton PDO Instance
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = getenv('DB_PORT') ?: '3306';
            $db   = getenv('DB_NAME') ?: 'ironcore_gym';
            $user = getenv('DB_USER') ?: 'root';
            $pass = getenv('DB_PASS') ?: '';
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Log connection error securely without exposing credentials
                if (defined('STORAGE_PATH')) {
                    error_log("[" . date('Y-m-d H:i:s') . "] DB Connection Error: " . $e->getMessage() . "\n", 3, STORAGE_PATH . '/logs/app.log');
                }
                
                if (APP_DEBUG) {
                    throw new Exception("Database Connection Failed: " . $e->getMessage());
                } else {
                    die("Database connection error. Please contact system administrator.");
                }
            }
        }

        return self::$instance;
    }
}
