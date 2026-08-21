<?php
// app/Views/scan/portal.php - Responsive Public Scanner Contact & Owner Registration View
include __DIR__ . '/../layouts/header.php';
?>

<div class="public-form-container" style="max-width: 580px; margin: 1.5rem auto; padding: 0 0.5rem;">
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
                <?php if (!empty($ownerDetails['car_number']) || !empty($ownerDetails['car_name'])): ?>
                    <div style="font-size: 1.05rem; font-weight: 800; color: var(--accent-orange);">
                        <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}")) ?>
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
                        Your call request has been automatically processed. The IVR Hotline (7971123254) is now ringing the vehicle owner's phone securely.
                    </p>

                    <div style="background: #ffffff; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #bbf7d0; margin-bottom: 1rem;">
                        <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 0.2rem;">Connected Agent (Vehicle Owner):</div>
                        <div style="font-size: 1.05rem; font-weight: 800; color: #0284c7; font-family: monospace;">
                            <i class="fa-solid fa-user-shield" style="color: #0284c7;"></i> Registered Owner Connected via IVR (7971123254)
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1rem;">
                        <a href="tel:7971123254" class="btn btn-primary" style="font-size: 0.95rem; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); text-decoration: none; justify-content: center;">
                            <i class="fa-solid fa-phone"></i> Dial IVR Hotline (7971123254) Directly
                        </a>
                        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" class="btn btn-outline" style="font-size: 0.88rem; font-weight: 700; color: #047857; border-color: #a7f3d0; text-decoration: none; justify-content: center;">
                            <i class="fa-solid fa-rotate-left"></i> Make Another Call Request
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- 1-CLICK INSTANT CALL OWNER CONTROLS -->
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 1.25rem; border-radius: 16px; margin-bottom: 1.25rem;">
                    <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST" style="margin-bottom: 0.75rem;">
                        <button type="submit" name="action_call_owner" class="btn btn-primary btn-glow" style="width: 100%; padding: 1.25rem; font-size: 1.2rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: var(--radius-lg); font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.75rem; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);">
                            <i class="fa-solid fa-phone-volume" style="font-size: 1.4rem;"></i> Call the Owner
                        </button>
                    </form>

                    <div style="text-align: center; margin-top: 0.5rem;">
                        <a href="tel:7971123254" onclick="triggerCallBridge('<?= htmlspecialchars($codeNumber) ?>')" style="font-size: 0.85rem; font-weight: 700; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-square-phone"></i> Direct Dial IVR Hotline (7971123254)
                        </a>
                    </div>
                </div>
                <script>
                function triggerCallBridge(code) {
                    fetch('api/make_call.php?code=' + encodeURIComponent(code), { method: 'GET', cache: 'no-cache' }).catch(function(){});
                }
                </script>
            <?php endif; ?>

            <div style="background: #ecfdf5; padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid #a7f3d0; text-align: center;">
                <p style="font-size: 0.82rem; color: #047857; margin: 0; line-height: 1.45; font-weight: 600;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> <strong>Inbound Masked Calling Active:</strong> Direct Dial-in via <strong>7971123254</strong>. Your personal mobile number stays 100% hidden and protected.
                </p>
            </div>
        </div>

    <?php else: ?>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
