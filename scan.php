<?php
// scan.php - Vehicle Sampark Mobile-Responsive Scanner & Owner Registration Page

require_once __DIR__ . '/config/database.php';
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
    $whatsappNumber = sanitize($_POST['whatsapp_number'] ?? '');
    $emergencyMobileNumber = sanitize($_POST['emergency_mobile_number'] ?? '');
    $carNumber = strtoupper(trim(sanitize($_POST['car_number'] ?? '')));
    $carName = sanitize($_POST['car_name'] ?? '');
    $carModel = sanitize($_POST['car_model'] ?? '');

    // Input Validations
    if (empty($fullName) || empty($mobileNumber) || empty($whatsappNumber) || empty($emergencyMobileNumber) || empty($carNumber) || empty($carName) || empty($carModel)) {
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

// Extract Owner Phone, WhatsApp, and Car Details for Mobile Contact Portal
$ownerName = '';
$mobileNumber = '';
$whatsappNumber = '';
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
            if (str_contains($lblLower, 'whatsapp')) {
                $whatsappNumber = $valStr;
            } elseif (empty($mobileNumber) && (str_contains($lblLower, 'mobile') || str_contains($lblLower, 'phone') || str_contains($lblLower, 'contact') || str_contains($lblLower, 'number'))) {
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

        if (empty($mobileNumber)) $mobileNumber = reset($respData) ?: '';
        if (empty($whatsappNumber)) $whatsappNumber = $mobileNumber;
    }
}

$cleanPhoneTel = preg_replace('/[^\d+]/', '', $mobileNumber);

// Vehicle Sampark Company Official WhatsApp Bot Number
$companyBotWhatsapp = '919876543210'; 
$waBotMessage = "Hi Vehicle Sampark! I am scanning QR code " . $codeNumber . ($carNumber ? " for vehicle plate " . $carNumber : "") . ". I would like to report an emergency issue or contact the vehicle owner.";

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

            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Contact Vehicle Owner</h1>
            
            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; display: inline-block; margin-bottom: 1.25rem; width: 100%;">
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.2rem;">
                    Serial Tag: <strong style="color: var(--primary); font-family: monospace; font-size: 0.92rem;"><?= htmlspecialchars($codeNumber) ?></strong>
                </div>
                <?php if ($carNumber || $carName): ?>
                    <div style="font-size: 1rem; font-weight: 800; color: var(--accent-orange);">
                        <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("$carName $carModel $carNumber")) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Need to alert the vehicle owner about wrong parking, emergency, or moving the car? Tap below to connect instantly:
            </p>

            <!-- DIRECT MOBILE CALL BUTTON -->
            <div style="margin-bottom: 1rem;">
                <a href="tel:<?= htmlspecialchars($cleanPhoneTel) ?>" class="btn btn-primary btn-glow" style="width: 100%; padding: 1rem; font-size: 1.05rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: var(--radius-lg); font-weight: 700;">
                    <i class="fa-solid fa-phone-volume" style="font-size: 1.2rem;"></i> Call Vehicle Owner Directly
                </a>
            </div>

            <!-- DIRECT WHATSAPP BOT BUTTON -->
            <div style="margin-bottom: 1rem;">
                <a href="https://wa.me/<?= htmlspecialchars($companyBotWhatsapp) ?>?text=<?= urlencode($waBotMessage) ?>" target="_blank" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.05rem; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: var(--radius-lg); font-weight: 700;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.35rem;"></i> Chat on WhatsApp (Vehicle Sampark Bot)
                </a>
            </div>

            <!-- INTERACTIVE BOT SIMULATION BUTTON -->
            <div style="margin-bottom: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="openWhatsAppBotSimulator('<?= htmlspecialchars($codeNumber) ?>', '<?= htmlspecialchars($carNumber) ?>')" style="width: 100%; padding: 0.75rem; font-size: 0.92rem; font-weight: 600;">
                    <i class="fa-solid fa-robot" style="color: var(--primary);"></i> Test Interactive WhatsApp Bot Flow
                </button>
            </div>

            <div style="background: #f8fafc; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0;">
                <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
                    <i class="fa-solid fa-lock" style="color: var(--primary);"></i> Powered by <strong>Vehicle Sampark</strong>. Official WhatsApp Bot API Integration.
                </p>
            </div>
        </div>

    <?php else: ?>
        <!-- ==========================================================================
             FIXED 7 VEHICLE OWNER REGISTRATION FORM
             ========================================================================== -->
        <div class="content-card" style="padding: 1.75rem 1.25rem;">
            <div style="text-align: center; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color);">
                <div style="margin-bottom: 0.4rem;">
                    <span class="code-badge"><?= htmlspecialchars($codeNumber) ?></span>
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Vehicle Owner Registration</h1>
                <p style="color: var(--text-muted); font-size: 0.88rem;">
                    Fill out your vehicle details to register this QR Tag. Once registered, public scanners can call or WhatsApp you directly.
                </p>
            </div>

            <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="mobile_number" class="form-control" placeholder="+91 98765 43210" required>
                </div>

                <div class="form-group">
                    <label class="form-label">WhatsApp Number <span class="required">*</span></label>
                    <input type="tel" name="whatsapp_number" class="form-control" placeholder="+91 98765 43210" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="emergency_mobile_number" class="form-control" placeholder="+91 98765 43210" required>
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

<!-- WhatsApp Bot Simulator Modal -->
<div class="modal-backdrop" id="botSimulatorModal">
    <div class="modal-card" style="max-width: 480px; height: 600px;">
        <div class="modal-header" style="background: #075e54; color: #ffffff;">
            <h3 style="color: #ffffff;"><i class="fa-brands fa-whatsapp" style="font-size: 1.4rem;"></i> Vehicle Sampark WhatsApp Bot</h3>
            <button type="button" class="modal-close" style="color: #ffffff;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="botChatContainer" style="background: #e5ddd5; display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem;">
            <!-- Dynamic WhatsApp Chat Bubbles render here -->
        </div>
    </div>
</div>

<script>
function openWhatsAppBotSimulator(code, carNum) {
    const modal = document.getElementById('botSimulatorModal');
    const chatBox = document.getElementById('botChatContainer');
    modal.classList.add('show');

    chatBox.innerHTML = `
        <div style="align-self: flex-start; background: #ffffff; padding: 0.75rem; border-radius: 8px; max-width: 85%; font-size: 0.88rem; box-shadow: 0 1px 2px rgba(0,0,0,0.15);">
            👋 <strong>Vehicle Sampark Bot:</strong> Welcome! You scanned vehicle tag <strong>${code}</strong>.<br><br>
            Please confirm the vehicle plate number: <strong>${carNum || 'GJ-03-NL-0104'}</strong>
            <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-primary btn-sm" onclick="botConfirmPlate(true, '${code}', '${carNum}')">✅ Yes, Correct</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="botConfirmPlate(false, '${code}', '${carNum}')">❌ No / Enter Other</button>
            </div>
        </div>
    `;
}

function botConfirmPlate(confirmed, code, carNum) {
    const chatBox = document.getElementById('botChatContainer');
    
    if (confirmed) {
        chatBox.innerHTML += `
            <div style="align-self: flex-end; background: #dcf8c6; padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.88rem;">
                ✅ Yes, Correct (${carNum})
            </div>
        `;
        showBotIssuesList(code, carNum);
    } else {
        chatBox.innerHTML += `
            <div style="align-self: flex-end; background: #dcf8c6; padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.88rem;">
                ❌ No, let me type plate number
            </div>
            <div style="align-self: flex-start; background: #ffffff; padding: 0.75rem; border-radius: 8px; max-width: 85%; font-size: 0.88rem;">
                🤖 Please enter the vehicle plate number below (e.g. GJ-03-NL-0104) to check database:
                <div style="display: flex; gap: 0.4rem; margin-top: 0.5rem;">
                    <input type="text" id="botCustomPlateInput" class="form-control form-control-sm" placeholder="e.g. GJ-03-NL-0104" style="text-transform: uppercase;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="botLookupCustomPlate()">Search</button>
                </div>
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

function botLookupCustomPlate() {
    const chatBox = document.getElementById('botChatContainer');
    const inputVal = document.getElementById('botCustomPlateInput').value;

    if (!inputVal) return;

    chatBox.innerHTML += `
        <div style="align-self: flex-end; background: #dcf8c6; padding: 0.65rem 0.85rem; border-radius: 8px; font-size: 0.88rem;">
            Searching plate: ${inputVal}
        </div>
    `;

    fetch(`api/whatsapp_bot.php?action=lookup_custom_plate&car_number=${encodeURIComponent(inputVal)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showBotIssuesList(data.data.code_number, data.data.car_number);
            } else {
                chatBox.innerHTML += `
                    <div style="align-self: flex-start; background: #ffebee; border: 1px solid #ffcdd2; color: #c62828; padding: 0.75rem; border-radius: 8px; max-width: 85%; font-size: 0.88rem;">
                        ⚠️ <strong>Vehicle Sampark Bot:</strong> ${data.message}
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
}

function showBotIssuesList(code, carNum) {
    const chatBox = document.getElementById('botChatContainer');
    chatBox.innerHTML += `
        <div style="align-self: flex-start; background: #ffffff; padding: 0.75rem; border-radius: 8px; max-width: 85%; font-size: 0.88rem;">
            🚨 <strong>Select the Emergency Issue to notify owner:</strong>
            <div style="display: flex; flex-direction: column; gap: 0.4rem; margin-top: 0.6rem;">
                <button type="button" class="btn btn-outline btn-sm" onclick="botSelectIssue('1', '${code}', '${carNum}')">1. 🚫 Vehicle Wrong Parked</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="botSelectIssue('2', '${code}', '${carNum}')">2. 🚨 Vehicle Accident / Emergency</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="botSelectIssue('3', '${code}', '${carNum}')">3. ⚠️ Lights On / Window Open</button>
                <button type="button" class="btn btn-outline btn-sm" onclick="botSelectIssue('4', '${code}', '${carNum}')">4. 📞 Towing / Obstruction Notice</button>
            </div>
        </div>
    `;
    chatBox.scrollTop = chatBox.scrollHeight;
}

function botSelectIssue(issueKey, code, carNum) {
    const chatBox = document.getElementById('botChatContainer');
    
    fetch(`api/whatsapp_bot.php?action=submit_issue&code=${encodeURIComponent(code)}&car_number=${encodeURIComponent(carNum)}&issue=${issueKey}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                chatBox.innerHTML += `
                    <div style="align-self: flex-start; background: #e8f5e9; border: 1px solid #c8e6c9; padding: 0.75rem; border-radius: 8px; max-width: 85%; font-size: 0.88rem; color: #1b5e20;">
                        ✅ <strong>Bystander Message:</strong> ${data.data.bystander_message}<br><br>
                        📲 <strong>Owner Alert Relayed:</strong><br>
                        <em>"${data.data.owner_relay_alert}"</em>
                    </div>
                `;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
