<?php
// app/Views/scan/whatsapp_options.php - Select Safety Alert Reason for WhatsApp Chat
include __DIR__ . '/../layouts/header.php';

$cleanOwnerMobile = $ownerDetails['clean_whatsapp_number'] ?: ($ownerDetails['clean_owner_mobile'] ?: '9723914037');
$carTitle = trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}");
if (empty($carTitle)) {
    $carTitle = "Vehicle (" . htmlspecialchars($codeNumber) . ")";
}

// 8 Emergency Safety Alert Options
$alertOptions = [
    [
        'id' => 1,
        'icon' => 'fa-car-side',
        'color' => '#0284c7',
        'bg' => '#f0f9ff',
        'border' => '#bae6fd',
        'title' => 'Vehicle Blocking Driveway / Passage',
        'description' => 'Notify owner that their vehicle is blocking the driveway, gate, or road passage.',
        'msg_reason' => '🚗 Vehicle Blocking Driveway / Passage. Please move your vehicle.'
    ],
    [
        'id' => 2,
        'icon' => 'fa-lightbulb',
        'color' => '#d97706',
        'bg' => '#fffbeb',
        'border' => '#fde68a',
        'title' => 'Vehicle Lights ON / Battery Draining',
        'description' => 'Alert owner that headlights or hazard warning lights are left ON.',
        'msg_reason' => '🚨 Vehicle Headlights / Hazard Lights are left ON. Battery may drain.'
    ],
    [
        'id' => 3,
        'icon' => 'fa-key',
        'color' => '#059669',
        'bg' => '#ecfdf5',
        'border' => '#a7f3d0',
        'title' => 'Key or Valuables Left Inside',
        'description' => 'Inform owner that keys, wallet, or valuables are visible inside vehicle.',
        'msg_reason' => '🔑 Vehicle Key or valuable items noticed inside your vehicle.'
    ],
    [
        'id' => 4,
        'icon' => 'fa-door-open',
        'color' => '#dc2626',
        'bg' => '#fef2f2',
        'border' => '#fecaca',
        'title' => 'Window or Door Unlocked / Open',
        'description' => 'Warn owner that window glass or vehicle door is left open/unlocked.',
        'msg_reason' => '🚪 Vehicle Window glass or door is left open / unlocked.'
    ],
    [
        'id' => 5,
        'icon' => 'fa-compact-disc',
        'color' => '#7c3aed',
        'bg' => '#f5f3ff',
        'border' => '#ddd6fe',
        'title' => 'Flat Tyre / Low Air Pressure',
        'description' => 'Notify owner about flat tyre or low air pressure on one of the wheels.',
        'msg_reason' => '🛞 Flat Tyre or low air pressure noticed on your vehicle.'
    ],
    [
        'id' => 6,
        'icon' => 'fa-droplet',
        'color' => '#2563eb',
        'bg' => '#eff6ff',
        'border' => '#bfdbfe',
        'title' => 'Oil or Liquid Leakage Noticed',
        'description' => 'Alert owner about fluid, oil, or coolant leaking underneath vehicle.',
        'msg_reason' => '💧 Oil / Fluid leakage noticed underneath your parked vehicle.'
    ],
    [
        'id' => 7,
        'icon' => 'fa-truck-towed',
        'color' => '#ea580c',
        'bg' => '#fff7ed',
        'border' => '#ffedd5',
        'title' => 'Towing Warning / Invalid Parking',
        'description' => 'Warn owner about traffic police towing or no-parking zone alert.',
        'msg_reason' => '🚔 Traffic Police Towing Warning / Invalid Parking Spot Alert.'
    ],
    [
        'id' => 8,
        'icon' => 'fa-triangle-exclamation',
        'color' => '#b91c1c',
        'bg' => '#fef2f2',
        'border' => '#f87171',
        'title' => 'Emergency Breakdown / Assistance',
        'description' => 'Urgent message requesting vehicle owner for immediate breakdown assistance.',
        'msg_reason' => '⚠️ Emergency Breakdown / Urgent Assistance requested for your vehicle.'
    ]
];
?>

<div class="public-form-container" style="max-width: 640px; margin: 1.5rem auto; padding: 0 0.75rem;">
    <!-- BACK TO PORTAL LINK -->
    <div style="margin-bottom: 1rem;">
        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" style="display: inline-flex; align-items: center; gap: 0.4rem; color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.88rem; background: #f0f9ff; padding: 0.45rem 0.85rem; border-radius: 10px; border: 1px solid #bae6fd;">
            <i class="fa-solid fa-arrow-left"></i> Back to Vehicle Contact Portal
        </a>
    </div>

    <div class="content-card" style="padding: 1.5rem 1.25rem;">
        <!-- HEADER -->
        <div style="text-align: center; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.25rem;">
            <div style="margin-bottom: 0.4rem;">
                <span class="badge badge-submitted" style="background: #25d366; color: #ffffff; padding: 0.35rem 0.85rem; font-size: 0.82rem;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1rem;"></i> WhatsApp Safety Alert
                </span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Select Alert Reason</h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 0;">
                Choose an alert option below to start a pre-formatted WhatsApp chat with the vehicle owner securely.
            </p>

            <div style="background: #f8fafc; padding: 0.75rem 1rem; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 1rem; text-align: center;">
                <div style="font-size: 0.82rem; color: var(--text-muted);">Vehicle QR Tag: <strong style="color: #0284c7; font-family: monospace;"><?= htmlspecialchars($codeNumber) ?></strong></div>
                <?php if (!empty($carTitle)): ?>
                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--accent-orange); margin-top: 0.2rem;">
                        <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars($carTitle) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 8 OPTIONS GRID -->
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
            <?php foreach ($alertOptions as $opt): 
                $waText = "Hello! I am contacting you regarding your vehicle {$carTitle} (QR Tag: {$codeNumber}) registered on Vehicle Sampark.\n\n*Reason:* {$opt['msg_reason']}\n\nPlease check when possible. Thank you!";
                $waUrl = "https://api.whatsapp.com/send?phone=91{$cleanOwnerMobile}&text=" . urlencode($waText);
            ?>
                <a href="<?= $waUrl ?>" target="_blank" style="text-decoration: none; color: inherit; display: block;">
                    <div style="background: <?= $opt['bg'] ?>; border: 1.5px solid <?= $opt['border'] ?>; border-radius: 16px; padding: 1rem 1.15rem; transition: transform 0.2s ease, box-shadow 0.2s ease; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div style="display: flex; align-items: flex-start; gap: 0.85rem;">
                            <div style="width: 42px; height: 42px; background: #ffffff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: <?= $opt['color'] ?>; font-size: 1.25rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                                <i class="fa-solid <?= $opt['icon'] ?>"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 0.98rem; font-weight: 800; color: #0f172a; margin: 0 0 0.2rem 0; line-height: 1.3;">
                                    <?= htmlspecialchars($opt['title']) ?>
                                </h3>
                                <p style="font-size: 0.8rem; color: #475569; margin: 0; line-height: 1.35;">
                                    <?= htmlspecialchars($opt['description']) ?>
                                </p>
                            </div>
                        </div>

                        <div style="background: #25d366; color: #ffffff; padding: 0.55rem 0.85rem; border-radius: 10px; font-weight: 800; font-size: 0.8rem; white-space: nowrap; flex-shrink: 0; display: flex; align-items: center; gap: 0.35rem; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1rem;"></i> Send
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 1.5rem; text-align: center;">
            <a href="scan.php?code=<?= urlencode($codeNumber) ?>" class="btn btn-outline" style="width: 100%; padding: 0.85rem; font-weight: 700;">
                <i class="fa-solid fa-arrow-left"></i> Cancel & Return to Call Options
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
