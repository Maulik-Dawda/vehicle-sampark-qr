<?php
// api/make_call.php - Vehicle Sampark BulkSMSPlans IVR Call Relay API (Matching bulksms_ivr_app setup)

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. POST or GET required.',
        'code' => 405
    ]);
    exit;
}

$codeNumber = sanitize($_REQUEST['code'] ?? '');
$receiver_number = sanitize($_REQUEST['receiver_number'] ?? $_REQUEST['caller_phone'] ?? $_REQUEST['visitor_phone'] ?? '');

if (empty($codeNumber)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing QR code parameter.',
        'code' => 400
    ]);
    exit;
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
    echo json_encode([
        'success' => false,
        'message' => "Vehicle QR Tag '$codeNumber' was not found in database.",
        'code' => 404
    ]);
    exit;
}

if ($qrRecord['status'] !== 'submitted' || empty($qrRecord['response_data'])) {
    echo json_encode([
        'success' => false,
        'message' => "Vehicle QR Tag '$codeNumber' has not been registered by an owner yet.",
        'code' => 400
    ]);
    exit;
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
    $ownerMobile = reset($respData) ?: '9723914037';
}

// Phone Number Cleaning Function (matches bulksms_ivr_app)
$cleanPhone = function($phone) {
    $num = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($num) == 12 && substr($num, 0, 2) == '91') {
        $num = substr($num, 2);
    }
    if (strlen($num) > 10) {
        $num = substr($num, -10);
    }
    return $num;
};

$agent_number = $cleanPhone($ownerMobile);
$receiver_number = $cleanPhone($receiver_number);

if (empty($agent_number) || strlen($agent_number) < 10) {
    $agent_number = '9723914037'; // Fallback configured owner number
}

if (empty($receiver_number) || strlen($receiver_number) < 10) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid 10-digit mobile number so the IVR can connect your call.',
        'code' => 400
    ]);
    exit;
}

// 2. Prepare BulkSMSPlans IVR API Configuration (bulksms_ivr_app standard)
$apiUrl = 'https://bulksmsplans.com/api/ivr/makeACall';
$apiId = 'APIvRpMDIEc151987';
$apiPassword = 'fetRZg6V';
$ivrNumber = '7971123254';

$params = array_filter([
    'api_id'          => $apiId,
    'api_password'    => $apiPassword,
    'ivr_number'      => $ivrNumber,
    'dial'            => '1',
    'receiver_number' => $receiver_number,
    'agent_number'    => $agent_number,
    'scheduled'       => '0',
    'ai_connect'      => '0'
], function($val) {
    return $val !== '' && $val !== null;
});

$targetUrl = $apiUrl . '?' . http_build_query($params);

// 3. Execute HTTP GET Request to BulkSMSPlans IVR API
$startTime = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'BulkSMSPlans-IVR-Client/1.0');

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$executionTimeMs = round((microtime(true) - $startTime) * 1000, 2);

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
    $logStmt->execute([$codeNumber, $receiver_number, $agent_number, $ivrNumber, $response ?: $curl_error]);
} catch (Exception $e) {
    // Non-blocking log error
}

if ($curl_error) {
    echo json_encode([
        'success' => false,
        'message' => 'cURL Error: ' . $curl_error,
        'http_code' => $http_code,
        'execution_time_ms' => $executionTimeMs
    ]);
    exit;
}

$decodedResp = json_decode($response, true);
$isJson = (json_last_error() === JSON_ERROR_NONE);

$isSuccess = ($http_code >= 200 && $http_code < 300);
if ($isJson && isset($decodedResp['code']) && $decodedResp['code'] != 200) {
    $isSuccess = false;
}

if ($isSuccess) {
    echo json_encode([
        'success' => true,
        'message' => 'Call initiated successfully! BulkSMSPlans IVR is bridging the lines.',
        'http_code' => $http_code,
        'data' => [
            'code_number' => $codeNumber,
            'car_number' => $carNumber,
            'ivr_number' => $ivrNumber,
            'agent_number' => $agent_number,
            'receiver_number' => $receiver_number
        ],
        'raw_response' => $isJson ? $decodedResp : $response,
        'execution_time_ms' => $executionTimeMs
    ]);
} else {
    $errMsg = $isJson ? ($decodedResp['message'] ?? 'API response returned an error.') : 'Unable to connect IVR call.';
    echo json_encode([
        'success' => false,
        'message' => $errMsg,
        'http_code' => $http_code,
        'raw_response' => $isJson ? $decodedResp : $response,
        'execution_time_ms' => $executionTimeMs
    ]);
}
