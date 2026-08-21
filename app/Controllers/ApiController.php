<?php
// app/Controllers/ApiController.php - Admin API & IVR Call Endpoint Controller

require_once __DIR__ . '/../Models/QRCodeModel.php';
require_once __DIR__ . '/../Models/BatchModel.php';
require_once __DIR__ . '/../Models/SubmissionModel.php';
require_once __DIR__ . '/../Models/CallLogModel.php';
require_once __DIR__ . '/../../includes/tag_template.php';

class ApiController {
    private $pdo;
    private $qrModel;
    private $batchModel;
    private $subModel;
    private $callLogModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->qrModel = new QRCodeModel($pdo);
        $this->batchModel = new BatchModel($pdo);
        $this->subModel = new SubmissionModel($pdo);
        $this->callLogModel = new CallLogModel($pdo);
    }

    public function handle() {
        $action = sanitize($_GET['action'] ?? '');

        switch ($action) {
            case 'create_batch':
                if (!isAdminLoggedIn()) {
                    jsonResponse(false, 'Unauthorized access. Please login as Admin.');
                }
                $this->handleCreateBatch();
                break;

            case 'get_qrcode':
                $this->handleGetQRCode();
                break;

            case 'get_submission':
                $this->handleGetSubmission();
                break;

            default:
                jsonResponse(false, 'Invalid action');
                break;
        }
    }

    private function handleCreateBatch() {
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
            $this->pdo->beginTransaction();

            $batchId = $this->batchModel->createBatch($batchName, $formTitle, $formDescription, $formSchemaJson, $quantity);

            $qrStmt = $this->pdo->prepare("
                INSERT INTO qr_codes (batch_id, code_number, status)
                VALUES (:batch_id, :code_number, 'pending')
            ");

            for ($i = 0; $i < $quantity; $i++) {
                $codeNumber = generateUniqueCodeNumber($this->pdo);
                $qrStmt->execute([
                    ':batch_id' => $batchId,
                    ':code_number' => $codeNumber
                ]);
            }

            $this->pdo->commit();
            jsonResponse(true, "Batch '$batchName' created successfully with $quantity QR codes!");

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            jsonResponse(false, 'Failed to create batch: ' . $e->getMessage());
        }
    }

    private function handleGetQRCode() {
        $codeNumber = sanitize($_GET['code'] ?? '');
        if (empty($codeNumber)) jsonResponse(false, 'Missing code parameter');

        $qr = $this->qrModel->findByCode($codeNumber);
        if (!$qr) jsonResponse(false, 'QR Code not found');

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

    private function handleGetSubmission() {
        $codeNumber = sanitize($_GET['code'] ?? '');
        if (empty($codeNumber)) jsonResponse(false, 'Missing code parameter');

        $qr = $this->qrModel->findByCode($codeNumber);
        if (!$qr) jsonResponse(false, 'QR Code not found');

        $scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber);

        if ($qr['status'] === 'pending') {
            jsonResponse(true, 'No submission yet', [
                'status' => 'pending',
                'code_number' => $codeNumber,
                'batch_name' => $qr['batch_name'],
                'form_title' => $qr['form_title'],
                'scan_url' => $scanUrl
            ]);
        }

        $submission = $this->subModel->getByQrId($qr['id']);
        if (!$submission) jsonResponse(false, 'Submission data missing');

        $responses = json_decode($submission['response_data'], true) ?: [];
        $callLogsCount = $this->callLogModel->getCallCountByCode($codeNumber);
        $recentCallLogs = $this->callLogModel->getLogsByCode($codeNumber, 5);

        jsonResponse(true, 'Submission retrieved', [
            'status' => 'submitted',
            'code_number' => $codeNumber,
            'batch_name' => $qr['batch_name'],
            'form_title' => $qr['form_title'],
            'scan_url' => $scanUrl,
            'submitted_at' => date('M j, Y g:i A', strtotime($submission['submitted_at'])),
            'submitter_ip' => $submission['submitter_ip'] ?? 'N/A',
            'responses' => $responses,
            'total_calls' => $callLogsCount,
            'recent_calls' => $recentCallLogs,
            'download_pdf_url' => 'admin-qr-single-pdf?code=' . urlencode($codeNumber)
        ]);
    }

    public function makeCall() {
        header('Content-Type: application/json; charset=utf-8');

        $codeNumber = sanitize($_REQUEST['code'] ?? '');
        $userDialerNumber = sanitize($_REQUEST['dial'] ?? $_REQUEST['user_number'] ?? $_REQUEST['caller_phone'] ?? '');

        if (empty($codeNumber)) {
            echo json_encode(['success' => false, 'message' => 'Missing QR code parameter.', 'code' => 400]);
            exit;
        }

        $qrRecord = $this->qrModel->findByCode($codeNumber);
        if (!$qrRecord || $qrRecord['status'] !== 'submitted') {
            echo json_encode(['success' => false, 'message' => 'Vehicle QR Tag has not been registered by an owner yet.', 'code' => 400]);
            exit;
        }

        $subRecord = $this->subModel->getByQrId($qrRecord['id']);
        $ownerDetails = $this->subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
        $cleanOwnerMobile = $ownerDetails['clean_owner_mobile'];

        $cleanPhone = function($phone) {
            $num = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($num) == 12 && substr($num, 0, 2) == '91') $num = substr($num, 2);
            if (strlen($num) > 10) $num = substr($num, -10);
            return $num;
        };

        $cleanUserDialNumber = $cleanPhone($userDialerNumber);
        if (empty($cleanUserDialNumber) || strlen($cleanUserDialNumber) < 10) {
            $cleanUserDialNumber = $cleanOwnerMobile;
        }

        $apiUrl = 'https://bulksmsplans.com/api/ivr/makeACall';
        $params = [
            'api_id'          => 'APIvRpMDIEc151987',
            'api_password'    => 'fetRZg6V',
            'ivr_number'      => '7971123254',
            'dial'            => $cleanUserDialNumber,
            'receiver_number' => $cleanUserDialNumber,
            'agent_number'    => $cleanOwnerMobile,
            'scheduled'       => '0',
            'timezone_id'     => '0',
            'ai_connect'      => '0'
        ];

        $targetUrl = $apiUrl . '?' . http_build_query(array_filter($params));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->callLogModel->logCall($codeNumber, $cleanUserDialNumber, $cleanOwnerMobile, '7971123254', $response);

        echo json_encode([
            'success' => true,
            'message' => 'Call request processed via BulkSMSPlans API.',
            'http_code' => $http_code,
            'data' => [
                'code_number' => $codeNumber,
                'dial' => $cleanUserDialNumber,
                'receiver_number' => $cleanUserDialNumber,
                'agent_number' => $cleanOwnerMobile
            ]
        ]);
        exit;
    }
}
