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

// Ensure Admins Table & Default Admin Seed Exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            two_factor_secret VARCHAR(100) DEFAULT NULL,
            two_factor_enabled TINYINT(1) DEFAULT 0,
            last_login DATETIME DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $chkAdmin = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $chkAdmin->execute(['admin']);
    $existingAdmin = $chkAdmin->fetch();

    if (!$existingAdmin) {
        $defaultPassHash = password_hash('admin123', PASSWORD_BCRYPT);
        $seedStmt = $pdo->prepare("
            INSERT INTO admins (username, email, password_hash, full_name, two_factor_secret, two_factor_enabled)
            VALUES ('admin', 'admin@vehiclesampark.com', ?, 'System Administrator', 'VSPK2FASECRET123', 0)
        ");
        $seedStmt->execute([$defaultPassHash]);
    } else {
        if (!password_verify('admin123', $existingAdmin['password_hash'])) {
            $upHash = password_hash('admin123', PASSWORD_BCRYPT);
            $upStmt = $pdo->prepare("UPDATE admins SET password_hash = ?, two_factor_enabled = 0 WHERE username = ?");
            $upStmt->execute([$upHash, 'admin']);
        }
    }
} catch (Exception $ex) {
    // Silent seed handler
}
