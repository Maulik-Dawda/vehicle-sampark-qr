<?php
// app/Models/CallLogModel.php - Handles Database Queries for IVR Call Logs

class CallLogModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS call_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    code_number TEXT,
                    caller_phone TEXT,
                    user_number TEXT,
                    owner_phone TEXT,
                    ivr_number TEXT,
                    api_response TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (Exception $e) {
            // Table creation check
        }
    }

    public function logCall($codeNumber, $userNumber, $ownerPhone, $ivrNumber, $apiResponse) {
        $stmt = $this->pdo->prepare("
            INSERT INTO call_logs (code_number, caller_phone, user_number, owner_phone, ivr_number, api_response)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$codeNumber, $userNumber, $userNumber, $ownerPhone, $ivrNumber, $apiResponse]);
    }

    public function getLogsByCode($codeNumber, $limit = 5) {
        $stmt = $this->pdo->prepare("SELECT caller_phone, user_number, ivr_number, created_at FROM call_logs WHERE code_number = ? ORDER BY id DESC LIMIT ?");
        $stmt->execute([$codeNumber, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCallCountByCode($codeNumber) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM call_logs WHERE code_number = ?");
        $stmt->execute([$codeNumber]);
        return (int)$stmt->fetchColumn();
    }
}
