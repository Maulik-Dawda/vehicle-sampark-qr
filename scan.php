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
            
            <!-- SIM PHONE SELECTION / INPUT CARD -->
            <div class="sim-card-box" style="background: #f0fdf4; border: 1.5px solid #bbf7d0; padding: 1.25rem; border-radius: 16px; margin-bottom: 1.25rem; text-align: left;">
                <div style="font-weight: 800; color: #047857; font-size: 0.98rem; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-sim-card" style="font-size: 1.2rem; color: #10b981;"></i> Your SIM / Phone Number
                </div>
                <p style="font-size: 0.83rem; color: #334155; margin-bottom: 0.85rem; line-height: 1.45;">
                    Select or enter your SIM mobile number present in this phone to initiate direct calling to the vehicle owner.
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div>
                        <label style="font-size: 0.8rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem; display: block;">Your Phone Number (SIM Number):</label>
                        <input type="tel" id="userSimPhoneInput" class="form-control" placeholder="Enter your 10-digit mobile number" style="font-size: 1rem; font-weight: 600; height: 46px; border-radius: 10px; border: 1.5px solid #cbd5e1;">
                    </div>

                    <!-- DIRECT 1-TAP CALL BUTTON (TRIGGERS API & BUILT-IN INBUILT PHONE APP INSTANTLY) -->
                    <button type="button" id="btnDirectCallOwner" onclick="initiateDirectMobileCall('<?= htmlspecialchars($codeNumber) ?>', '<?= htmlspecialchars($cleanOwnerMobile) ?>')" class="btn btn-primary btn-glow" style="width: 100%; padding: 1.1rem; font-size: 1.1rem; background: linear-gradient(135deg, #10b981, #059669); font-weight: 800; border-radius: 14px; display: flex; align-items: center; justify-content: center; gap: 0.65rem; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);">
                        <i class="fa-solid fa-phone-volume" style="font-size: 1.3rem;"></i> Direct Call Vehicle Owner
                    </button>
                </div>
            </div>

            <div style="background: #f8fafc; padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; text-align: center;">
                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0; line-height: 1.45;">
                    <i class="fa-solid fa-bolt" style="color: var(--primary);"></i> <strong>Instant Connection:</strong> Tapping "Direct Call Vehicle Owner" immediately launches your phone's built-in call app to ring the vehicle owner directly.
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

<script>
function initiateDirectMobileCall(codeNumber, ownerNumber) {
    const userPhoneInput = document.getElementById('userSimPhoneInput');
    const userDial = userPhoneInput ? userPhoneInput.value.trim() : '';

    // 1. Send API Request in background
    const formData = new URLSearchParams();
    formData.append('code', codeNumber);
    formData.append('dial', userDial);

    fetch('api/make_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
    });

    // 2. Directly initiate call in mobile's built-in phone app without permission dialogs
    const targetTel = ownerNumber || '7971123254';
    window.location.href = 'tel:' + targetTel;
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
