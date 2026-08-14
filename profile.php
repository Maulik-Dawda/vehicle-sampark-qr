<?php
// profile.php - Vehicle Sampark Modern Enterprise Profile & Security Settings

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pageTitle = 'Profile & Security Settings - Vehicle Sampark';
$adminId = $_SESSION['admin_id'] ?? 1;

// Fetch current admin details
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

// Fallback defaults if null
if (!$admin) {
    $admin = [
        'id' => 1,
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'full_name' => $_SESSION['admin_fullname'] ?? 'System Administrator',
        'email' => $_SESSION['admin_email'] ?? 'admin@vehiclesampark.com',
        'password_hash' => '',
        'two_factor_enabled' => 0,
        'two_factor_secret' => 'VSPK2FASECRET123'
    ];
}

$successMsg = '';
$errorMsg = '';

// Handle Profile Updates, Password Changes, and 2FA Configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'update_info') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($fullName) || empty($email)) {
            $errorMsg = 'Full name and email address are required.';
        } else {
            try {
                $upStmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
                $upStmt->execute([$fullName, $email, $adminId]);
                $_SESSION['admin_fullname'] = $fullName;
                $_SESSION['admin_email'] = $email;
                $admin['full_name'] = $fullName;
                $admin['email'] = $email;
                $successMsg = 'Profile details updated successfully!';
            } catch (Exception $e) {
                $errorMsg = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    } elseif ($formType === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!empty($admin['password_hash']) && !password_verify($currentPass, $admin['password_hash'])) {
            $errorMsg = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errorMsg = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errorMsg = 'New passwords do not match.';
        } else {
            try {
                $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                $upStmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $upStmt->execute([$newHash, $adminId]);
                $admin['password_hash'] = $newHash;
                $successMsg = 'Security password updated successfully!';
            } catch (Exception $e) {
                $errorMsg = 'Failed to update password: ' . $e->getMessage();
            }
        }
    } elseif ($formType === 'toggle_2fa') {
        $enable2fa = isset($_POST['enable_2fa']) ? 1 : 0;
        try {
            $upStmt = $pdo->prepare("UPDATE admins SET two_factor_enabled = ? WHERE id = ?");
            $upStmt->execute([$enable2fa, $adminId]);
            $admin['two_factor_enabled'] = $enable2fa;
            $successMsg = $enable2fa ? 'Two-Factor Authentication (2FA) is now ENABLED.' : 'Two-Factor Authentication (2FA) is now DISABLED.';
        } catch (Exception $e) {
            $errorMsg = 'Failed to update 2FA status: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 1040px; margin: 0 auto; padding-bottom: 3rem;">

    <!-- HERO PROFILE HEADER BANNER -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 20px; padding: 2.25rem 2rem; margin-bottom: 2rem; color: #ffffff; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15); border: 1px solid rgba(255, 255, 255, 0.08); position: relative; overflow: hidden;">
        <div style="position: absolute; right: -50px; top: -50px; width: 220px; height: 220px; background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(0,0,0,0) 70%); pointer-events: none;"></div>
        
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem; position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="width: 76px; height: 76px; border-radius: 50%; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-family: 'Outfit', sans-serif; font-size: 2.2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35); border: 3px solid rgba(255, 255, 255, 0.2);">
                    <?= strtoupper(substr($admin['username'], 0, 1)) ?>
                </div>
                <div>
                    <h1 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; margin: 0 0 0.35rem 0; color: #ffffff; display: flex; align-items: center; gap: 0.6rem;">
                        <?= htmlspecialchars($admin['full_name']) ?>
                        <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(52, 211, 153, 0.3); text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-shield-check"></i> Super Admin
                        </span>
                    </h1>
                    <div style="color: #94a3b8; font-size: 0.92rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-user-tag" style="color: #10b981;"></i> @<?= htmlspecialchars($admin['username']) ?></span>
                        <span>&bull;</span>
                        <span><i class="fa-solid fa-envelope" style="color: #38bdf8;"></i> <?= htmlspecialchars($admin['email']) ?></span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" onclick="openLogoutModal(event)" class="btn btn-outline" style="color: #f43f5e; border-color: rgba(244, 63, 94, 0.4); background: rgba(244, 63, 94, 0.1); font-weight: 600; padding: 0.65rem 1.15rem;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout Session
                </button>
            </div>
        </div>
    </div>

    <!-- ALERT FEEDBACK NOTIFICATIONS -->
    <?php if ($successMsg): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.75rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
            <i class="fa-solid fa-circle-check" style="font-size: 1.25rem; color: #10b981;"></i>
            <div><?= htmlspecialchars($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.75rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; box-shadow: 0 4px 12px rgba(244, 63, 94, 0.1);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.25rem; color: #f43f5e;"></i>
            <div><?= htmlspecialchars($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <!-- MAIN SETTINGS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.75rem;">
        
        <!-- CARD 1: PROFILE DETAILS -->
        <div class="content-card" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.85rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">Profile Information</h3>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0;">Update your personal admin account credentials</p>
                </div>
            </div>

            <form action="admin-qr-profile" method="POST">
                <input type="hidden" name="form_type" value="update_info">
                
                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">
                        Username <span style="color: #94a3b8; font-weight: normal;">(System Assigned)</span>
                    </label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" readonly style="background: #f8fafc !important; color: #64748b; padding-left: 2.5rem; font-weight: 600;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">Full Name</label>
                    <div style="position: relative;">
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required style="padding-left: 2.5rem;">
                        <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #10b981;"></i>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">Email Address</label>
                    <div style="position: relative;">
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required style="padding-left: 2.5rem;">
                        <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #38bdf8;"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.8rem; font-weight: 600; border-radius: 10px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                </button>
            </form>
        </div>

        <!-- CARD 2: CHANGE SECURITY PASSWORD -->
        <div class="content-card" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.85rem;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(56, 189, 248, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div>
                    <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">Security & Password</h3>
                    <p style="font-size: 0.82rem; color: #64748b; margin: 0;">Change your admin access password</p>
                </div>
            </div>

            <form action="admin-qr-profile" method="POST">
                <input type="hidden" name="form_type" value="change_password">

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">Current Password</label>
                    <div style="position: relative;">
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••" required style="padding-left: 2.5rem; padding-right: 2.5rem;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                        <button type="button" onclick="togglePasswordVisibility('current_password', 'eye_current')" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eye_current"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.15rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required style="padding-left: 2.5rem; padding-right: 2.5rem;">
                        <i class="fa-solid fa-shield" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #10b981;"></i>
                        <button type="button" onclick="togglePasswordVisibility('new_password', 'eye_new')" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eye_new"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 0.4rem; display: block;">Confirm New Password</label>
                    <div style="position: relative;">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required style="padding-left: 2.5rem; padding-right: 2.5rem;">
                        <i class="fa-solid fa-check-double" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #10b981;"></i>
                        <button type="button" onclick="togglePasswordVisibility('confirm_password', 'eye_confirm')" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer;">
                            <i class="fa-solid fa-eye" id="eye_confirm"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: 0.8rem; font-weight: 600; border-radius: 10px;">
                    <i class="fa-solid fa-arrows-rotate"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- CARD 3: TWO-FACTOR AUTHENTICATION (2FA) & SYSTEM PREFERENCES -->
    <div class="content-card" style="background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 1.75rem; margin-top: 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.85rem;">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">Two-Factor Authentication (2FA) Protection</h3>
                <p style="font-size: 0.82rem; color: #64748b; margin: 0;">Add an extra layer of security to your admin login portal</p>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem; background: #f8fafc; padding: 1.35rem 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= !empty($admin['two_factor_enabled']) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(148, 163, 184, 0.15)' ?>; color: <?= !empty($admin['two_factor_enabled']) ? '#10b981' : '#64748b' ?>; display: flex; align-items: center; justify-content: center; font-size: 1.35rem;">
                    <i class="fa-solid <?= !empty($admin['two_factor_enabled']) ? 'fa-shield-check' : 'fa-shield-slash' ?>"></i>
                </div>
                <div>
                    <h4 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin: 0 0 0.2rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        2FA Status: 
                        <?php if (!empty($admin['two_factor_enabled'])): ?>
                            <span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 0.6rem; vertical-align: middle;"></i> ENABLED</span>
                        <?php else: ?>
                            <span style="color: #64748b; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 0.6rem; vertical-align: middle;"></i> DISABLED</span>
                        <?php endif; ?>
                    </h4>
                    <p style="font-size: 0.88rem; color: #64748b; margin: 0;">
                        When enabled, signing into your account requires a 6-digit Authenticator code.
                    </p>
                </div>
            </div>

            <form action="admin-qr-profile" method="POST" style="margin: 0;">
                <input type="hidden" name="form_type" value="toggle_2fa">
                <?php if (!empty($admin['two_factor_enabled'])): ?>
                    <button type="submit" class="btn btn-outline" style="color: #f43f5e; border-color: #fecaca; background: #fff1f2; font-weight: 600; padding: 0.65rem 1.25rem;">
                        <i class="fa-solid fa-power-off"></i> Disable 2FA
                    </button>
                <?php else: ?>
                    <input type="hidden" name="enable_2fa" value="1">
                    <button type="submit" class="btn btn-primary btn-glow" style="font-weight: 600; padding: 0.65rem 1.25rem;">
                        <i class="fa-solid fa-shield-check"></i> Enable 2FA Protection
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <div style="margin-top: 1.25rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1.15rem; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.9rem; font-weight: 700; color: #166534; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-key" style="color: #10b981;"></i> Secret Authenticator Key:
                </div>
                <div style="font-size: 0.85rem; color: #15803d;">
                    Secret Pin: <code style="background: #dcfce7; color: #14532d; padding: 3px 10px; border-radius: 6px; font-weight: 700; font-family: monospace; font-size: 0.95rem; border: 1px solid #a7f3d0;"><?= htmlspecialchars($admin['two_factor_secret'] ?: 'VSPK2FASECRET123') ?></code>
                </div>
            </div>
            <div style="font-size: 0.85rem; color: #166534; background: #ffffff; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid #bbf7d0; font-weight: 600;">
                <i class="fa-solid fa-clock-rotate-left"></i> Demo Code: <strong style="color: #047857;">123456</strong>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
