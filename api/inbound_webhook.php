<?php
// api/inbound_webhook.php - BulkSMSPlans Inbound Masked Calling IVR Webhook Endpoint

header('Content-Type: application/json; charset=utf-8');

if (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
} elseif (file_exists(__DIR__ . '/../config/database.sample.php')) {
    require_once __DIR__ . '/../config/database.sample.php';
}
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/CallLogModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

$callerNumber = sanitize($_REQUEST['caller_number'] ?? $_REQUEST['dial'] ?? $_REQUEST['from'] ?? $_REQUEST['caller_phone'] ?? '');
$ivrNumber    = sanitize($_REQUEST['ivr_number'] ?? $_REQUEST['to'] ?? '7971123254');
$visitorIp    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$callLogModel = new CallLogModel($pdo);
$mapping = $callLogModel->findActiveMapping($visitorIp);

if ($mapping && !empty($mapping['owner_phone'])) {
    $agentNumber = preg_replace('/[^\d]/', '', $mapping['owner_phone']);
    if (strlen($agentNumber) > 10) $agentNumber = substr($agentNumber, -10);
    $codeNumber = $mapping['code_number'];
} else {
    // Default fallback vehicle owner if no active session mapping is found
    $agentNumber = '9723914037';
    $codeNumber = 'DEFAULT-TAG';
}

$cleanCallerNumber = preg_replace('/[^\d]/', '', $callerNumber);
if (strlen($cleanCallerNumber) > 10) $cleanCallerNumber = substr($cleanCallerNumber, -10);
if (empty($cleanCallerNumber)) $cleanCallerNumber = 'VISITOR-CALLER';

// Log the Inbound Call to Database
$callLogModel->logCall($codeNumber, $cleanCallerNumber, $agentNumber, $ivrNumber, 'Inbound Direct Dial-In Webhook Forwarded');

echo json_encode([
    'status'       => 'success',
    'action'       => 'forward',
    'agent_number' => $agentNumber,
    'dial'         => $cleanCallerNumber,
    'ivr_number'   => $ivrNumber,
    'code_number'  => $codeNumber,
    'message'      => 'Inbound masked call forwarded to vehicle owner.'
], JSON_UNESCAPED_UNICODE);
exit;
