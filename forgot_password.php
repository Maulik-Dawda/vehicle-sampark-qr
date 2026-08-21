<?php
// forgot_password.php - Admin Forgot Password Entry Point

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

$controller = new AuthController($pdo);
$controller->forgotPassword();
