<?php
// api.php - Vehicle Sampark API Router & Tag Card Data Provider (Admin Protected)

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/tag_template.php';

$action = sanitize($_GET['action'] ?? '');

switch ($action) {
    case 'create_batch':
        if (!isAdminLoggedIn()) {
            jsonResponse(false, 'Unauthorized access. Please login as Admin.');
        }
        handleCreateBatch($pdo);
        break;

    case 'get_qrcode':
        handleGetQRCode($pdo);
        break;

    case 'get_submission':
        handleGetSubmission($pdo);
        break;

    default:
        jsonResponse(false, 'Invalid action');
        break;
}

function handleCreateBatch($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed');
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data) {
        jsonResponse(false, 'Invalid JSON payload');
    }

    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
    $formTitle = sanitize($data['form_title'] ?? 'Vehicle QR Tag Registration');
    $formDescription = sanitize($data['form_description'] ?? 'Scan QR code to register vehicle owner details for direct call and WhatsApp contact.');

    if ($quantity < 1 || $quantity > 500) {
        jsonResponse(false, 'Quantity must be between 1 and 500');
    }

    if (empty($formTitle)) {
        $formTitle = 'Vehicle QR Tag Registration';
    }

    // Fixed 7 Vehicle Owner Fields (Always pre-configured)
    $fixedFields = [
        ['id' => 'full_name', 'type' => 'text', 'label' => 'Full Name', 'required' => true],
        ['id' => 'mobile_number', 'type' => 'mobile', 'label' => 'Mobile Number', 'required' => true],
        ['id' => 'whatsapp_number', 'type' => 'mobile', 'label' => 'WhatsApp Number', 'required' => true],
        ['id' => 'emergency_mobile_number', 'type' => 'mobile', 'label' => 'Emergency Mobile Number', 'required' => true],
        ['id' => 'car_number', 'type' => 'text', 'label' => 'Car Number', 'required' => true],
        ['id' => 'car_name', 'type' => 'text', 'label' => 'Car Name', 'required' => true],
        ['id' => 'car_model', 'type' => 'text', 'label' => 'Car Model', 'required' => true]
    ];

    $batchName = 'Batch ' . date('Y-m-d H:i') . ' (' . $quantity . ' Tags)';
    $formSchemaJson = json_encode($fixedFields, JSON_UNESCAPED_UNICODE);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO batches (batch_name, form_title, form_description, form_schema, total_qrs)
            VALUES (:batch_name, :form_title, :form_description, :form_schema, :total_qrs)
        ");
        $stmt->execute([
            ':batch_name' => $batchName,
            ':form_title' => $formTitle,
            ':form_description' => $formDescription,
            ':form_schema' => $formSchemaJson,
            ':total_qrs' => $quantity
        ]);

        $batchId = $pdo->lastInsertId();

        $qrStmt = $pdo->prepare("
            INSERT INTO qr_codes (batch_id, code_number, status)
            VALUES (:batch_id, :code_number, 'pending')
        ");

        for ($i = 0; $i < $quantity; $i++) {
            $codeNumber = generateUniqueCode('QRC');
            $qrStmt->execute([
                ':batch_id' => $batchId,
                ':code_number' => $codeNumber
            ]);
        }

        $pdo->commit();

        jsonResponse(true, "Successfully generated batch with $quantity Vehicle Tags", [
            'batch_id' => $batchId,
            'quantity' => $quantity
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        jsonResponse(false, 'Database error: ' . $e->getMessage());
    }
}

function handleGetQRCode($pdo) {
    $codeNumber = sanitize($_GET['code'] ?? '');

    if (empty($codeNumber)) {
        jsonResponse(false, 'Missing code parameter');
    }

    $stmt = $pdo->prepare("
        SELECT q.*, b.form_title, b.batch_name
        FROM qr_codes q
        JOIN batches b ON q.batch_id = b.id
        WHERE q.code_number = ?
    ");
    $stmt->execute([$codeNumber]);
    $qr = $stmt->fetch();

    if (!$qr) {
        jsonResponse(false, 'QR Code not found');
    }

    $scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber);
    $qrImageBase64 = getQRCodeBase64($codeNumber);
    $tagHtml = renderVehicleSamparkTagHTML($codeNumber, $qr['form_title']);

    jsonResponse(true, 'QR Code details retrieved', [
        'code_number' => $codeNumber,
        'form_title' => $qr['form_title'],
        'status' => $qr['status'],
        'scan_url' => $scanUrl,
        'qr_image' => $qrImageBase64,
        'tag_html' => $tagHtml,
        'download_url' => 'admin-qr-single-pdf?code=' . urlencode($codeNumber)
    ]);
}

function handleGetSubmission($pdo) {
    $codeNumber = sanitize($_GET['code'] ?? '');

    if (empty($codeNumber)) {
        jsonResponse(false, 'Missing code parameter');
    }

    $stmt = $pdo->prepare("
        SELECT q.*, b.form_title, b.batch_name
        FROM qr_codes q
        JOIN batches b ON q.batch_id = b.id
        WHERE q.code_number = ?
    ");
    $stmt->execute([$codeNumber]);
    $qr = $stmt->fetch();

    if (!$qr) {
        jsonResponse(false, 'QR Code not found');
    }

    if ($qr['status'] === 'pending') {
        jsonResponse(true, 'No submission yet', [
            'status' => 'pending',
            'code_number' => $codeNumber,
            'scan_url' => getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber)
        ]);
    }

    $subStmt = $pdo->prepare("SELECT * FROM submissions WHERE qr_code_id = ? ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$qr['id']]);
    $submission = $subStmt->fetch();

    if (!$submission) {
        jsonResponse(false, 'Submission data missing');
    }

    $responses = json_decode($submission['response_data'], true) ?: [];

    jsonResponse(true, 'Submission retrieved', [
        'status' => 'submitted',
        'code_number' => $codeNumber,
        'submitted_at' => date('M j, Y g:i A', strtotime($submission['submitted_at'])),
        'responses' => $responses
    ]);
}
