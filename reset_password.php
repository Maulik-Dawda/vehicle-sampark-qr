<?php
// reset_password.php - Admin Password Reset Completion Page

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$token = sanitize($_GET['token'] ?? ($_SESSION['reset_token_active'] ?? ''));
$error = '';
$success = false;

if (empty($token)) {
    $error = 'Invalid or missing password reset token.';
} else {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE reset_token = ? AND reset_token_expires > CURRENT_TIMESTAMP LIMIT 1");
    $stmt->execute([$token]);
    $admin = $stmt->fetch();

    if (!$admin) {
        $error = 'Reset token is invalid or has expired. Please request a new one.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error && isset($admin)) {
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'Passwords do not match.';
    } else {
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $upStmt = $pdo->prepare("UPDATE admins SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $upStmt->execute([$newHash, $admin['id']]);

        unset($_SESSION['reset_token_active']);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Vehicle Sampark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body style="background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 430px;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" style="height: 60px; border-radius: var(--radius-md); margin-bottom: 0.75rem;" onerror="this.src='assets/images/logo-icon.svg'">
        <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #0f172a;">Set New Password</h1>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Create a strong password for your administrator account</p>
    </div>

    <div class="content-card" style="padding: 2rem; border-radius: var(--radius-xl); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);">
        <?php if ($success): ?>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: #ecfdf5; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1rem auto;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h3 style="color: var(--text-main); font-weight: 700;">Password Updated!</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem; margin-bottom: 1.5rem;">Your admin password has been successfully reset.</p>
                <a href="login.php" class="btn btn-primary btn-glow" style="width: 100%;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login Now
                </a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($admin)): ?>
                <form action="reset_password.php?token=<?= urlencode($token) ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem; margin-top: 0.5rem;">
                        <i class="fa-solid fa-shield-check"></i> Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="forgot_password.php" class="btn btn-secondary">Request New Reset Link</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
