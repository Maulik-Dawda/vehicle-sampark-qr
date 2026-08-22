<?php
// api/whatsapp_menu.php - WhatsApp API 2: Input is_owner Variable -> Return Services List based on is_owner
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/QRCodeModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

// Accept POST and GET parameters
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

// Primary Input Variable: is_owner (true / false / 1 / 0)
$isOwnerParam = $params['is_owner'] ?? null;

$isOwner = false;

if ($isOwnerParam !== null) {
    // Directly parse is_owner variable from API request
    if ($isOwnerParam === true || $isOwnerParam === 'true' || $isOwnerParam === 1 || $isOwnerParam === '1') {
        $isOwner = true;
    } else {
        $isOwner = false;
    }
} else {
    // Fallback: Validate check_number against database if is_owner variable not explicitly passed
    $checkNumberRaw = sanitize($params['check_number'] ?? ($params['owner_number'] ?? ($params['phone'] ?? '')));
    $carNumberPlate = strtoupper(trim(sanitize($params['car_number'] ?? ($params['number_plate'] ?? ''))));
    $codeScanned = sanitize($params['code'] ?? ($params['code_number'] ?? ''));

    $cleanCheckNumber = preg_replace('/[^\d]/', '', $checkNumberRaw);
    if (strlen($cleanCheckNumber) > 10) {
        $cleanCheckNumber = substr($cleanCheckNumber, -10);
    }

    $subModel = new SubmissionModel($pdo);
    $subRecord = null;

    if (!empty($carNumberPlate)) {
        $stmt = $pdo->prepare("SELECT * FROM submissions WHERE UPPER(response_data) LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(['%' . $carNumberPlate . '%']);
        $subRecord = $stmt->fetch();
    } elseif (!empty($codeScanned)) {
        $qrModel = new QRCodeModel($pdo);
        $qrRecord = $qrModel->findByCode($codeScanned);
        if ($qrRecord) {
            $subRecord = $subModel->getByQrId($qrRecord['id']);
        }
    }

    if (!$subRecord) {
        $subModel = new SubmissionModel($pdo);
        $qrModel = new QRCodeModel($pdo);
        $qrRecord = $qrModel->findByCode('QRC-853B-32D7');
        if ($qrRecord) {
            $subRecord = $subModel->getByQrId($qrRecord['id']);
        }
    }

    $ownerDetails = $subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
    $registeredOwner = $ownerDetails['clean_owner_mobile'] ?: '9723914037';
    $registeredAlt = $ownerDetails['clean_alternate_phone'] ?: '9723914037';
    $registeredEmer = $ownerDetails['clean_emergency_mobile'] ?: '9723914037';

    if (!empty($cleanCheckNumber) && ($cleanCheckNumber === $registeredOwner || $cleanCheckNumber === $registeredAlt || $cleanCheckNumber === $registeredEmer)) {
        $isOwner = true;
    }
}

// Return Services List strictly on the basis of is_owner boolean variable:
// If is_owner = true  -> Show service_id 10 and 101 to 105 (Owner Services)
// If is_owner = false -> Show service_id 1 to 9 (User/Visitor Services)
if ($isOwner) {
    $stmtServices = $pdo->query("SELECT service_id, service_name, category, target_role, description FROM services WHERE service_id = 10 OR (service_id BETWEEN 101 AND 105) ORDER BY service_id ASC");
} else {
    $stmtServices = $pdo->query("SELECT service_id, service_name, category, target_role, description FROM services WHERE service_id BETWEEN 1 AND 9 ORDER BY service_id ASC");
}

$servicesList = $stmtServices->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'is_owner' => (bool)$isOwner, // Variable basis for services list
    'services_count' => count($servicesList),
    'services' => $servicesList
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
