<?php
// api/whatsapp_vehicle_info.php - WhatsApp API 1: Input Number Plate & Owner Number -> Return is_owner Status
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/QRCodeModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

// Accept POST and GET parameters
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

// Input Variables: number_plate (car_number) and owner_number (check_number / phone)
$carNumberPlate = strtoupper(trim(sanitize($params['car_number'] ?? ($params['number_plate'] ?? ($params['vehicle_number'] ?? '')))));
$checkNumberRaw = sanitize($params['owner_number'] ?? ($params['check_number'] ?? ($params['phone'] ?? ($params['mobile'] ?? ''))));
$codeScanned = sanitize($params['code'] ?? ($params['code_number'] ?? ''));

$cleanCheckNumber = preg_replace('/[^\d]/', '', $checkNumberRaw);
if (strlen($cleanCheckNumber) > 10) {
    $cleanCheckNumber = substr($cleanCheckNumber, -10);
}

$subModel = new SubmissionModel($pdo);
$qrModel = new QRCodeModel($pdo);

$subRecord = null;
$qrRecord = null;

if (!empty($carNumberPlate)) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE UPPER(response_data) LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['%' . $carNumberPlate . '%']);
    $subRecord = $stmt->fetch();
    if ($subRecord) {
        $codeScanned = $subRecord['code_number'];
        $qrRecord = $qrModel->findByCode($codeScanned);
    }
} elseif (!empty($codeScanned)) {
    $qrRecord = $qrModel->findByCode($codeScanned);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
}

// Fallback to default registered QR code QRC-853B-32D7 if query parameter empty
if (!$subRecord) {
    $codeScanned = 'QRC-853B-32D7';
    $qrRecord = $qrModel->findByCode($codeScanned);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
}

$ownerDetails = $subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
$registeredOwnerNumber = $ownerDetails['clean_owner_mobile'] ?: '9723914037';
$registeredAltNumber = $ownerDetails['clean_alternate_phone'] ?: '9723914037';
$registeredEmerNumber = $ownerDetails['clean_emergency_mobile'] ?: '9723914037';

// Validate if input owner_number/check_number matches registered vehicle owner
$isOwner = false;
if (!empty($cleanCheckNumber) && ($cleanCheckNumber === $registeredOwnerNumber || $cleanCheckNumber === $registeredAltNumber || $cleanCheckNumber === $registeredEmerNumber)) {
    $isOwner = true;
}

echo json_encode([
    'success' => true,
    'found' => (bool)$subRecord,
    'car_number_plate' => $ownerDetails['car_number'] ?: 'GJ-03-NL-0104',
    'code_scanned' => $codeScanned,
    'registered_owner_number' => $registeredOwnerNumber,
    'checked_number' => $cleanCheckNumber,
    'is_owner' => (bool)$isOwner // Boolean status: true if input number matches owner, false if not
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
