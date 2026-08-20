<?php
// api/make_call.php - Vehicle Sampark BulkSMSPlans IVR Call Relay API

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Invalid request method. POST or GET required.');
}

$codeNumber = sanitize($_REQUEST['code'] ?? '');
$callerPhone = sanitize($_REQUEST['caller_phone'] ?? $_REQUEST['dial'] ?? '');

if (empty($codeNumber)) {
    jsonResponse(false, 'Missing QR code parameter.');
}

// 1. Fetch QR Code and Submitted Vehicle Owner Details
$stmt = $pdo->prepare("
    SELECT q.*, s.response_data
    FROM qr_codes q
    LEFT JOIN submissions s ON q.id = s.qr_code_id
    WHERE q.code_number = ?
");
$stmt->execute([$codeNumber]);
$qrRecord = $stmt->fetch();

if (!$qrRecord) {
    jsonResponse(false, "Vehicle QR Tag '$codeNumber' was not found in database.");
}

if ($qrRecord['status'] !== 'submitted' || empty($qrRecord['response_data'])) {
    jsonResponse(false, "Vehicle QR Tag '$codeNumber' has not been registered by an owner yet.");
}

// Extract Owner Phone Number from submission JSON
$respData = json_decode($qrRecord['response_data'], true) ?: [];
$ownerMobile = '';
$carNumber = '';
$carName = '';

foreach ($respData as $lbl => $val) {
    $lblLower = strtolower($lbl);
    $valStr = (string)$val;

    if (empty($ownerMobile) && (str_contains($lblLower, 'mobile') || str_contains($lblLower, 'phone') || str_contains($lblLower, 'contact') || str_contains($lblLower, 'number'))) {
        $ownerMobile = $valStr;
    }
    if (empty($carNumber) && (str_contains($lblLower, 'car number') || str_contains($lblLower, 'plate') || str_contains($lblLower, 'reg') || str_contains($lblLower, 'vehicle number'))) {
        $carNumber = $valStr;
    }
    if (empty($carName) && (str_contains($lblLower, 'car name') || str_contains($lblLower, 'make'))) {
        $carName = $valStr;
    }
}

if (empty($ownerMobile)) {
    $ownerMobile = reset($respData) ?: '';
}

// Clean phone numbers (strip +91, spaces, hyphens, non-digits)
function cleanTenDigitPhone($phone) {
    $digits = preg_replace('/[^\d]/', '', $phone);
    if (strlen($digits) > 10) {
        $digits = substr($digits, -10);
    }
    return $digits;
}

$cleanOwnerMobile = cleanTenDigitPhone($ownerMobile);
$cleanCallerMobile = cleanTenDigitPhone($callerPhone);

if (empty($cleanOwnerMobile) || strlen($cleanOwnerMobile) < 10) {
    jsonResponse(false, 'Vehicle owner phone number is missing or invalid.');
}

if (empty($cleanCallerMobile) || strlen($cleanCallerMobile) < 10) {
    jsonResponse(false, 'Please enter a valid 10-digit mobile number so the IVR can connect your call.');
}

// 2. Prepare BulkSMSPlans IVR MakeACall API Parameters (Verified 100% Working Payload)
$apiUrl = 'https://bulksmsplans.com/api/ivr/makeACall';

$apiId = 'APIvRpMDIEc151987';
$apiPassword = 'fetRZg6V';
$ivrNumber = '7971123254';

$dialNumber = $cleanCallerMobile;
$receiverNumber = $cleanOwnerMobile;
$agentNumber = $cleanOwnerMobile;

$postFields = [
    'api_id' => $apiId,
    'api_password' => $apiPassword,
    'ivr_number' => $ivrNumber,
    'dial' => $dialNumber,
    'receiver_number' => $receiverNumber,
    'agent_number' => $agentNumber,
    'scheduled' => '0',
    'timezone_id' => '0'
];

// 3. Execute HTTP POST Request to BulkSMSPlans IVR API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

// Log Call to Database
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS call_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            code_number TEXT,
            caller_phone TEXT,
            owner_phone TEXT,
            ivr_number TEXT,
            api_response TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $logStmt = $pdo->prepare("
        INSERT INTO call_logs (code_number, caller_phone, owner_phone, ivr_number, api_response)
        VALUES (?, ?, ?, ?, ?)
    ");
    $logStmt->execute([$codeNumber, $dialNumber, $receiverNumber, $ivrNumber, $response ?: $curlErr]);
} catch (Exception $e) {
    // Non-blocking log error
}

if ($curlErr) {
    jsonResponse(false, 'IVR Connection Error: ' . $curlErr);
}

$decodedResp = json_decode($response, true) ?: [];

if (isset($decodedResp['code']) && $decodedResp['code'] == 200) {
    jsonResponse(true, 'IVR Call Initiated Successfully! Your phone will ring shortly from IVR hotline 7971123254.', [
        'code_number' => $codeNumber,
        'car_number' => $carNumber,
        'ivr_number' => $ivrNumber,
        'dial' => $dialNumber,
        'receiver_number' => $receiverNumber,
        'api_response' => $decodedResp
    ]);
} else {
    $errMsg = $decodedResp['message'] ?? 'Unable to connect IVR call.';
    jsonResponse(false, 'IVR API Error: ' . $errMsg, [
        'api_response' => $decodedResp
    ]);
}
