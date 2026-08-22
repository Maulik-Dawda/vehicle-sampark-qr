<?php
// app/Models/SubmissionModel.php - Handles Database Queries for Vehicle Owner Submissions

class SubmissionModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getByQrId($qrCodeId) {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions WHERE qr_code_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$qrCodeId]);
        return $stmt->fetch();
    }

    public function getByCodeNumber($codeNumber) {
        $stmt = $this->pdo->prepare("SELECT * FROM submissions WHERE code_number = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$codeNumber]);
        return $stmt->fetch();
    }

    public function createSubmission($qrCodeId, $codeNumber, $responses, $submitterIp) {
        $stmt = $this->pdo->prepare("
            INSERT INTO submissions (qr_code_id, code_number, response_data, file_paths, submitter_ip)
            VALUES (:qr_code_id, :code_number, :response_data, :file_paths, :submitter_ip)
        ");
        return $stmt->execute([
            ':qr_code_id' => $qrCodeId,
            ':code_number' => $codeNumber,
            ':response_data' => json_encode($responses, JSON_UNESCAPED_UNICODE),
            ':file_paths' => json_encode([], JSON_UNESCAPED_UNICODE),
            ':submitter_ip' => $submitterIp
        ]);
    }

    public function extractOwnerDetails($responseData) {
        $respData = is_string($responseData) ? (json_decode($responseData, true) ?: []) : $responseData;
        $ownerName = '';
        $mobileNumber = '';
        $alternatePhone = '';
        $emergencyMobileNumber = '';
        $whatsappNumber = '';
        $carName = '';
        $carNumber = '';
        $carModel = '';

        foreach ($respData as $lbl => $val) {
            $lblLower = strtolower($lbl);
            $valStr = trim((string)$val);

            if (empty($ownerName) && (str_contains($lblLower, 'name') || str_contains($lblLower, 'owner'))) {
                $ownerName = $valStr;
            }
            
            if (str_contains($lblLower, 'alternate') || str_contains($lblLower, 'secondary')) {
                $alternatePhone = $valStr;
            } elseif (str_contains($lblLower, 'emergency')) {
                $emergencyMobileNumber = $valStr;
            } elseif (str_contains($lblLower, 'whatsapp')) {
                $whatsappNumber = $valStr;
            } elseif (empty($mobileNumber) && (str_contains($lblLower, 'mobile') || str_contains($lblLower, 'phone') || str_contains($lblLower, 'contact'))) {
                $mobileNumber = $valStr;
            }

            if (empty($carName) && (str_contains($lblLower, 'car name') || str_contains($lblLower, 'make') || str_contains($lblLower, 'brand') || (str_contains($lblLower, 'car') && !str_contains($lblLower, 'number') && !str_contains($lblLower, 'model')))) {
                $carName = $valStr;
            }
            if (empty($carNumber) && (str_contains($lblLower, 'car number') || str_contains($lblLower, 'plate') || str_contains($lblLower, 'reg') || str_contains($lblLower, 'vehicle number') || str_contains($lblLower, 'license'))) {
                $carNumber = $valStr;
            }
            if (empty($carModel) && (str_contains($lblLower, 'model') || str_contains($lblLower, 'variant'))) {
                $carModel = $valStr;
            }
        }

        if (empty($mobileNumber)) $mobileNumber = reset($respData) ?: '9723914037';
        if (empty($alternatePhone)) $alternatePhone = $emergencyMobileNumber ?: $mobileNumber;
        if (empty($emergencyMobileNumber)) $emergencyMobileNumber = $alternatePhone ?: $mobileNumber;
        if (empty($whatsappNumber)) $whatsappNumber = $mobileNumber;

        $cleanOwnerMobile = preg_replace('/[^\d]/', '', $mobileNumber);
        if (strlen($cleanOwnerMobile) > 10) {
            $cleanOwnerMobile = substr($cleanOwnerMobile, -10);
        }

        $cleanAlternatePhone = preg_replace('/[^\d]/', '', $alternatePhone);
        if (strlen($cleanAlternatePhone) > 10) {
            $cleanAlternatePhone = substr($cleanAlternatePhone, -10);
        }

        $cleanEmergencyMobile = preg_replace('/[^\d]/', '', $emergencyMobileNumber);
        if (strlen($cleanEmergencyMobile) > 10) {
            $cleanEmergencyMobile = substr($cleanEmergencyMobile, -10);
        }

        $cleanWhatsappNumber = preg_replace('/[^\d]/', '', $whatsappNumber);
        if (strlen($cleanWhatsappNumber) > 10) {
            $cleanWhatsappNumber = substr($cleanWhatsappNumber, -10);
        }

        return [
            'owner_name' => $ownerName,
            'mobile_number' => $mobileNumber,
            'alternate_phone' => $alternatePhone,
            'emergency_mobile_number' => $emergencyMobileNumber,
            'whatsapp_number' => $whatsappNumber,
            'clean_owner_mobile' => $cleanOwnerMobile,
            'clean_alternate_phone' => $cleanAlternatePhone,
            'clean_emergency_mobile' => $cleanEmergencyMobile,
            'clean_whatsapp_number' => $cleanWhatsappNumber,
            'car_name' => $carName,
            'car_number' => $carNumber,
            'car_model' => $carModel
        ];
    }
}
