<?php
// api/inbound_webhook.php - Universal Inbound Masked Calling Webhook Endpoint

error_reporting(0);
ini_set('display_errors', 0);

if (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
} elseif (file_exists(__DIR__ . '/../config/database.sample.php')) {
    require_once __DIR__ . '/../config/database.sample.php';
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/CallLogModel.php';

// Accept JSON payload if posted as raw body
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

$callerNumber = sanitize($params['caller_number'] ?? $params['dial'] ?? $params['from'] ?? $params['caller'] ?? $params['caller_phone'] ?? $params['mobile'] ?? $params['Phone'] ?? $params['CustomerNumber'] ?? $params['CallerNumber'] ?? '');
$ivrNumber    = sanitize($params['ivr_number'] ?? $params['to'] ?? $params['VirtualNumber'] ?? $params['DIDNumber'] ?? '07971123254');
$visitorIp    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$callLogModel = new CallLogModel($pdo);
$mapping = $callLogModel->findActiveMapping($visitorIp);

if ($mapping && !empty($mapping['owner_phone'])) {
    $agentNumber = preg_replace('/[^\d]/', '', $mapping['owner_phone']);
    if (strlen($agentNumber) > 10) $agentNumber = substr($agentNumber, -10);
    $codeNumber = $mapping['code_number'];
} else {
    // Default fallback vehicle owner number
    $agentNumber = '9723914037';
    $codeNumber = 'DEFAULT-TAG';
}

$cleanCallerNumber = preg_replace('/[^\d]/', '', $callerNumber);
if (strlen($cleanCallerNumber) > 10) $cleanCallerNumber = substr($cleanCallerNumber, -10);
if (empty($cleanCallerNumber)) $cleanCallerNumber = 'VISITOR-CALLER';

// Log Inbound Webhook Event to Database
$callLogModel->logCall($codeNumber, $cleanCallerNumber, $agentNumber, $ivrNumber, 'Inbound Webhook Connected -> ' . $agentNumber);

$format = strtolower($params['format'] ?? '');

if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Response>';
    echo '<Status>success</Status>';
    echo '<Action>forward</Action>';
    echo '<AgentNumber>' . htmlspecialchars($agentNumber) . '</AgentNumber>';
    echo '<IVRNumber>' . htmlspecialchars($ivrNumber) . '</IVRNumber>';
    echo '</Response>';
} elseif ($format === 'text' || $format === 'plain') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "STATUS=SUCCESS\nACTION=FORWARD\nAGENT_NUMBER=" . $agentNumber . "\nIVR_NUMBER=" . $ivrNumber;
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'       => 'success',
        'action'       => 'forward',
        'agent_number' => $agentNumber,
        'dial'         => $cleanCallerNumber,
        'ivr_number'   => $ivrNumber,
        'code_number'  => $codeNumber,
        'message'      => 'Inbound call forwarded to vehicle owner.'
    ], JSON_UNESCAPED_UNICODE);
}
exit;
