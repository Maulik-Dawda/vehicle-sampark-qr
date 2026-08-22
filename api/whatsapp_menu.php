<?php
// api/whatsapp_menu.php - WhatsApp API 2: Validate check_number & Return Role-Based Services with IDs
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/QRCodeModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

// Accept POST method from WhatsApp (or GET fallback)
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

// WhatsApp POST parameter: check_number (the phone number calling WhatsApp bot)
$checkNumberRaw = sanitize($params['check_number'] ?? ($params['sender_phone'] ?? ($params['from'] ?? '')));
$codeScanned = sanitize($params['code'] ?? ($params['code_number'] ?? ''));
$carNumberPlate = strtoupper(trim(sanitize($params['car_number'] ?? ($params['vehicle_number'] ?? ''))));

$cleanCheckNumber = preg_replace('/[^\d]/', '', $checkNumberRaw);
if (strlen($cleanCheckNumber) > 10) {
    $cleanCheckNumber = substr($cleanCheckNumber, -10);
}

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

// Fallback to default registered QR code QRC-853B-32D7 if query parameter not provided
if (!$subRecord) {
    $codeScanned = 'QRC-853B-32D7';
    $qrRecord = $qrModel->findByCode($codeScanned);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
}

$ownerDetails = $subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
$ownerNumber = $ownerDetails['clean_owner_mobile'] ?: '9723914037';
$alternatePhone = $ownerDetails['clean_alternate_phone'] ?: '9723914037';
$emergencyPhone = $ownerDetails['clean_emergency_mobile'] ?: '9723914037';

// Validate check_number against registered vehicle owner numbers
$isOwner = false;
if (!empty($cleanCheckNumber) && ($cleanCheckNumber === $ownerNumber || $cleanCheckNumber === $alternatePhone || $cleanCheckNumber === $emergencyPhone)) {
    $isOwner = true;
}

// Query Services Table based on is_owner boolean expression
$targetRoles = $isOwner ? ['owner', 'both'] : ['user', 'both'];
$inClause = implode(',', array_map(function($r) use ($pdo) { return $pdo->quote($r); }, $targetRoles));

$stmtServices = $pdo->query("SELECT service_id, service_name, category, target_role, description FROM services WHERE target_role IN ($inClause) ORDER BY service_id ASC");
$servicesList = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'validated' => $isOwner,
    'is_owner' => (bool)$isOwner, // Boolean expression for is_owner
    'check_number' => $cleanCheckNumber,
    'owner_number' => $ownerNumber,
    'car_number_plate' => $ownerDetails['car_number'] ?: 'GJ-03-NL-0104',
    'code_scanned' => $codeScanned,
    'services_count' => count($servicesList),
    'services' => $servicesList
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
