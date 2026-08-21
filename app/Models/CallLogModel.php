<?php
// app/Models/CallLogModel.php - Handles Database Queries for IVR Call Logs & Inbound Masked Mappings

class CallLogModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        if (!$this->pdo) return;
        try {
            $driver = '';
            try {
                $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            } catch (Exception $ex) {
                $driver = 'sqlite';
            }

            if ($driver === 'mysql') {
                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS `call_logs` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `code_number` VARCHAR(50) DEFAULT NULL,
                        `caller_phone` VARCHAR(50) DEFAULT NULL,
                        `user_number` VARCHAR(50) DEFAULT NULL,
                        `owner_phone` VARCHAR(50) DEFAULT NULL,
                        `ivr_number` VARCHAR(50) DEFAULT NULL,
                        `api_response` TEXT DEFAULT NULL,
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");

                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS `inbound_call_mappings` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `code_number` VARCHAR(50) NOT NULL,
                        `owner_phone` VARCHAR(50) NOT NULL,
                        `visitor_ip` VARCHAR(45) DEFAULT NULL,
                        `status` VARCHAR(20) DEFAULT 'active',
                        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            } else {
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
                    );
                ");

                $this->pdo->exec("
                    CREATE TABLE IF NOT EXISTS inbound_call_mappings (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        code_number TEXT NOT NULL,
                        owner_phone TEXT NOT NULL,
                        visitor_ip TEXT,
                        status TEXT DEFAULT 'active',
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            }
        } catch (Throwable $e) {
            // Silently swallow table creation exceptions to prevent 500 errors
        }
    }

    public function logCall($codeNumber, $userNumber, $ownerPhone, $ivrNumber, $apiResponse) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO call_logs (code_number, caller_phone, user_number, owner_phone, ivr_number, api_response)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$codeNumber, $userNumber, $userNumber, $ownerPhone, $ivrNumber, $apiResponse]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getLogsByCode($codeNumber, $limit = 5) {
        try {
            $stmt = $this->pdo->prepare("SELECT caller_phone, user_number, ivr_number, created_at FROM call_logs WHERE code_number = ? ORDER BY id DESC LIMIT ?");
            $stmt->execute([$codeNumber, (int)$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function getCallCountByCode($codeNumber) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM call_logs WHERE code_number = ?");
            $stmt->execute([$codeNumber]);
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function createMapping($codeNumber, $ownerPhone, $visitorIp = '') {
        try {
            $clearStmt = $this->pdo->prepare("DELETE FROM inbound_call_mappings WHERE code_number = ? OR (visitor_ip = ? AND visitor_ip != '')");
            $clearStmt->execute([$codeNumber, $visitorIp]);

            $stmt = $this->pdo->prepare("
                INSERT INTO inbound_call_mappings (code_number, owner_phone, visitor_ip, status)
                VALUES (?, ?, ?, 'active')
            ");
            return $stmt->execute([$codeNumber, $ownerPhone, $visitorIp]);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function findActiveMapping($visitorIp = '') {
        try {
            if (!empty($visitorIp)) {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM inbound_call_mappings 
                    WHERE (visitor_ip = ? OR status = 'active')
                    ORDER BY id DESC LIMIT 1
                ");
                $stmt->execute([$visitorIp]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($res) return $res;
            }

            $stmt = $this->pdo->query("SELECT * FROM inbound_call_mappings ORDER BY id DESC LIMIT 1");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return false;
        }
    }
}
