<?php
// app/Views/scan/portal.php - Responsive Public Scanner Contact & Owner Registration View
include __DIR__ . '/../layouts/header.php';
?>

<style>
/* PREVENT MOBILE VIRTUAL KEYBOARD OVERLAP & ZERO-LAG NATIVE SCROLLING */
.public-form-container {
    max-width: 580px;
    margin: 1rem auto 3rem auto;
    padding: 0 0.5rem;
    box-sizing: border-box;
    transition: padding-bottom 0.2s ease;
}

@media (max-width: 640px) {
    .public-form-container {
        padding-bottom: 60vh !important; /* Bottom space so lowest inputs scroll comfortably above soft keyboard */
    }
}

.registered-tag-preview {
    background: #ffffff;
    border: 2px solid #a7f3d0;
    border-radius: 20px;
    padding: 1.25rem;
    margin: 1.25rem 0;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.12);
    text-align: center;
}

/* NATIVE BROWSER SCROLL-MARGIN FOR 60FPS ZERO-LAG ALIGNMENT BELOW HEADER */
.form-group, input.form-control {
    scroll-margin-top: 95px !important;
    padding: 0.5rem;
    border-radius: 14px;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.form-group.field-focused {
    background: #f0f9ff !important;
    border: 2px solid #0284c7 !important;
    box-shadow: 0 8px 20px rgba(2, 132, 199, 0.2) !important;
}

.form-group.field-focused label {
    color: #0369a1 !important;
    font-weight: 800 !important;
}
</style>

<div class="public-form-container" id="publicFormContainer">
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
        <!-- REGISTRATION SUCCESS SCREEN WITH TAG DISPLAY & NO TEST PUBLIC BUTTON -->
        <div class="content-card" style="padding: 2rem 1.25rem; text-align: center;">
            <div style="width: 72px; height: 72px; background: #ecfdf5; color: #10b981; border: 2.5px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.4rem; margin: 0 auto 1.25rem auto; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2);">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            
            <h1 style="font-size: 1.65rem; color: var(--text-main); font-weight: 800; margin-bottom: 0.5rem;">
                QR Code Successfully Registered!
            </h1>
            
            <div style="background: #f0f9ff; border: 1.5px solid #bae6fd; padding: 1rem; border-radius: 16px; margin: 1rem 0; text-align: center;">
                <p style="color: #0369a1; font-size: 0.95rem; margin: 0; line-height: 1.5; font-weight: 600;">
                    Your QR Code <strong style="color: #0284c7; font-family: monospace; font-size: 1.05rem;"><?= htmlspecialchars($codeNumber) ?></strong> is registered with vehicle <strong style="color: #d97706;"><?= htmlspecialchars(trim("{$_POST['car_name']} {$_POST['car_model']}")) ?></strong> and number plate <strong style="color: #10b981; font-family: monospace; font-size: 1.05rem;"><?= htmlspecialchars(strtoupper($_POST['car_number'])) ?></strong>.
                </p>
            </div>

            <!-- REGISTERED VEHICLE QR TAG DISPLAY ON SCREEN -->
            <div class="registered-tag-preview">
                <div style="font-size: 0.85rem; font-weight: 800; color: #047857; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-qrcode"></i> Your Registered Vehicle QR Tag
                </div>
                <div style="background: #ffffff; padding: 1rem; border-radius: 16px; border: 1.5px dashed #10b981; display: inline-block;">
                    <img src="qr_stream.php?code=<?= urlencode($codeNumber) ?>" alt="Registered QR Code" style="width: 170px; height: 170px; display: block; margin: 0 auto; border-radius: 8px;">
                    <div style="font-family: monospace; font-weight: 800; color: #0284c7; font-size: 1.1rem; margin-top: 0.5rem;">
                        <?= htmlspecialchars($codeNumber) ?>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 800; color: #d97706; margin-top: 0.2rem;">
                        <?= htmlspecialchars(strtoupper($_POST['car_number'])) ?>
                    </div>
                </div>
            </div>

            <!-- ACTION OPTIONS AFTER REGISTRATION -->
            <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1.5rem;">
                <a href="scan.php?code=<?= urlencode($codeNumber) ?>&view=whatsapp_options" class="btn" style="width: 100%; padding: 1.05rem; font-size: 1.05rem; background: #25d366; color: #ffffff; border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.6rem; text-decoration: none; box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> WhatsApp Message Options
                </a>
                
                <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST" style="margin: 0;">
                    <button type="submit" name="action_call_owner" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.6rem; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                        <i class="fa-solid fa-phone-volume" style="font-size: 1.1rem;"></i> Emergency Call (Masked IVR Hotline)
                    </button>
                </form>
            </div>
        </div>

    <?php elseif ($qrData['status'] === 'submitted'): ?>
        <!-- SUBMITTED PUBLIC CONTACT VIEW (WHATSAPP vs EMERGENCY CALL) -->
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
                <!-- CALL INITIATED CONFIRMATION -->
                <div style="background: #ecfdf5; border: 2px solid #a7f3d0; padding: 1.5rem 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; text-align: left;">
                    <div style="font-weight: 800; color: #065f46; font-size: 1.15rem; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.4rem;"></i> Inbound Masked Call Initiated!
                    </div>
                    <p style="font-size: 0.88rem; color: #047857; margin-bottom: 1rem; line-height: 1.5;">
                        Your call request has been processed. The IVR Hotline (<strong>7971123254</strong>) is now routing the call to the vehicle owner securely.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 1rem;">
                        <a href="tel:7971123254" class="btn btn-primary" style="font-size: 0.95rem; font-weight: 700; background: linear-gradient(135deg, #10b981, #059669); text-decoration: none; justify-content: center; padding: 0.9rem;">
                            <i class="fa-solid fa-phone"></i> Dial Inbound IVR Line (7971123254) Directly
                        </a>
                        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" class="btn btn-outline" style="font-size: 0.88rem; font-weight: 700; color: #047857; border-color: #a7f3d0; text-decoration: none; justify-content: center;">
                            <i class="fa-solid fa-rotate-left"></i> Make Another Call Request
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- 2 MAIN OPTIONS: WHATSAPP MESSAGE & EMERGENCY CALL -->
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; padding: 1.25rem; border-radius: 16px; margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                    
                    <!-- OPTION 1: WHATSAPP MESSAGE BUTTON -->
                    <a href="scan.php?code=<?= urlencode($codeNumber) ?>&view=whatsapp_options" class="btn" style="width: 100%; padding: 1.15rem 1rem; font-size: 1.1rem; background: #25d366; color: #ffffff; border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.65rem; text-decoration: none; box-shadow: 0 8px 22px rgba(37, 211, 102, 0.35);">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.4rem;"></i> 💬 WhatsApp Message (8 Safety Alerts)
                    </a>

                    <!-- OPTION 2: EMERGENCY CALL BUTTON -->
                    <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST" style="margin: 0;">
                        <button type="submit" name="action_call_owner" class="btn btn-primary btn-glow" style="width: 100%; padding: 1.15rem 1rem; font-size: 1.1rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.65rem; border: none; cursor: pointer; box-shadow: 0 8px 22px rgba(16, 185, 129, 0.35);">
                            <i class="fa-solid fa-phone-volume" style="font-size: 1.3rem;"></i> 📞 Emergency Call (Inbound IVR Hotline)
                        </button>
                    </form>

                    <!-- DIRECT DIAL IVR SECONDARY LINK -->
                    <div style="text-align: center; margin-top: 0.2rem;">
                        <a href="tel:7971123254" style="font-size: 0.88rem; font-weight: 700; color: #0284c7; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-square-phone" style="font-size: 1rem;"></i> Direct Dial Inbound IVR Hotline (7971123254)
                        </a>
                    </div>

                    <!-- OPTION 3: NEAREST SERVICE CENTER & GARAGE BUTTON -->
                    <a href="scan.php?code=<?= urlencode($codeNumber) ?>&view=garages" class="btn" style="width: 100%; padding: 0.95rem; font-size: 0.98rem; background: linear-gradient(135deg, #0284c7, #0369a1); color: #ffffff; border-radius: 12px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.55rem; text-decoration: none; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.25);">
                        <i class="fa-solid fa-wrench" style="font-size: 1.1rem;"></i> 🛠️ Nearest Service Center & Garage
                    </a>
                </div>
            <?php endif; ?>

            <div style="background: #ecfdf5; padding: 0.85rem; border-radius: var(--radius-md); border: 1px solid #a7f3d0; text-align: center;">
                <p style="font-size: 0.82rem; color: #047857; margin: 0; line-height: 1.45; font-weight: 600;">
                    <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> <strong>100% Privacy Protection Active:</strong> Mobile numbers are kept secure and masked during WhatsApp and IVR Hotline calls (<strong>7971123254</strong>).
                </p>
            </div>
        </div>

    <?php else: ?>
        <!-- VEHICLE OWNER REGISTRATION FORM WITH ALTERNATE PHONE & KEYBOARD RESPONSIVE FIX -->
        <div class="content-card" style="padding: 1.75rem 1.25rem;">
            <div style="text-align: center; margin-bottom: 1.5rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color);">
                <div style="margin-bottom: 0.4rem;">
                    <span class="code-badge"><?= htmlspecialchars($codeNumber) ?></span>
                </div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">Vehicle Owner Registration</h1>
                <p style="color: var(--text-muted); font-size: 0.88rem;">
                    Fill out your vehicle details to register this QR Tag. Once registered, public scanners can contact you securely.
                </p>
            </div>

            <form action="scan.php?code=<?= urlencode($codeNumber) ?>" method="POST" id="ownerRegisterForm">
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Primary Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="mobile_number" class="form-control" placeholder="e.g. 98765 43210" required>
                </div>

                <!-- REQUIRED FIELD: ALTERNATE PHONE NUMBER -->
                <div class="form-group">
                    <label class="form-label">Alternate Phone Number <span class="required">*</span></label>
                    <input type="tel" name="alternate_phone" class="form-control" placeholder="e.g. 98765 43211 (Alternate contact)" required>
                    <span class="form-help">Secondary phone number to receive alerts if primary phone is unreachable.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="emergency_mobile_number" class="form-control" placeholder="e.g. 98765 43212 (Family/Emergency)" required>
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
                    <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.95rem; font-size: 1rem; font-weight: 800;">
                        <i class="fa-solid fa-id-card"></i> Register Vehicle Owner & Activate QR Tag
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
// SILKY SMOOTH ZERO-LAG NATIVE SCROLL ENGINE
document.addEventListener("DOMContentLoaded", function() {
    const formContainer = document.getElementById("publicFormContainer");
    const formGroups = document.querySelectorAll("#ownerRegisterForm .form-group");
    const inputs = document.querySelectorAll("#ownerRegisterForm input");

    let scrollTimer = null;

    // Instantly cancel JS auto-scroll if user touches or swipes the screen
    function cancelPendingScroll() {
        if (scrollTimer) {
            clearTimeout(scrollTimer);
            scrollTimer = null;
        }
    }

    window.addEventListener("touchstart", cancelPendingScroll, { passive: true });
    window.addEventListener("touchmove", cancelPendingScroll, { passive: true });
    window.addEventListener("wheel", cancelPendingScroll, { passive: true });

    function smoothScrollToInput(inputEl) {
        if (!inputEl) return;
        cancelPendingScroll();

        const parentGroup = inputEl.closest(".form-group") || inputEl;
        
        formGroups.forEach(fg => fg.classList.remove("field-focused"));
        if (parentGroup.classList) {
            parentGroup.classList.add("field-focused");
        }

        if (formContainer) {
            formContainer.style.paddingBottom = "60vh";
        }

        // Single smooth scroll call using native CSS scroll-margin-top (95px clearance)
        scrollTimer = setTimeout(function() {
            parentGroup.scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
        }, 220);
    }

    inputs.forEach(function(input) {
        input.addEventListener("focus", function() {
            smoothScrollToInput(input);
        });

        input.addEventListener("blur", function() {
            const parentGroup = input.closest(".form-group");
            if (parentGroup) {
                parentGroup.classList.remove("field-focused");
            }
            
            setTimeout(function() {
                if (document.activeElement && document.activeElement.tagName === "INPUT") {
                    return;
                }
                if (formContainer) {
                    formContainer.style.paddingBottom = "2rem";
                }
            }, 300);
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
