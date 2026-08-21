<?php
// app/Views/auth/forgot_password.php - Responsive Forgot Password View
include __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card" style="max-width: 440px; margin: 3rem auto; padding: 2rem 1.5rem;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--text-main);">Forgot Password</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Enter your registered email address to reset your password</p>
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
    <?php endif; ?>

    <form action="admin-qr-forgot-password" method="POST">
        <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="admin@vehiclesampark.com" required>
        </div>

        <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
            <i class="fa-solid fa-paper-plane"></i> Send Reset Request
        </button>

        <div style="text-align: center; margin-top: 1.25rem;">
            <a href="admin-qr-login" style="font-size: 0.88rem; font-weight: 600; color: var(--text-muted);"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
