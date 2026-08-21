<?php
// app/Views/home/index.php - Landing Page View
$pageTitle = 'Vehicle Sampark | Smart Vehicle QR & Emergency Safety System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Responsive CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    
    <style>
        .announcement-bar {
            background: linear-gradient(90deg, #10b981 0%, #059669 50%, #f97316 100%);
            color: #ffffff;
            text-align: center;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2);
        }

        .hero-section {
            background: radial-gradient(circle at 50% 0%, #ecfdf5 0%, #ffffff 65%, #f8fafc 100%);
            padding: 5rem 1.5rem 4.5rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            background: #ffffff;
            border: 1px solid #a7f3d0;
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-full);
            color: #047857;
            font-size: 0.88rem;
            font-weight: 800;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.15);
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-family: var(--font-heading);
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 900;
            color: #0f172a;
            line-height: 1.12;
            letter-spacing: -1px;
            margin-bottom: 1.25rem;
        }

        .hero-title .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: clamp(0.95rem, 2vw, 1.15rem);
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2.25rem;
            max-width: 580px;
        }

        .section-header {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 3.5rem auto;
        }

        .section-tag {
            display: inline-block;
            background: #ecfdf5;
            color: #047857;
            padding: 0.35rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            border: 1px solid #a7f3d0;
        }

        .section-title {
            font-family: var(--font-heading);
            font-size: clamp(1.75rem, 4vw, 2.4rem);
            font-weight: 800;
            color: #0f172a;
            line-height: 1.25;
        }
    </style>
</head>
<body>

    <!-- TOP ANNOUNCEMENT TICKER BANNER -->
    <div class="announcement-bar">
        <i class="fa-solid fa-bolt" style="color: #fbbf24;"></i> <span id="announcementText">🚀 Instant 1-Click WhatsApp Bot & Call Relay &bull; 100% Mobile Number Privacy Protection Guaranteed!</span>
    </div>

    <!-- MAIN STICKY NAVIGATION BAR -->
    <nav class="navbar" style="height: 72px;">
        <div class="nav-container" style="max-width: 1380px; height: 100%;">
            <a href="index.php" class="nav-brand">
                <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" class="brand-logo-img" style="height: 44px; border-radius: var(--radius-sm);" onerror="this.src='assets/images/logo-icon.svg'">
                <div>
                    <span style="font-family: var(--font-heading); font-weight: 900; font-size: 1.35rem; color: #0f172a; display: block; line-height: 1;">Vehicle Sampark</span>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #10b981; letter-spacing: 0.5px;">ALWAYS WITHIN REACH</span>
                </div>
            </a>
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <a href="#contact" class="btn btn-outline btn-sm"><i class="fa-solid fa-envelope"></i> Contact Us</a>
                <a href="admin-qr-login" class="btn btn-primary btn-sm"><i class="fa-solid fa-lock"></i> Admin Portal</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section">
        <div class="page-container" style="max-width: 1380px;">
            <div style="text-align: center; max-width: 800px; margin: 0 auto;">
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i> Instant Call Relay & Number Masking
                </div>
                <h1 class="hero-title">
                    Vehicle Sampark <br><span class="gradient-text">Always Within Reach</span>
                </h1>
                <p class="hero-subtitle" style="margin: 0 auto 2rem auto;">
                    A simple scan lets anyone contact the vehicle owner instantly. Protect your vehicle from wrong parking, emergency situations, or towing — while keeping your personal phone number 100% hidden.
                </p>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="#contact" class="btn btn-primary btn-glow" style="padding: 1rem 2rem; font-size: 1.1rem; border-radius: 14px;">
                        <i class="fa-solid fa-qrcode"></i> Get Your Smart QR Tag Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>
