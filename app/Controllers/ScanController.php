<?php
// app/Controllers/ScanController.php - Public QR Code Scanner & Call Relay Controller

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../Models/QRCodeModel.php';
require_once __DIR__ . '/../Models/SubmissionModel.php';
require_once __DIR__ . '/../Models/CallLogModel.php';

class ScanController {
    private $pdo;
    private $qrModel;
    private $subModel;
    private $callLogModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->qrModel = new QRCodeModel($pdo);
        $this->subModel = new SubmissionModel($pdo);
        $this->callLogModel = new CallLogModel($pdo);
    }

    public function show() {
        $codeNumber = sanitize($_GET['code'] ?? '');
        $isScanPage = true;
        $pageTitle = 'Vehicle Sampark - Contact Owner';
        $error = '';
        $success = false;

        if (empty($codeNumber)) {
            $error = 'No QR Code specified. Please scan a valid Vehicle Sampark QR Code.';
            include __DIR__ . '/../Views/scan/portal.php';
            return;
        }

        $qrData = $this->qrModel->findByCode($codeNumber);

        if (!$qrData) {
            $error = "Vehicle QR Code '$codeNumber' was not found or has been removed.";
            include __DIR__ . '/../Views/scan/portal.php';
            return;
        }

        // 1. Handle Free Masked Calling / Web Call Request
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_call_owner']) && $qrData['status'] === 'submitted') {
            $subRecord = $this->subModel->getByQrId($qrData['id']);
            $ownerDetails = $this->subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
            $cleanOwnerMobile = $ownerDetails['clean_owner_mobile'];

            $visitorIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            // Log Call to Database
            $this->callLogModel->logCall($codeNumber, 'FREE-WEB-CALLER', $cleanOwnerMobile, 'FREE-WEBRTC-PROXY', 'Free Masked Call Triggered');

            header("Location: scan.php?code=" . urlencode($codeNumber) . "&call_status=initiated");
            exit;
        }

        // 2. Handle First-Time Vehicle Owner Registration Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $qrData['status'] === 'pending') {
            $fullName = sanitize($_POST['full_name'] ?? '');
            $mobileNumber = sanitize($_POST['mobile_number'] ?? '');
            $emergencyMobileNumber = sanitize($_POST['emergency_mobile_number'] ?? '');
            $whatsappNumber = sanitize($_POST['whatsapp_number'] ?? $mobileNumber);
            $carNumber = strtoupper(trim(sanitize($_POST['car_number'] ?? '')));
            $carName = sanitize($_POST['car_name'] ?? '');
            $carModel = sanitize($_POST['car_model'] ?? '');

            if (empty($fullName) || empty($mobileNumber) || empty($emergencyMobileNumber) || empty($carNumber) || empty($carName) || empty($carModel)) {
                $error = 'Please fill out all required vehicle owner registration fields.';
            } elseif (!preg_match('/^[A-Za-z0-9\s\-]{4,15}$/', $carNumber)) {
                $error = 'Invalid Car Number format. Example of valid format: GJ-03-NL-0104 or MH-01-AB-1234.';
            } else {
                $responses = [
                    'Full Name' => $fullName,
                    'Mobile Number' => $mobileNumber,
                    'WhatsApp Number' => $whatsappNumber,
                    'Emergency Mobile Number' => $emergencyMobileNumber,
                    'Car Number' => $carNumber,
                    'Car Name' => $carName,
                    'Car Model' => $carModel
                ];

                try {
                    $this->pdo->beginTransaction();
                    $submitterIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

                    $this->subModel->createSubmission($qrData['id'], $codeNumber, $responses, $submitterIp);
                    $this->qrModel->updateStatus($qrData['id'], 'submitted');

                    $this->pdo->commit();
                    $success = true;
                    $qrData['status'] = 'submitted';

                } catch (Exception $e) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    $error = "Database Error: " . $e->getMessage();
                }
            }
        }

        // Extract Vehicle Details for Display
        $ownerDetails = [
            'owner_name' => '',
            'mobile_number' => '',
            'clean_owner_mobile' => '9723914037',
            'car_name' => '',
            'car_number' => '',
            'car_model' => ''
        ];

        if ($qrData['status'] === 'submitted') {
            $subRecord = $this->subModel->getByQrId($qrData['id']);
            if ($subRecord) {
                $ownerDetails = $this->subModel->extractOwnerDetails($subRecord['response_data']);
                // Register active inbound call mapping for direct dial-in
                $this->callLogModel->createMapping($codeNumber, $ownerDetails['clean_owner_mobile'], $_SERVER['REMOTE_ADDR'] ?? '');
            }
        }

        $callStatus = sanitize($_GET['call_status'] ?? '');
        $displayUserNumber = sanitize($_GET['user_number'] ?? '');

        include __DIR__ . '/../Views/scan/portal.php';
    }
}
