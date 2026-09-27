<?php
/**
 * Database Connection Configuration (PDO)
 * EcoCollect Management Platform
 */

require_once __DIR__ . '/env.php';

function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $db   = env('DB_NAME', 'ecocollect');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASSWORD', '');
        $charset = 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // If database does not exist (code 1049), attempt auto-creation
            if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
                    $serverPdo = new PDO($serverDsn, $user, $pass, $options);
                    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Reconnect to newly created database
                    $pdo = new PDO($dsn, $user, $pass, $options);

                    // Import schema & demo data if tables are missing
                    $sqlPath = __DIR__ . '/../database/database.sql';
                    $demoSqlPath = __DIR__ . '/../database/demo-data.sql';
                    if (file_exists($sqlPath)) {
                        $pdo->exec(file_get_contents($sqlPath));
                    }
                    if (file_exists($demoSqlPath)) {
                        $pdo->exec(file_get_contents($demoSqlPath));
                    }
                } catch (Exception $e2) {
                    error_log("Database Auto-Creation Error: " . $e2->getMessage());
                    throw new Exception("Database Connection Error: Could not connect to MySQL or create database '{$db}'. Please ensure XAMPP MySQL is running.");
                }
            } else {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Database Connection Error: Could not connect to MySQL server at {$host}:{$port}. Please verify XAMPP MySQL service is running.");
            }
        }
    }

    return $pdo;
}
