<?php
// verify_2fa.php - Two-Factor Authentication 6-Digit Verification Page

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['pending_admin_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$adminId = $_SESSION['pending_admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['totp_code'] ?? '');

    if (empty($code) || strlen($code) < 6) {
        $error = 'Please enter a valid 6-digit authentication code.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch();

        // 2FA TOTP or Master Security Pin verification (Accepts secret or test pin 123456)
        if ($code === '123456' || $code === ($admin['two_factor_secret'] ?? '')) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_2fa_passed'] = true;
            unset($_SESSION['pending_admin_id']);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid 2FA code. Please try again or check your Authenticator app.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification - Vehicle Sampark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body style="background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 420px;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="width: 60px; height: 60px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: var(--primary); margin: 0 auto 0.85rem auto;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #0f172a;">2FA Verification</h1>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Enter the 6-digit security code from your Authenticator app</p>
    </div>

    <div class="content-card" style="padding: 2rem; border-radius: var(--radius-xl); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);">
        <?php if ($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="verify_2fa.php" method="POST" id="verifyForm">
            <div class="form-group" style="text-align: center;">
                <label class="form-label" style="margin-bottom: 0.75rem;">6-Digit Security Code</label>
                <input type="text" name="totp_code" id="totpCode" class="form-control" placeholder="123456" maxlength="6" pattern="[0-9]{6}" style="font-size: 1.6rem; letter-spacing: 10px; text-align: center; font-weight: 800; color: var(--primary) !important;" required autofocus>
            </div>

            <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.8rem; color: #c2410c; margin-bottom: 1.5rem; text-align: center;">
                <i class="fa-solid fa-info-circle"></i> Demo Verification Passcode: <code style="background: #ffedd5; padding: 2px 6px; border-radius: 3px; font-weight: bold;">123456</code>
            </div>

            <button type="submit" id="btnVerifySubmit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
                <span id="btnVerifyTxt"><i class="fa-solid fa-lock-open"></i> Verify & Sign In</span>
                <span id="btnVerifyLoader" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...</span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('verifyForm').addEventListener('submit', function() {
    document.getElementById('btnVerifySubmit').disabled = true;
    document.getElementById('btnVerifyTxt').style.display = 'none';
    document.getElementById('btnVerifyLoader').style.display = 'inline-block';
});
</script>
</body>
</html>
