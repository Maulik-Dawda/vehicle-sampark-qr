<?php
// includes/header.php - Vehicle Sampark Modern Enterprise Header

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$currentAdmin = isAdminLoggedIn() ? getLoggedInAdmin() : null;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle ?? 'Vehicle Sampark | Smart Vehicle QR System') ?></title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Modern Enterprise CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

    <!-- TOP NAVIGATION NAVBAR -->
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
                    <a href="admin-qr-dashboard" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'admin-qr-dashboard') !== false || $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span>
                    </a>
                    <a href="admin-qr-batches" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'admin-qr-batches') !== false || $currentPage === 'batches.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-layer-group"></i> <span>Form Batches</span>
                    </a>
                    <a href="admin-qr-profile" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'admin-qr-profile') !== false || $currentPage === 'profile.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-gear"></i> <span>Settings</span>
                    </a>
                    
                    <div class="user-chip">
                        <div class="user-avatar"><?= strtoupper(substr($currentAdmin['username'], 0, 1)) ?></div>
                        <span><?= htmlspecialchars($currentAdmin['username']) ?></span>
                        <button type="button" onclick="openLogoutModal(event)" title="Logout" style="background: none; border: none; color: var(--accent-rose); margin-left: 0.25rem; cursor: pointer; padding: 4px 6px; font-size: 1.05rem; display: inline-flex; align-items: center;">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
