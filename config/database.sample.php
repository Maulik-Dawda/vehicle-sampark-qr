<?php
// config/database.sample.php - Sample Database Configuration Template
// Copy this file to config/database.php on your server and enter your credentials.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_vehicle_sampark'); // Your Hostinger MySQL DB Name
define('DB_USER', 'u123456789_admin');           // Your Hostinger MySQL DB User
define('DB_PASS', 'YourHostingerDBPassword123!'); // Your Hostinger MySQL DB Password

$pdo = null;
$dbEngine = 'mysql';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    try {
        $dbEngine = 'sqlite';
        $sqliteFile = __DIR__ . '/database.sqlite';
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (PDOException $sqle) {
        die("Database Connection Error: " . $sqle->getMessage());
    }
}
