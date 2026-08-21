<?php
// scan.php - Vehicle Sampark Mobile-Responsive Scanner & Owner Registration Page

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';

$codeNumber = sanitize($_GET['code'] ?? '');
$isScanPage = true;
$pageTitle = 'Vehicle Sampark - Contact Owner';

$error = '';
$success = false;

if (empty($codeNumber)) {
    $error = 'No QR Code specified. Please scan a valid Vehicle Sampark QR Code.';
} else {
    $stmt = $pdo->prepare("
        SELECT q.*, b.form_title, b.form_description, b.form_schema
        FROM qr_codes q
        JOIN batches b ON q.batch_id = b.id
        WHERE q.code_number = ?
    ");
    $stmt->execute([$codeNumber]);
    $qrData = $stmt->fetch();

    if (!$qrData) {
        $error = "Vehicle QR Code '$codeNumber' was not found or has been removed.";
    }
}

// Extract Owner Phone and Car Details for Mobile Contact Portal
$ownerName = '';
$mobileNumber = '';
$carName = '';
$carNumber = '';
$carModel = '';

if (!$error && $qrData['status'] === 'submitted') {
    $subStmt = $pdo->prepare("SELECT * FROM submissions WHERE qr_code_id = ? ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$qrData['id']]);
    $subRecord = $subStmt->fetch();

    if ($subRecord) {
        $respData = json_decode($subRecord['response_data'], true) ?: [];

        foreach ($respData as $lbl => $val) {
            $lblLower = strtolower($lbl);
            $valStr = (string)$val;

            if (empty($ownerName) && (str_contains($lblLower, 'name') || str_contains($lblLower, 'owner'))) {
                $ownerName = $valStr;
            }
            if (empty($mobileNumber) && (str_contains($lblLower, 'mobile') || str_contains($lblLower, 'phone') || str_contains($lblLower, 'contact') || str_contains($lblLower, 'number'))) {
                $mobileNumber = $valStr;
            }

            if (empty($carName) && (str_contains($lblLower, 'car name') || str_contains($lblLower, 'make') || (str_contains($lblLower, 'car') && !str_contains($lblLower, 'number') && !str_contains($lblLower, 'model')))) {
                $carName = $valStr;
            }
            if (empty($carNumber) && (str_contains($lblLower, 'car number') || str_contains($lblLower, 'plate') || str_contains($lblLower, 'reg') || str_contains($lblLower, 'vehicle number'))) {
                $carNumber = $valStr;
            }
            if (empty($carModel) && (str_contains($lblLower, 'model') || str_contains($lblLower, 'variant'))) {
                $carModel = $valStr;
            }
        }

        if (empty($mobileNumber)) $mobileNumber = reset($respData) ?: '9723914037';
    }
}

$cleanOwnerMobile = preg_replace('/[^\d]/', '', $mobileNumber);
if (strlen($cleanOwnerMobile) > 10) {
    $cleanOwnerMobile = substr($cleanOwnerMobile, -10);
}

// Handle Form Submission for Calling Vehicle Owner via BulkSMSPlans API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && isset($_POST['action_call_owner']) && $qrData['status'] === 'submitted') {
    $userInputNumber = sanitize($_POST['user_number'] ?? '');
    $cleanUserNumber = preg_replace('/[^\d]/', '', $userInputNumber);
    if (strlen($cleanUserNumber) > 10) {
        $cleanUserNumber = substr($cleanUserNumber, -10);
    }

    if (empty($cleanUserNumber) || strlen($cleanUserNumber) < 10) {
        $error = 'Please enter a valid 10-digit mobile number.';
    } else {
        // 1. Store User Number in Database (call_logs table as user_number)
        try {
            $pdo->exec("
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

            $logStmt = $pdo->prepare("
                INSERT INTO call_logs (code_number, caller_phone, user_number, owner_phone, ivr_number, api_response)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $logStmt->execute([$codeNumber, $cleanUserNumber, $cleanUserNumber, $cleanOwnerMobile, '7971123254', 'BulkSMSPlans API Call Request Triggered']);
        } catch (Exception $e) {
            // Non-blocking log error
        }

        // 2. Trigger BulkSMSPlans IVR API (dial = User's Inputted Number, agent_number = Owner Number from DB)
        $apiUrl = 'https://bulksmsplans.com/api/ivr/makeACall';
        $params = [
            'api_id'          => 'APIvRpMDIEc151987',
            'api_password'    => 'fetRZg6V',
            'ivr_number'      => '7971123254',
            'dial'            => $cleanUserNumber, // User's inputted number as dialer
            'receiver_number' => $cleanUserNumber, // User's inputted number as receiver
            'agent_number'    => $cleanOwnerMobile, // Owner's number from DB as agent
            'scheduled'       => '0',
            'timezone_id'     => '0',
            'ai_connect'      => '0'
        ];

        $targetUrl = $apiUrl . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $apiResponse = curl_exec($ch);
        curl_close($ch);

        // 3. Refresh Page & Show Call Initiated Message with User Number (without auto opening phone dialer)
        header("Location: scan.php?code=" . urlencode($codeNumber) . "&call_status=initiated&user_number=" . urlencode($cleanUserNumber));
        exit;
    }
}

// Handle First-Time Owner Registration Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && $qrData['status'] === 'pending') {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $mobileNumber = sanitize($_POST['mobile_number'] ?? '');
    $emergencyMobileNumber = sanitize($_POST['emergency_mobile_number'] ?? '');
    $whatsappNumber = sanitize($_POST['whatsapp_number'] ?? $mobileNumber);
    $carNumber = strtoupper(trim(sanitize($_POST['car_number'] ?? '')));
    $carName = sanitize($_POST['car_name'] ?? '');
    $carModel = sanitize($_POST['car_model'] ?? '');

    // Input Validations
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
            $pdo->beginTransaction();
            $submitterIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $subStmt = $pdo->prepare("
                INSERT INTO submissions (qr_code_id, code_number, response_data, file_paths, submitter_ip)
                VALUES (:qr_code_id, :code_number, :response_data, :file_paths, :submitter_ip)
            ");
            $subStmt->execute([
                ':qr_code_id' => $qrData['id'],
                ':code_number' => $codeNumber,
                ':response_data' => json_encode($responses, JSON_UNESCAPED_UNICODE),
                ':file_paths' => json_encode([], JSON_UNESCAPED_UNICODE),
                ':submitter_ip' => $submitterIp
            ]);

            $upStmt = $pdo->prepare("UPDATE qr_codes SET status = 'submitted', submitted_at = CURRENT_TIMESTAMP WHERE id = ?");
            $upStmt->execute([$qrData['id']]);

            $pdo->commit();
            $success = true;
            $qrData['status'] = 'submitted';

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

$callStatus = sanitize($_GET['call_status'] ?? '');
$displayUserNumber = sanitize($_GET['user_number'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<div class="public-form-container" style="max-width: 640px; margin: 1.5rem auto; padding: 0 0.5rem;">
    <?php if ($error): ?>
        <div class="content-card" style="padding: 2rem 1.5rem; text-align: center;">
            <i class="fa-solid fa-triangle-exclamation empty-icon" style="color: var(--accent-rose); font-size: 3rem;"></i>
            <h3 style="margin-top: 1rem; color: var(--text-main);">Scanning Error</h3>
            <p style="color: var(--accent-rose); font-weight: 500; margin-top: 0.5rem;"><?= htmlspecialchars($error) ?></p>
            <div style="margin-top: 1.5rem;">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-house"></i> Go to Homepage</a>
            </div>
        </div>

    <?php elseif ($success): ?>
        <!-- Owner Registration Confirmation -->
        <div class="content-card" style="padding: 2.5rem 1.5rem; text-align: center;">
            <div style="width: 68px; height: 68px; background: #ecfdf5; color: var(--primary); border: 2px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.25rem auto;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 1.6rem; color: var(--text-main); font-weight: 800; margin-bottom: 0.5rem;">Vehicle QR Registration Complete!</h1>
            <p style="color: var(--text-muted); margin-bottom: 1.25rem; font-size: 0.92rem;">
                Congratulations! You are now the registered owner of QR Code <strong style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($codeNumber) ?></strong>.
            </p>
            <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="scan.php?code=<?= urlencode($codeNumber) ?>" class="btn btn-primary" style="flex: 1; min-width: 200px;">
                    <i class="fa-solid fa-qrcode"></i> Test Public Contact Page
                </a>
            </div>
        </div>

    <?php elseif ($qrData['status'] === 'submitted'): ?>
        <!-- MOBILE PUBLIC BYSTANDER CONTACT PORTAL -->
        <div class="content-card" style="padding: 1.75rem 1.25rem; text-align: center; border-color: #cbd5e1;">
            <div style="margin-bottom: 0.85rem;">
                <span class="badge badge-submitted" style="font-size: 0.82rem; padding: 0.35rem 0.85rem;">
                    <i class="fa-solid fa-shield-halved"></i> Vehicle Sampark Verified Tag
                </span>
            </div>
            
            <div style="margin: 0.5rem 0 1rem 0;">
                <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" style="height: 52px; border-radius: var(--radius-sm);" onerror="this.src='assets/images/logo-icon.svg'">
            </div>

            <h1 style="font-size: 1.65rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Contact Vehicle Owner</h1>
            
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; display: inline-block; margin-bottom: 1.25rem; width: 100%;">
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.2rem;">
                    Serial Tag: <strong style="color: var(--primary); font-family: monospace; font-size: 0.92rem;"><?= htmlspecialchars($codeNumber) ?></strong>
                </div>
                <?php if ($carNumber || $carName): ?>
                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--accent-orange);">
                        <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("$carName $carModel $carNumber")) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($callStatus === 'initiated'): ?>
                <!-- REFRESHED CALL INITIATED CONFIRMATION BANNER (SERVER-SIDE API EXECUTED) -->
                <div style="background: #ecfdf5; border: 2px solid #a7f3d0; padding: 1.5rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; text-align: left;">
                    <div style="font-weight: 800; color: #065f46; font-size: 1.15rem; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.4rem;"></i> Calling Initiated Towards Vehicle Owner!
                    </div>
                    <p style="font-size: 0.88rem; color: #047857; margin-bottom: 1rem; line-height: 1.5;">
                        Your call request has been processed through the BulkSMSPlans IVR API. The IVR system will bridge the call between your mobile line and the vehicle owner.
                    </p>
                    
                    <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 0.75rem;">
                        <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.2rem;">Your Inputted Mobile Number (Dialer):</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; font-family: monospace;">
                            <i class="fa-solid fa-mobile-screen" style="color: #10b981;"></i> <?= htmlspecialchars($displayUserNumber) ?>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 1rem;">
                        <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.2rem;">Agent (Vehicle Owner):</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0284c7; font-family: monospace;">
                            <i class="fa-solid fa-user-shield" style="color: #0284c7;"></i> Registered Owner Connected via IVR (7971123254)
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" class="btn btn-outline" style="font-size: 0.88rem; font-weight: 700; color: #047857; border-color: #a7f3d0; text-decoration: none;">
                            <i class="fa-solid fa-rotate-left"></i> Make Another Call Request
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- USER INPUT FORM TO ENTER MOBILE NUMBER & CALL THE OWNER -->
                <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST" style="text-align: left; margin-bottom: 1.25rem;">
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 1.25rem; border-radius: 16px;">
                        <label style="font-size: 0.88rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; display: block;">
                            Enter Your Mobile Number <span style="color: var(--accent-rose);">*</span>
                        </label>
                        <div style="position: relative; margin-bottom: 1rem;">
                            <input type="tel" name="user_number" class="form-control" placeholder="Enter your 10-digit mobile number" style="padding-left: 2.6rem; height: 48px; border-radius: 12px; font-size: 1.05rem; font-weight: 700; border: 1.5px solid #10b981;" required>
                            <i class="fa-solid fa-phone" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #10b981; font-size: 1.1rem;"></i>
                        </div>

                        <button type="submit" name="action_call_owner" class="btn btn-primary btn-glow" style="width: 100%; padding: 1.15rem; font-size: 1.15rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: var(--radius-lg); font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.65rem; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);">
                            <i class="fa-solid fa-phone-volume" style="font-size: 1.35rem;"></i> Call the Owner
                        </button>
                    </div>
                </form>
            <?php endif; ?>

            <div style="background: #f8fafc; padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0; line-height: 1.45;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> <strong>100% Privacy Protection:</strong> BulkSMSPlans IVR API bridges the call using agent_number and dialer number securely.
                </p>
            </div>
        </div>

    <?php else: ?>
        <!-- ==========================================================================
             VEHICLE OWNER REGISTRATION FORM
             ========================================================================== -->
        <div class="content-card" style="padding: 1.75rem 1.25rem;">
            <div style="text-align: center; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color);">
                <div style="margin-bottom: 0.4rem;">
                    <span class="code-badge"><?= htmlspecialchars($codeNumber) ?></span>
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Vehicle Owner Registration</h1>
                <p style="color: var(--text-muted); font-size: 0.88rem;">
                    Fill out your vehicle details to register this QR Tag. Once registered, public scanners can call you securely.
                </p>
            </div>

            <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="mobile_number" class="form-control" placeholder="e.g. 98765 43210" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="emergency_mobile_number" class="form-control" placeholder="e.g. 98765 43210" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Car Number / License Plate <span class="required">*</span></label>
                    <input type="text" name="car_number" class="form-control" placeholder="e.g. GJ-03-NL-0104" style="text-transform: uppercase;" required>
                    <span class="form-help">Format example: GJ-03-NL-0104 or MH-01-AB-1234</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Car Name / Brand <span class="required">*</span></label>
                    <input type="text" name="car_name" class="form-control" placeholder="e.g. Hyundai, Toyota, Honda" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Car Model <span class="required">*</span></label>
                    <input type="text" name="car_model" class="form-control" placeholder="e.g. Creta, Fortuner, City" required>
                </div>

                <div style="margin-top: 1.75rem;">
                    <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
                        <i class="fa-solid fa-id-card"></i> Register Vehicle Owner & Activate QR Tag
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
