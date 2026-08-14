<?php
// profile.php - Vehicle Sampark Admin Profile, Change Password & 2FA Setup

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pageTitle = 'Admin Profile & Security - Vehicle Sampark';
$adminId = $_SESSION['admin_id'];

$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

$successMsg = '';
$errorMsg = '';

// Handle Profile Updates, Password Changes, and 2FA Configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'update_info') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($fullName) || empty($email)) {
            $errorMsg = 'Full name and email are required.';
        } else {
            $upStmt = $pdo->prepare("UPDATE admins SET full_name = ?, email = ? WHERE id = ?");
            $upStmt->execute([$fullName, $email, $adminId]);
            $_SESSION['admin_fullname'] = $fullName;
            $_SESSION['admin_email'] = $email;
            $admin['full_name'] = $fullName;
            $admin['email'] = $email;
            $successMsg = 'Profile information updated successfully!';
        }
    } elseif ($formType === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPass, $admin['password_hash'])) {
            $errorMsg = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errorMsg = 'New password must be at least 6 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errorMsg = 'New passwords do not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $upStmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $upStmt->execute([$newHash, $adminId]);
            $admin['password_hash'] = $newHash;
            $successMsg = 'Password changed successfully!';
        }
    } elseif ($formType === 'toggle_2fa') {
        $enable2fa = isset($_POST['enable_2fa']) ? 1 : 0;
        $upStmt = $pdo->prepare("UPDATE admins SET two_factor_enabled = ? WHERE id = ?");
        $upStmt->execute([$enable2fa, $adminId]);
        $admin['two_factor_enabled'] = $enable2fa;
        $successMsg = $enable2fa ? 'Two-Factor Authentication (2FA) is now ENABLED.' : 'Two-Factor Authentication (2FA) is now DISABLED.';
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--text-main);">
            <i class="fa-solid fa-user-gear" style="color: var(--primary);"></i> Admin Security & Profile Settings
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Manage account details, update admin password, and configure Two-Factor Authentication (2FA)</p>
    </div>

    <?php if ($successMsg): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 0.9rem 1.15rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.9rem 1.15rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; font-weight: 600; display: flex; align-items: center; gap: 0.6rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        <!-- Card 1: Profile Details -->
        <div class="content-card">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.65rem;">
                <i class="fa-solid fa-id-badge" style="color: var(--accent-orange);"></i> Admin Details
            </h3>
            <form action="profile.php" method="POST">
                <input type="hidden" name="form_type" value="update_info">
                
                <div class="form-group">
                    <label class="form-label">Username (Read-only)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" readonly style="background: #f1f5f9 !important;">
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                </div>

                <button type="submit" class="btn btn-secondary" style="width: 100%;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Details
                </button>
            </form>
        </div>

        <!-- Card 2: Change Password -->
        <div class="content-card">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.65rem;">
                <i class="fa-solid fa-key" style="color: var(--primary);"></i> Change Password
            </h3>
            <form action="profile.php" method="POST">
                <input type="hidden" name="form_type" value="change_password">

                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-glow" style="width: 100%;">
                    <i class="fa-solid fa-lock"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- Card 3: Two-Factor Authentication (2FA) Security System -->
    <div class="content-card" style="margin-top: 1.5rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.65rem;">
            <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Two-Factor Authentication (2FA) Protection
        </h3>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; background: #f8fafc; padding: 1.25rem; border-radius: var(--radius-lg); border: 1px solid #e2e8f0;">
            <div>
                <h4 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">
                    Status: <?= $admin['two_factor_enabled'] ? '<span style="color: var(--primary);"><i class="fa-solid fa-circle-check"></i> Enabled</span>' : '<span style="color: var(--text-muted);"><i class="fa-solid fa-circle-xmark"></i> Disabled</span>' ?>
                </h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                    When enabled, signing in requires both your password and a 6-digit Authenticator code.
                </p>
            </div>

            <form action="profile.php" method="POST">
                <input type="hidden" name="form_type" value="toggle_2fa">
                <?php if ($admin['two_factor_enabled']): ?>
                    <button type="submit" class="btn btn-outline" style="color: var(--accent-rose); border-color: #fecaca;">
                        <i class="fa-solid fa-power-off"></i> Disable 2FA
                    </button>
                <?php else: ?>
                    <input type="hidden" name="enable_2fa" value="1">
                    <button type="submit" class="btn btn-primary btn-glow">
                        <i class="fa-solid fa-shield-check"></i> Enable 2FA Security
                    </button>
                <?php endif; ?>
            </form>
        </div>

        <div style="margin-top: 1.25rem; background: #fff7ed; border: 1px solid #ffedd5; padding: 1rem; border-radius: var(--radius-md);">
            <div style="font-size: 0.88rem; font-weight: 700; color: #c2410c; margin-bottom: 0.35rem;">
                <i class="fa-solid fa-qrcode"></i> Authenticator Key & Secret Pin
            </div>
            <p style="font-size: 0.82rem; color: #9a3412; margin: 0;">
                Secret Authenticator Key: <code style="background: #ffedd5; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-family: monospace; font-size: 0.9rem;"><?= htmlspecialchars($admin['two_factor_secret'] ?: 'VSPK2FASECRET123') ?></code> &bull; Demo Passcode: <code style="background: #ffedd5; padding: 2px 8px; border-radius: 4px; font-weight: bold;">123456</code>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/header.php'; ?>
