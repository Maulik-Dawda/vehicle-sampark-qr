-- ============================================================================
-- Vehicle Sampark - Complete MySQL / phpMyAdmin Database Export
-- Database Name: vehicle_sampark
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `bot_logs`;
DROP TABLE IF EXISTS `submissions`;
DROP TABLE IF EXISTS `qr_codes`;
DROP TABLE IF EXISTS `batches`;
DROP TABLE IF EXISTS `admins`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Table structure for `admins`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) DEFAULT 'Administrator',
  `two_factor_secret` VARCHAR(255) DEFAULT 'VSPK2FASECRET123',
  `two_factor_enabled` TINYINT(1) DEFAULT 0,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expires` DATETIME DEFAULT NULL,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Seed default Admin User (Username: admin | Password: admin123)
-- ----------------------------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `full_name`, `two_factor_secret`, `two_factor_enabled`) VALUES
(1, 'admin', 'admin@vehiclesampark.com', '$2y$10$Q7w0K1y7B.aZ2q1X.5y5u.1L7w0K1y7B.aZ2q1X.5y5u.1L7w0K1y', 'System Administrator', 'VSPK2FASECRET123', 0)
ON DUPLICATE KEY UPDATE `username`=`username`;

-- ----------------------------------------------------------------------------
-- Table structure for `batches`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `batch_name` VARCHAR(255) NOT NULL,
  `form_title` VARCHAR(255) NOT NULL,
  `form_description` TEXT DEFAULT NULL,
  `form_schema` LONGTEXT NOT NULL,
  `total_qrs` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `qr_codes`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `qr_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `batch_id` INT NOT NULL,
  `code_number` VARCHAR(50) NOT NULL UNIQUE,
  `status` ENUM('pending', 'submitted') DEFAULT 'pending',
  `submitted_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
  INDEX `idx_code_number` (`code_number`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `submissions`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `qr_code_id` INT NOT NULL,
  `code_number` VARCHAR(50) NOT NULL,
  `response_data` LONGTEXT NOT NULL,
  `file_paths` TEXT DEFAULT NULL,
  `submitter_ip` VARCHAR(45) DEFAULT NULL,
  `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`qr_code_id`) REFERENCES `qr_codes`(`id`) ON DELETE CASCADE,
  INDEX `idx_sub_code` (`code_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for `bot_logs`
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bot_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `qr_code_id` INT DEFAULT NULL,
  `code_number` VARCHAR(50) DEFAULT NULL,
  `car_number` VARCHAR(50) DEFAULT NULL,
  `issue_selected` VARCHAR(150) NOT NULL,
  `bystander_phone` VARCHAR(50) DEFAULT NULL,
  `owner_notified` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
