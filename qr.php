<?php
// qr.php - QR Code PNG Streamer Entry Point

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/app/Controllers/QrController.php';

$controller = new QrController($pdo);
$controller->streamPng();
