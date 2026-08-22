<?php
// api/whatsapp_menu.php - WhatsApp Integration API 2: Role-Based Dynamic Services Menu (Owner vs Public Visitor)
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Models/QRCodeModel.php';
require_once __DIR__ . '/../app/Models/SubmissionModel.php';

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

$contactPhoneRaw = sanitize($params['contact_phone'] ?? ($params['sender_phone'] ?? ($params['from'] ?? '')));
$codeNumber = sanitize($params['code'] ?? '');
$vehicleNumber = strtoupper(trim(sanitize($params['vehicle_number'] ?? ($params['car_number'] ?? ''))));

$cleanSenderPhone = preg_replace('/[^\d]/', '', $contactPhoneRaw);
if (strlen($cleanSenderPhone) > 10) {
    $cleanSenderPhone = substr($cleanSenderPhone, -10);
}

$subModel = new SubmissionModel($pdo);
$qrModel = new QRCodeModel($pdo);

$qrRecord = null;
$subRecord = null;

if (!empty($codeNumber)) {
    $qrRecord = $qrModel->findByCode($codeNumber);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
} elseif (!empty($vehicleNumber)) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE UPPER(response_data) LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['%' . $vehicleNumber . '%']);
    $subRecord = $stmt->fetch();
    if ($subRecord) {
        $codeNumber = $subRecord['code_number'];
        $qrRecord = $qrModel->findByCode($codeNumber);
    }
} elseif (!empty($cleanSenderPhone)) {
    $stmt = $pdo->prepare("SELECT * FROM submissions WHERE response_data LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['%' . $cleanSenderPhone . '%']);
    $subRecord = $stmt->fetch();
    if ($subRecord) {
        $codeNumber = $subRecord['code_number'];
        $qrRecord = $qrModel->findByCode($codeNumber);
    }
}

// Fallback to default QRC-853B-32D7 if query empty
if (!$subRecord) {
    $codeNumber = 'QRC-853B-32D7';
    $qrRecord = $qrModel->findByCode($codeNumber);
    if ($qrRecord) {
        $subRecord = $subModel->getByQrId($qrRecord['id']);
    }
}

$ownerDetails = $subModel->extractOwnerDetails($subRecord['response_data'] ?? []);
$appBaseUrl = getAppBaseUrl();

// Check if WhatsApp contacting number belongs to the registered vehicle owner
$ownerPhones = array_filter([
    $ownerDetails['clean_owner_mobile'] ?? '',
    $ownerDetails['clean_alternate_phone'] ?? '',
    $ownerDetails['clean_emergency_mobile'] ?? '',
    $ownerDetails['clean_whatsapp_number'] ?? ''
]);

$isOwner = false;
if (!empty($cleanSenderPhone) && in_array($cleanSenderPhone, $ownerPhones, true)) {
    $isOwner = true;
}

$carTitle = trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}");
if (empty($carTitle)) {
    $carTitle = "Vehicle (" . htmlspecialchars($codeNumber) . ")";
}

if ($isOwner) {
    // OWNER ROLE MENU & SERVICES LIST
    $menuData = [
        'user_role' => 'owner',
        'is_owner' => true,
        'contact_phone' => $cleanSenderPhone,
        'code_number' => $codeNumber,
        'vehicle_title' => $carTitle,
        'welcome_message' => "Welcome back, " . ($ownerDetails['owner_name'] ?: 'Vehicle Owner') . "! You are logged in as the verified Owner of {$carTitle} (QR: {$codeNumber}).",
        'menu_header' => "⚙️ Vehicle Owner Control Panel Services:",
        'services' => [
            [
                'option_id' => 1,
                'title' => '📞 View Call & Alert History',
                'action_key' => 'view_call_logs',
                'description' => 'Check history of all inbound IVR calls and safety alerts sent to your vehicle QR tag.',
                'url' => $appBaseUrl . '/dashboard.php'
            ],
            [
                'option_id' => 2,
                'title' => '📱 Update Emergency & Alternate Phone Numbers',
                'action_key' => 'update_contacts',
                'description' => 'Update your primary, alternate, or emergency contacts for receiving calls.',
                'url' => $appBaseUrl . '/profile.php'
            ],
            [
                'option_id' => 3,
                'title' => '🔕 Temporary Mute / Pause Public Alerts',
                'action_key' => 'mute_alerts',
                'description' => 'Temporarily pause public call and WhatsApp notifications for your vehicle.',
                'status' => 'active'
            ],
            [
                'option_id' => 4,
                'title' => '🏷️ Download High-Res Replacement QR Tag',
                'action_key' => 'download_qr_tag',
                'description' => 'Get a print-ready PDF or high-resolution PNG image of your vehicle QR tag.',
                'url' => $appBaseUrl . '/qr_stream.php?code=' . urlencode($codeNumber) . '&download=1'
            ],
            [
                'option_id' => 5,
                'title' => '🛠️ Search Nearest Garages & Service Centers',
                'action_key' => 'nearest_garages',
                'description' => 'Locate nearby emergency repair shops, multi-brand workshops, and authorized service centers.',
                'url' => $appBaseUrl . '/scan.php?code=' . urlencode($codeNumber) . '&view=garages'
            ]
        ]
    ];
} else {
    // PUBLIC VISITOR ROLE MENU & SERVICES LIST
    $menuData = [
        'user_role' => 'public_visitor',
        'is_owner' => false,
        'contact_phone' => $cleanSenderPhone,
        'code_number' => $codeNumber,
        'vehicle_title' => $carTitle,
        'welcome_message' => "Hello! You are contacting Vehicle Sampark regarding {$carTitle} (QR: {$codeNumber}). Please select a service or alert reason below:",
        'menu_header' => "🚨 Public Safety & Emergency Contact Options:",
        'services' => [
            [
                'option_id' => 1,
                'title' => '💬 Send WhatsApp Safety Alert (8 Options)',
                'action_key' => 'whatsapp_safety_options',
                'description' => 'Send pre-formatted safety alerts (Blocking Way, Lights ON, Key Inside, Unlocked Door, Flat Tyre, Leakage, Towing, Breakdown).',
                'url' => $appBaseUrl . '/scan.php?code=' . urlencode($codeNumber) . '&view=whatsapp_options',
                'safety_options' => [
                    '1. Vehicle Blocking Driveway / Passage',
                    '2. Vehicle Lights ON / Battery Draining',
                    '3. Key or Valuables Left Inside',
                    '4. Window or Door Unlocked / Open',
                    '5. Flat Tyre / Low Air Pressure',
                    '6. Oil or Liquid Leakage Noticed',
                    '7. Towing Warning / Invalid Parking',
                    '8. Emergency Breakdown / Assistance'
                ]
            ],
            [
                'option_id' => 2,
                'title' => '📞 Emergency Masked Call to Owner',
                'action_key' => 'call_owner_ivr',
                'description' => 'Connect directly to vehicle owner via Inbound Masked IVR Hotline line 7971123254.',
                'ivr_hotline' => '7971123254',
                'url' => $appBaseUrl . '/scan.php?code=' . urlencode($codeNumber)
            ],
            [
                'option_id' => 3,
                'title' => '🛠️ Search Nearest Garages & Service Centers',
                'action_key' => 'nearest_garages',
                'description' => 'Find emergency auto repair workshops, tyre centers, and tow trucks nearby with Google Maps navigation.',
                'url' => $appBaseUrl . '/scan.php?code=' . urlencode($codeNumber) . '&view=garages'
            ]
        ]
    ];
}

echo json_encode([
    'success' => true,
    'data' => $menuData
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
