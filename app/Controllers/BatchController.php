<?php
// app/Controllers/BatchController.php - Admin Batch Management Controller

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../Models/BatchModel.php';
require_once __DIR__ . '/../Models/QRCodeModel.php';

class BatchController {
    private $pdo;
    private $batchModel;
    private $qrModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->batchModel = new BatchModel($pdo);
        $this->qrModel = new QRCodeModel($pdo);
    }

    public function index() {
        requireAdminLogin();
        $admin = getLoggedInAdmin();
        $pageTitle = 'QR Batches Management';

        $allBatches = $this->batchModel->getAllBatches();
        $stats = $this->batchModel->getStats();

        include __DIR__ . '/../Views/admin/batches.php';
    }
}
