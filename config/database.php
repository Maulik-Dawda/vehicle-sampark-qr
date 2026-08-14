<?php
// config/database.php - Vehicle Sampark Database Manager (MySQL & SQLite)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load Untracked Server Database Credentials if Present
if (file_exists(__DIR__ . '/db_credentials.php')) {
    require_once __DIR__ . '/db_credentials.php';
}

// 2. Default Fallback Credentials if db_credentials.php is Missing
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'vehicle_sampark');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

$pdo = null;
$dbEngine = 'mysql';

try {
    // 1. Try MySQL Connection (phpMyAdmin)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // 2. Fallback to SQLite PDO for Zero-Config Local Development
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

// Auto-Create Database Tables & Admin Credentials
try {
    if ($dbEngine === 'mysql') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admins` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `email` VARCHAR(150) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `full_name` VARCHAR(150) DEFAULT 'Administrator',
                `two_factor_secret` VARCHAR(255) DEFAULT NULL,
                `two_factor_enabled` TINYINT(1) DEFAULT 0,
                `reset_token` VARCHAR(255) DEFAULT NULL,
                `reset_token_expires` DATETIME DEFAULT NULL,
                `last_login` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `batches` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `batch_name` VARCHAR(255) NOT NULL,
                `form_title` VARCHAR(255) NOT NULL,
                `form_description` TEXT DEFAULT NULL,
                `form_schema` LONGTEXT NOT NULL,
                `total_qrs` INT DEFAULT 0,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `qr_codes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `batch_id` INT NOT NULL,
                `code_number` VARCHAR(50) NOT NULL UNIQUE,
                `status` ENUM('pending', 'submitted') DEFAULT 'pending',
                `submitted_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `submissions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `qr_code_id` INT NOT NULL,
                `code_number` VARCHAR(50) NOT NULL,
                `response_data` LONGTEXT NOT NULL,
                `file_paths` TEXT DEFAULT NULL,
                `submitter_ip` VARCHAR(45) DEFAULT NULL,
                `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS `bot_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `qr_code_id` INT DEFAULT NULL,
                `code_number` VARCHAR(50) DEFAULT NULL,
                `car_number` VARCHAR(50) DEFAULT NULL,
                `issue_selected` VARCHAR(150) NOT NULL,
                `bystander_phone` VARCHAR(50) DEFAULT NULL,
                `owner_notified` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        try {
            $pdo->exec("ALTER TABLE admins ADD COLUMN two_factor_secret VARCHAR(255) DEFAULT NULL");
        } catch (Exception $ex) {}
        try {
            $pdo->exec("ALTER TABLE admins ADD COLUMN two_factor_enabled TINYINT(1) DEFAULT 0");
        } catch (Exception $ex) {}
        try {
            $pdo->exec("ALTER TABLE admins ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        } catch (Exception $ex) {}
        try {
            $pdo->exec("ALTER TABLE admins ADD COLUMN reset_token_expires DATETIME DEFAULT NULL");
        } catch (Exception $ex) {}

    } else {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                full_name TEXT DEFAULT 'Administrator',
                two_factor_secret TEXT,
                two_factor_enabled INTEGER DEFAULT 0,
                reset_token TEXT,
                reset_token_expires DATETIME,
                last_login DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS batches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                batch_name TEXT NOT NULL,
                form_title TEXT NOT NULL,
                form_description TEXT,
                form_schema TEXT NOT NULL,
                total_qrs INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS qr_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                batch_id INTEGER NOT NULL,
                code_number TEXT UNIQUE NOT NULL,
                status TEXT DEFAULT 'pending',
                submitted_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS submissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                qr_code_id INTEGER NOT NULL,
                code_number TEXT NOT NULL,
                response_data TEXT NOT NULL,
                file_paths TEXT,
                submitter_ip TEXT,
                submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (qr_code_id) REFERENCES qr_codes(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS bot_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                qr_code_id INTEGER,
                code_number TEXT,
                car_number TEXT,
                issue_selected TEXT NOT NULL,
                bystander_phone TEXT,
                owner_notified INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
    }

    // Seed default Admin Account if missing (Username: admin | Password: admin123)
    $chkAdmin = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    if ($chkAdmin == 0) {
        $defaultPassHash = password_hash('admin123', PASSWORD_BCRYPT);
        $seedStmt = $pdo->prepare("
            INSERT INTO admins (username, email, password_hash, full_name, two_factor_secret, two_factor_enabled)
            VALUES ('admin', 'admin@vehiclesampark.com', ?, 'System Administrator', 'VSPK2FASECRET123', 0)
        ");
        $seedStmt->execute([$defaultPassHash]);
    }

} catch (Exception $e) {
    // Migration exception handler
}

/**
 * Checks if Admin is logged in via Session and 2FA verified
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && !empty($_SESSION['admin_2fa_passed']);
}

/**
 * Enforces Admin Login requirement for dashboard pages
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Returns logged in Admin info
 */
function getLoggedInAdmin() {
    return [
        'id' => $_SESSION['admin_id'] ?? 1,
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'full_name' => $_SESSION['admin_fullname'] ?? 'Administrator',
        'email' => $_SESSION['admin_email'] ?? 'admin@vehiclesampark.com'
    ];
}
