<?php
// app/Controllers/QrController.php - Handles QR Image Streaming & PDF Tag Generation

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Models/QRCodeModel.php';
require_once __DIR__ . '/../Models/BatchModel.php';
require_once __DIR__ . '/../../includes/tag_template.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrController {
    private $pdo;
    private $qrModel;
    private $batchModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->qrModel = new QRCodeModel($pdo);
        $this->batchModel = new BatchModel($pdo);
    }

    public function streamPng() {
        $codeNumber = sanitize($_GET['code'] ?? '');

        if (empty($codeNumber)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Missing QR code identifier';
            exit;
        }

        $scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber);

        $options = new QROptions();
        $options->outputType = QRCode::OUTPUT_IMAGE_PNG;
        $options->eccLevel = QRCode::ECC_H;
        $options->scale = 8;
        $options->imageBase64 = false;

        try {
            $qrcode = new QRCode($options);
            $rawPng = $qrcode->render($scanUrl);
            $finalPng = embedLogoInQRCode($rawPng);

            header('Content-Type: image/png');
            header('Content-Length: ' . strlen($finalPng));

            if (isset($_GET['download']) && $_GET['download'] == '1') {
                header('Content-Disposition: attachment; filename="' . $codeNumber . '.png"');
            } else {
                header('Content-Disposition: inline; filename="' . $codeNumber . '.png"');
            }

            echo $finalPng;
            exit;

        } catch (\Throwable $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'QR Generation Error: ' . $e->getMessage();
            exit;
        }
    }

    public function singlePdf() {
        requireAdminLogin();
        $codeNumber = sanitize($_GET['code'] ?? '');

        if (empty($codeNumber)) {
            die('Missing QR Code parameter.');
        }

        $qrData = $this->qrModel->findByCode($codeNumber);
        if (!$qrData) {
            die('QR Tag not found.');
        }

        generateSingleTagPDF($codeNumber, $qrData['form_title'] ?? 'Vehicle QR Tag Registration');
        exit;
    }

    public function batchPdf() {
        requireAdminLogin();
        $batchId = (int)($_GET['batch_id'] ?? 0);

        if ($batchId <= 0) {
            die('Missing or invalid batch_id parameter.');
        }

        $batch = $this->batchModel->findById($batchId);
        if (!$batch) {
            die('Batch not found.');
        }

        $stmt = $this->pdo->prepare("SELECT code_number FROM qr_codes WHERE batch_id = ? ORDER BY id ASC");
        $stmt->execute([$batchId]);
        $qrCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($qrCodes)) {
            die('No QR Codes found in this batch.');
        }

        generateBatchPDF($qrCodes, $batch['form_title'], $batch['batch_name']);
        exit;
    }
}
