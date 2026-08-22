<?php
echo "=== 1. TESTING API 1: whatsapp_vehicle_info.php ===\n";
$_GET['code'] = 'QRC-853B-32D7';
include __DIR__ . '/../api/whatsapp_vehicle_info.php';

echo "\n\n=== 2. TESTING API 2 (POST check_number = 9723914037 -> OWNER TRUE): whatsapp_menu.php ===\n";
$_GET = [];
$_POST = [
    'check_number' => '9723914037',
    'code' => 'QRC-853B-32D7'
];
include __DIR__ . '/../api/whatsapp_menu.php';

echo "\n\n=== 3. TESTING API 2 (POST check_number = 9898989898 -> OWNER FALSE): whatsapp_menu.php ===\n";
$_GET = [];
$_POST = [
    'check_number' => '9898989898',
    'code' => 'QRC-853B-32D7'
];
include __DIR__ . '/../api/whatsapp_menu.php';
