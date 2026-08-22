<?php
echo "=== TESTING API 1: whatsapp_vehicle_info.php ===\n";
$_GET['code'] = 'QRC-853B-32D7';
include __DIR__ . '/../api/whatsapp_vehicle_info.php';

echo "\n\n=== TESTING API 2 (Public Visitor): whatsapp_menu.php ===\n";
$_GET = [];
$_GET['contact_phone'] = '9898989898';
$_GET['code'] = 'QRC-853B-32D7';
include __DIR__ . '/../api/whatsapp_menu.php';

echo "\n\n=== TESTING API 2 (Vehicle Owner): whatsapp_menu.php ===\n";
$_GET = [];
$_GET['contact_phone'] = '9723914037';
$_GET['code'] = 'QRC-853B-32D7';
include __DIR__ . '/../api/whatsapp_menu.php';
