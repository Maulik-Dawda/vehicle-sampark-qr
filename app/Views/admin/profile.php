<?php
// app/Views/admin/profile.php - Responsive Admin Profile & Settings View
include __DIR__ . '/../layouts/header.php';
?>

<div class="page-container" style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 1.5rem;">
        <h1 class="page-title"><i class="fa-solid fa-user-gear" style="color: var(--primary);"></i> Admin Profile & Security</h1>
        <p class="page-subtitle">Manage your admin username, email, and password settings</p>
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

    <div class="content-card" style="padding: 1.75rem;">
        <form action="admin-qr-profile" method="POST">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">
                <i class="fa-solid fa-user" style="color: var(--primary);"></i> Account Details
            </h3>

            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">
                <i class="fa-solid fa-lock" style="color: var(--primary);"></i> Change Password (Optional)
            </h3>

            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
            </div>

            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary btn-glow" style="padding: 0.85rem 1.5rem;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
