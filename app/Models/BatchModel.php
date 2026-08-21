<?php
// app/Models/BatchModel.php - Handles Database Queries for Batches

class BatchModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllBatches() {
        $stmt = $this->pdo->query("SELECT id, batch_name, form_title, total_qrs, created_at FROM batches ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM batches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createBatch($batchName, $formTitle, $formDescription, $formSchemaJson, $quantity) {
        $stmt = $this->pdo->prepare("
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
        return $this->pdo->lastInsertId();
    }

    public function getStats() {
        return [
            'total_qrs' => $this->pdo->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn(),
            'submitted_qrs' => $this->pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'submitted'")->fetchColumn(),
            'pending_qrs' => $this->pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'pending'")->fetchColumn(),
            'total_batches' => $this->pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn(),
            'total_bot_logs' => $this->pdo->query("SELECT COUNT(*) FROM bot_logs")->fetchColumn(),
        ];
    }
}
