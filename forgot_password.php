<?php
// forgot_password.php - Admin Password Reset Request Interface

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your registered email address.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$email, $email]);
        $admin = $stmt->fetch();

        if ($admin) {
            $resetToken = bin2hex(random_bytes(16));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $upStmt = $pdo->prepare("UPDATE admins SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
            $upStmt->execute([$resetToken, $expires, $admin['id']]);

            $_SESSION['reset_token_active'] = $resetToken;
            $message = "Password reset token generated. Proceed to reset page.";
            header("Location: reset_password.php?token=" . urlencode($resetToken));
            exit;
        } else {
            $error = 'No administrator account found with that email or username.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Vehicle Sampark</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body style="background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 420px;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" style="height: 60px; border-radius: var(--radius-md); margin-bottom: 0.75rem;" onerror="this.src='assets/images/logo-icon.svg'">
        <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: #0f172a;">Password Recovery</h1>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Enter your registered email or username</p>
    </div>

    <div class="content-card" style="padding: 2rem; border-radius: var(--radius-xl); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);">
        <?php if ($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.75rem; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 1.25rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="email">Admin Email or Username</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="text" name="email" id="email" class="form-control" style="padding-left: 2.6rem;" placeholder="admin@vehiclesampark.com" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem; margin-top: 0.5rem;">
                <i class="fa-solid fa-key"></i> Generate Reset Link
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.25rem;">
            <a href="login.php" style="font-size: 0.88rem; font-weight: 600; color: var(--primary); text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>
</body>
</html>
