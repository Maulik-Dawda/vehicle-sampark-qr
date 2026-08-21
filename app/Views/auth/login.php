<?php
// app/Views/auth/login.php - Responsive Admin Login View
include __DIR__ . '/../layouts/header.php';
?>

<div class="auth-card" style="max-width: 440px; margin: 3rem auto; padding: 2rem 1.5rem;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" style="height: 54px; border-radius: var(--radius-sm); margin-bottom: 0.75rem;" onerror="this.src='assets/images/logo-icon.svg'">
        <h2 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 800; color: var(--text-main);">Admin Login</h2>
        <p style="color: var(--text-muted); font-size: 0.88rem;">Enter your credentials to access the enterprise dashboard</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="admin-qr-login" method="POST">
        <div class="form-group">
            <label class="form-label">Username or Email</label>
            <input type="text" name="username" class="form-control" placeholder="admin or admin@vehiclesampark.com" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <a href="admin-qr-forgot-password" style="font-size: 0.85rem; font-weight: 600; color: var(--primary);">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
            <i class="fa-solid fa-right-to-bracket"></i> Login to Dashboard
        </button>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
