<?php
// app/Views/auth/reset_password.php - Responsive Reset Password View
include __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card" style="max-width: 440px; margin: 3rem auto; padding: 2rem 1.5rem;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--text-main);">Reset Password</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Enter your new admin account password</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom: 1.25rem;">
            <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
        </div>
        <div style="margin-top: 1.25rem;">
            <a href="admin-qr-login" class="btn btn-primary" style="width: 100%;">Go to Login</a>
        </div>
    <?php else: ?>
        <form action="admin-qr-reset-password?token=<?= urlencode($token) ?>" method="POST">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
                <i class="fa-solid fa-key"></i> Update Password
            </button>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
