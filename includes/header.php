<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Vehicle Sampark - Smart Vehicle QR System' ?></title>
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Responsive CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    <!-- Top Responsive Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" class="brand-logo-img" onerror="this.src='assets/images/logo-icon.svg'">
                <div class="brand-text">
                    <span class="brand-name">Vehicle <span>Sampark</span></span>
                    <span class="brand-tag">Connecting Mobility</span>
                </div>
            </a>
            
            <div class="nav-actions">
                <?php if (isAdminLoggedIn()): ?>
                    <?php $currentAdmin = getLoggedInAdmin(); ?>
                    <a href="dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php') ? 'active' : '' ?>">
                        <i class="fa-solid fa-qrcode"></i> QR Codes
                    </a>
                    <a href="batches.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'batches.php') ? 'active' : '' ?>">
                        <i class="fa-solid fa-layer-group"></i> Form Batches
                    </a>
                    <button type="button" class="btn btn-primary btn-glow" id="btnOpenGenerator">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Code
                    </button>
                    <!-- Admin User Profile Badge & Logout -->
                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-left: 0.5rem; padding-left: 0.75rem; border-left: 1px solid var(--border-color);">
                        <a href="profile.php" class="btn btn-outline btn-sm <?= (basename($_SERVER['PHP_SELF']) === 'profile.php') ? 'active' : '' ?>" title="Admin Profile & Security Settings">
                            <i class="fa-solid fa-user-gear" style="color: var(--primary);"></i> <?= htmlspecialchars($currentAdmin['username']) ?>
                        </a>
                        <a href="logout.php" class="btn btn-outline btn-sm" title="Sign Out of Admin Portal" style="color: var(--accent-rose); border-color: #fecaca;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="main-content">
