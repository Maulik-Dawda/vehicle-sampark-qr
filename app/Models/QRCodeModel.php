<?php
// app/Models/QRCodeModel.php - Handles Database Queries for QR Codes

class QRCodeModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByCode($codeNumber) {
        $stmt = $this->pdo->prepare("
            SELECT q.*, b.form_title, b.form_description, b.form_schema, b.batch_name
            FROM qr_codes q
            JOIN batches b ON q.batch_id = b.id
            WHERE q.code_number = ?
        ");
        $stmt->execute([$codeNumber]);
        return $stmt->fetch();
    }

    public function listFiltered($batchFilter = 0, $statusFilter = '', $searchQuery = '', $limit = 15, $offset = 0) {
        $whereClauses = [];
        $queryParams = [];

        if ($batchFilter > 0) {
            $whereClauses[] = "q.batch_id = :batch_id";
            $queryParams[':batch_id'] = $batchFilter;
        }

        if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'submitted'])) {
            $whereClauses[] = "q.status = :status";
            $queryParams[':status'] = $statusFilter;
        }

        if (!empty($searchQuery)) {
            $whereClauses[] = "(q.code_number LIKE :search OR b.batch_name LIKE :search OR b.form_title LIKE :search)";
            $queryParams[':search'] = '%' . $searchQuery . '%';
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        $sql = "
            SELECT q.*, b.batch_name, b.form_title,
                   (SELECT COUNT(*) FROM submissions s WHERE s.qr_code_id = q.id) as submission_count
            FROM qr_codes q
            JOIN batches b ON q.batch_id = b.id
            $whereSql
            ORDER BY q.id DESC
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($queryParams);
        return $stmt->fetchAll();
    }

    public function countFiltered($batchFilter = 0, $statusFilter = '', $searchQuery = '') {
        $whereClauses = [];
        $queryParams = [];

        if ($batchFilter > 0) {
            $whereClauses[] = "q.batch_id = :batch_id";
            $queryParams[':batch_id'] = $batchFilter;
        }

        if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'submitted'])) {
            $whereClauses[] = "q.status = :status";
            $queryParams[':status'] = $statusFilter;
        }

        if (!empty($searchQuery)) {
            $whereClauses[] = "(q.code_number LIKE :search OR b.batch_name LIKE :search OR b.form_title LIKE :search)";
            $queryParams[':search'] = '%' . $searchQuery . '%';
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM qr_codes q
            JOIN batches b ON q.batch_id = b.id
            $whereSql
        ");
        $stmt->execute($queryParams);
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus($qrId, $status) {
        $stmt = $this->pdo->prepare("UPDATE qr_codes SET status = ?, submitted_at = CURRENT_TIMESTAMP WHERE id = ?");
        return $stmt->execute([$status, $qrId]);
    }
}
