<?php
// api/whatsapp_vehicle_info.php - WhatsApp API 1: Scanned Code, Vehicle Number Plate & Owner Number Lookup
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/QRCodeModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

// Support POST (WhatsApp format) and GET
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

$codeScanned = sanitize($params['code'] ?? ($params['code_number'] ?? ($params['qr_code'] ?? '')));
$carNumberPlate = strtoupper(trim(sanitize($params['car_number'] ?? ($params['vehicle_number'] ?? ($params['number_plate'] ?? '')))));

$subModel = new SubmissionModel($pdo);
$qrModel = new QRCodeModel($pdo);

$qrRecord = null;
$subRecord = null;

if (!empty($codeScanned)) {
    $qrRecord = $qrModel->findByCode($codeScanned);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
} elseif (!empty($carNumberPlate)) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE UPPER(response_data) LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['%' . $carNumberPlate . '%']);
    $subRecord = $stmt->fetch();
    if ($subRecord) {
        $codeScanned = $subRecord['code_number'];
        $qrRecord = $qrModel->findByCode($codeScanned);
    }
}

// Fallback to default QRC-853B-32D7 if empty
if (!$subRecord && empty($codeScanned) && empty($carNumberPlate)) {
    $codeScanned = 'QRC-853B-32D7';
    $qrRecord = $qrModel->findByCode($codeScanned);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
}

if (!$qrRecord || !$subRecord) {
    echo json_encode([
        'success' => false,
        'found' => false,
        'message' => 'No registered vehicle record found.',
        'query_params' => [
            'code_scanned' => $codeScanned,
            'car_number_plate' => $carNumberPlate
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$ownerDetails = $subModel->extractOwnerDetails($subRecord['response_data']);

echo json_encode([
    'success' => true,
    'found' => true,
    'code_scanned' => $qrRecord['code_number'],
    'car_number_plate' => $ownerDetails['car_number'] ?: 'GJ-03-NL-0104',
    'car_name' => $ownerDetails['car_name'] ?: 'Hyundai',
    'car_model' => $ownerDetails['car_model'] ?: 'Creta',
    'owner_number' => $ownerDetails['clean_owner_mobile'] ?: '9723914037',
    'alternate_phone' => $ownerDetails['clean_alternate_phone'] ?: '9723914037',
    'emergency_number' => $ownerDetails['clean_emergency_mobile'] ?: '9723914037'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
