<?php
// index.php - Vehicle Sampark Landing Page with Clean HTML Formatting in Cartoon Privacy Story

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/tag_template.php';

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
        /* LANDING PAGE CUSTOM HIGH-AESTHETIC STYLES */
        .announcement-bar {
            background: linear-gradient(90deg, #10b981 0%, #059669 50%, #f97316 100%);
            color: #ffffff;
            text-align: center;
            padding: 0.55rem 1rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.2);
        }

        .hero-section {
            background: radial-gradient(circle at 50% 0%, #ecfdf5 0%, #ffffff 70%, #f8fafc 100%);
            padding: 4.5rem 1.5rem 4rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffffff;
            border: 1px solid #a7f3d0;
            padding: 0.45rem 1.1rem;
            border-radius: var(--radius-full);
            color: #047857;
            font-size: 0.85rem;
            font-weight: 800;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.12);
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-family: var(--font-heading);
            font-size: 3.1rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.12;
            letter-spacing: -0.8px;
            margin-bottom: 1.25rem;
        }

        .hero-title .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.12rem;
            color: var(--text-muted);
            line-height: 1.65;
            margin-bottom: 2.25rem;
            max-width: 580px;
        }

        /* HERO PERFECTLY ALIGNED ANIMATED SCANNER CONTAINER */
        .hero-scanner-container {
            position: relative;
            display: inline-block;
            width: 100%;
            max-width: 580px;
            margin: 0 auto;
        }

        .hero-scanner-container::before {
            content: '';
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(249, 115, 22, 0.2));
            border-radius: 24px;
            filter: blur(25px);
            z-index: 0;
            animation: heroGlowPulse 4s infinite ease-in-out;
        }

        @keyframes heroGlowPulse {
            0%, 100% { opacity: 0.4; transform: scale(0.98); }
            50% { opacity: 0.8; transform: scale(1.02); }
        }

        .scanner-card-box {
            position: relative;
            z-index: 1;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .hero-scanner-container .sampark-tag-box {
            margin-bottom: 0 !important;
            box-shadow: none !important;
            border-radius: 10px !important;
        }

        .scanner-laser {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent 0%, #10b981 50%, transparent 100%);
            box-shadow: 0 0 15px #10b981, 0 0 25px #10b981;
            z-index: 10;
            animation: laserSweep 3.5s infinite ease-in-out;
        }

        @keyframes laserSweep {
            0% { top: 0%; opacity: 0; }
            15% { opacity: 1; }
            50% { top: 100%; opacity: 1; }
            65% { opacity: 1; }
            100% { top: 0%; opacity: 0; }
        }

        .scanner-bracket {
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #10b981;
            z-index: 11;
            pointer-events: none;
        }

        .bracket-tl { top: 6px; left: 6px; border-right: none; border-bottom: none; border-top-left-radius: 6px; }
        .bracket-tr { top: 6px; right: 6px; border-left: none; border-bottom: none; border-top-right-radius: 6px; }
        .bracket-bl { bottom: 6px; left: 6px; border-right: none; border-top: none; border-bottom-left-radius: 6px; }
        .bracket-br { bottom: 6px; right: 6px; border-left: none; border-top: none; border-bottom-right-radius: 6px; }

        .scan-status-toast {
            margin: 1.25rem auto 0 auto;
            max-width: 580px;
            width: 100%;
            background: #ffffff;
            border: 2px solid #a7f3d0;
            padding: 0.85rem 1.25rem;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f172a;
            position: relative;
            z-index: 5;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        .scan-status-toast.connected {
            border-color: #10b981;
            background: #ecfdf5;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #f97316;
            display: inline-block;
            animation: dotPing 1.5s infinite;
        }

        @keyframes dotPing {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(249, 115, 22, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(249, 115, 22, 0); }
        }

        .status-dot.active-green {
            background: #10b981;
            animation: greenDotPing 1.5s infinite;
        }

        @keyframes greenDotPing {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .action-pills-live {
            display: flex;
            gap: 0.4rem;
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.4s ease;
        }

        .action-pills-live.show {
            opacity: 1;
            transform: scale(1);
        }

        /* INFINITE SCROLLING TICKER MARQUEE */
        .ticker-wrap {
            width: 100%;
            overflow: hidden;
            background: #0f172a;
            color: #ffffff;
            padding: 0.9rem 0;
            position: relative;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
        }

        .ticker-move {
            display: flex;
            gap: 2.5rem;
            width: max-content;
            animation: tickerSlide 28s linear infinite;
        }

        .ticker-move:hover {
            animation-play-state: paused;
        }

        @keyframes tickerSlide {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.92rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: #f8fafc;
            white-space: nowrap;
        }

        .ticker-item i {
            color: var(--primary);
            font-size: 1.05rem;
        }

        /* CARTOON ANIMATED PRIVACY STORY TIMELINE SECTION */
        .story-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 5rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .story-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }

        .story-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
        }

        .story-card:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.08);
            border-color: #10b981;
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.15);
        }

        .story-badge-num {
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: 0.88rem;
            padding: 0.25rem 0.85rem;
            border-radius: var(--radius-full);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        /* CARTOON SCENE ILLUSTRATION CONTAINERS */
        .cartoon-scene-box {
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
        }

        /* SCENE 1: CAR WRONGLY PARKED */
        .car-anim-container {
            font-size: 4rem;
            color: #f97316;
            animation: carShake 2s infinite ease-in-out;
            position: relative;
        }

        @keyframes carShake {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-4px) rotate(-1deg); }
            75% { transform: translateY(2px) rotate(1deg); }
        }

        .alert-bubble-cartoon {
            position: absolute;
            top: -15px;
            right: -25px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 0.2rem 0.55rem;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
            animation: alertPop 1.5s infinite alternate;
        }

        @keyframes alertPop {
            0% { transform: scale(0.9); }
            100% { transform: scale(1.1); }
        }

        /* SCENE 2: SCANNING QR CODE */
        .phone-scan-container {
            position: relative;
            font-size: 3.5rem;
            color: #10b981;
        }

        .scan-beam-line {
            position: absolute;
            top: 25%;
            left: 20%;
            width: 60%;
            height: 3px;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: scanBeamMove 1.8s infinite alternate ease-in-out;
        }

        @keyframes scanBeamMove {
            0% { top: 20%; }
            100% { top: 70%; }
        }

        /* SCENE 3: BOT GATEWAY MASKING */
        .bot-shield-container {
            font-size: 3.8rem;
            color: #25d366;
            position: relative;
            animation: shieldPulse 2.5s infinite ease-in-out;
        }

        @keyframes shieldPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 5px rgba(37, 211, 102, 0.4)); }
            50% { transform: scale(1.08); filter: drop-shadow(0 0 20px rgba(37, 211, 102, 0.8)); }
        }

        .privacy-lock-icon {
            position: absolute;
            bottom: -5px;
            right: -10px;
            background: #0f172a;
            border: 2px solid #10b981;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            color: #10b981;
        }

        /* SCENE 4: ANONYMOUS ALERT SOLVED */
        .owner-received-container {
            font-size: 3.8rem;
            color: #38bdf8;
            position: relative;
        }

        .success-checkmark-cartoon {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #10b981;
            color: #ffffff;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.5);
            animation: checkBounce 1.8s infinite ease-in-out;
        }

        @keyframes checkBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* FLOATING WHATSAPP QUICK CONNECT WIDGET (BOTTOM RIGHT) */
        .floating-wa-wrapper {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.65rem;
        }

        .wa-chat-bubble {
            background: #ffffff;
            border: 1px solid #a7f3d0;
            border-left: 5px solid #25d366;
            padding: 0.85rem 1.15rem;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            max-width: 270px;
            font-size: 0.88rem;
            color: #0f172a;
            position: relative;
            animation: waBubbleFloat 3s infinite ease-in-out;
        }

        @keyframes waBubbleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .wa-bubble-close {
            position: absolute;
            top: 4px;
            right: 8px;
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 0.8rem;
            cursor: pointer;
        }

        .wa-btn-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.95rem;
            box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .wa-btn-circle:hover {
            transform: scale(1.08);
            box-shadow: 0 12px 30px rgba(37, 211, 102, 0.55);
            color: #ffffff;
        }

        .wa-btn-circle::before {
            content: '';
            position: absolute;
            top: -6px;
            left: -6px;
            right: -6px;
            bottom: -6px;
            border: 2px solid #25d366;
            border-radius: 50%;
            animation: waPulseRing 2s infinite ease-out;
        }

        @keyframes waPulseRing {
            0% { transform: scale(0.95); opacity: 0.9; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        /* HAZARD CARDS */
        .section-header {
            text-align: center;
            max-width: 680px;
            margin: 0 auto 3.5rem auto;
        }

        .section-tag {
            color: var(--accent-orange);
            font-weight: 800;
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-bottom: 0.4rem;
        }

        .section-title {
            font-family: var(--font-heading);
            font-size: 2.35rem;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.2;
            letter-spacing: -0.6px;
        }

        .hazard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 1.75rem;
        }

        .hazard-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-card);
            position: relative;
            overflow: hidden;
        }

        .hazard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent-orange));
            opacity: 0;
            transition: var(--transition);
        }

        .hazard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.08);
            border-color: var(--primary);
        }

        .hazard-card:hover::before {
            opacity: 1;
        }

        .hazard-icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1.15rem;
        }

        .icon-box-orange { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
        .icon-box-green { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

        .hazard-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.45rem;
        }

        .hazard-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* STEP BY STEP FLOW */
        .step-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2.25rem 1.75rem;
            text-align: center;
            box-shadow: var(--shadow-card);
            position: relative;
            transition: var(--transition);
        }

        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .step-num-badge {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: 1.4rem;
            margin: 0 auto 1.35rem auto;
            box-shadow: 0 6px 15px rgba(0,0,0,0.06);
        }

        /* COMPARISON TABLE */
        .compare-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }

        .compare-table {
            width: 100%;
            border-collapse: collapse;
        }

        .compare-table th, .compare-table td {
            padding: 1.15rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.92rem;
        }

        .compare-table th {
            background: #f8fafc;
            font-family: var(--font-heading);
            font-weight: 800;
            color: #0f172a;
            font-size: 0.98rem;
        }

        /* FAQ ACCORDION */
        .faq-item {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-item.open {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.08);
        }

        .faq-question {
            padding: 1.25rem 1.5rem;
            font-weight: 800;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.02rem;
        }

        .faq-answer {
            padding: 0 1.5rem 1.25rem 1.5rem;
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.65;
            display: none;
        }

        .faq-item.open .faq-answer {
            display: block;
        }

        .faq-item.open .faq-icon {
            transform: rotate(180deg);
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- ANNOUNCEMENT BAR -->
    <div class="announcement-bar">
        ⚡ 100% Privacy-Guarded Smart Vehicle QR System &bull; Instant Call & WhatsApp Emergency Alerts!
    </div>

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
                <a href="#how-it-works" class="nav-link"><i class="fa-solid fa-list-check"></i> How It Works</a>
                <a href="#hazards" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Emergency Cases</a>
                <a href="#why-us" class="nav-link"><i class="fa-solid fa-shield-halved"></i> Why Us</a>
                <a href="#faq" class="nav-link"><i class="fa-solid fa-circle-question"></i> FAQ</a>
                <a href="#contact" class="nav-link"><i class="fa-solid fa-envelope"></i> Contact Us</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION WITH PERFECTLY ALIGNED ANIMATED SCANNER -->
    <section class="hero-section">
        <div style="max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem; align-items: center;">
            <div>
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i> Protect Your Vehicle & Personal Privacy
                </div>

                <h1 class="hero-title">
                    Let Anyone Reach You With a <span class="gradient-text">Quick QR Scan</span>
                </h1>

                <p class="hero-subtitle">
                    A simple scan lets anyone contact you instantly. Get real-time WhatsApp alerts, 4-option emergency bot reports, and direct calls if your vehicle needs attention — without revealing your personal phone number.
                </p>

                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="#contact" class="btn btn-primary btn-glow" style="padding: 1.05rem 1.85rem; font-size: 1.05rem; border-radius: var(--radius-lg);">
                        <i class="fa-solid fa-phone-volume"></i> Get Smart QR Tag Now
                    </a>
                    <a href="https://wa.me/919876543210?text=Hi%20Vehicle%20Sampark!%20I%20want%20to%20get%20smart%20vehicle%20QR%20tags" target="_blank" class="btn btn-secondary" style="padding: 1.05rem 1.85rem; font-size: 1.05rem; color: #ea580c; border-color: #ffedd5; border-radius: var(--radius-lg);">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Chat on WhatsApp
                    </a>
                </div>

                <!-- TRUST BADGES -->
                <div style="display: flex; gap: 2rem; margin-top: 2.5rem; padding-top: 1.75rem; border-top: 1px solid #e2e8f0; flex-wrap: wrap;">
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 900; color: var(--primary);">100% Private</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">No Numbers Revealed</div>
                    </div>
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 900; color: var(--accent-orange);">Instant Relay</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">Call & WhatsApp Bot</div>
                    </div>
                    <div>
                        <div style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 900; color: #0f172a;">Smart Badge</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700;">Center Logo Tag Card</div>
                    </div>
                </div>
            </div>

            <!-- HERO PERFECTLY ALIGNED SCANNER DEMO MOCKUP -->
            <div style="text-align: center;">
                <div class="hero-scanner-container">
                    <div class="scanner-card-box">
                        <!-- Laser Beam -->
                        <div class="scanner-laser"></div>
                        <!-- Target Brackets -->
                        <div class="scanner-bracket bracket-tl"></div>
                        <div class="scanner-bracket bracket-tr"></div>
                        <div class="scanner-bracket bracket-bl"></div>
                        <div class="scanner-bracket bracket-br"></div>

                        <!-- Render Physical Tag Card -->
                        <?= renderVehicleSamparkTagHTML('QRC-SAMPARK-01', 'Vehicle Contact Tag') ?>
                    </div>

                    <!-- LIVE ANIMATED CONNECTION STATUS TOAST -->
                    <div class="scan-status-toast" id="liveHeroScanToast">
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <span class="status-dot" id="liveHeroDot"></span>
                            <span id="liveHeroMsg">Scanning Vehicle Tag...</span>
                        </div>
                        
                        <div class="action-pills-live" id="liveHeroPills">
                            <span class="badge badge-submitted" style="background: #10b981; color: #ffffff;">
                                <i class="fa-solid fa-phone-volume"></i> Call Owner
                            </span>
                            <span class="badge" style="background: #f97316; color: #ffffff;">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp Bot
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LIVE INFINITE SCROLLING TICKER MARQUEE -->
    <div class="ticker-wrap">
        <div class="ticker-move">
            <div class="ticker-item"><i class="fa-solid fa-shield-halved"></i> 100% Privacy Guarded - No Mobile Numbers Revealed</div>
            <div class="ticker-item"><i class="fa-solid fa-bolt"></i> Instant Call & 4-Option Emergency WhatsApp Bot Relay</div>
            <div class="ticker-item"><i class="fa-solid fa-car-burst"></i> Avoid Scratching, Vandalism & Unexpected Towing Fines</div>
            <div class="ticker-item"><i class="fa-solid fa-soap"></i> Integrated Doorstep Car Cleaning & Nearby Garage Support</div>
            <div class="ticker-item"><i class="fa-solid fa-qrcode"></i> Zero App Download - Scans with Any Phone Camera or Google Lens</div>

            <!-- Duplicate for Seamless Infinite Loop -->
            <div class="ticker-item"><i class="fa-solid fa-shield-halved"></i> 100% Privacy Guarded - No Mobile Numbers Revealed</div>
            <div class="ticker-item"><i class="fa-solid fa-bolt"></i> Instant Call & 4-Option Emergency WhatsApp Bot Relay</div>
            <div class="ticker-item"><i class="fa-solid fa-car-burst"></i> Avoid Scratching, Vandalism & Unexpected Towing Fines</div>
            <div class="ticker-item"><i class="fa-solid fa-soap"></i> Integrated Doorstep Car Cleaning & Nearby Garage Support</div>
            <div class="ticker-item"><i class="fa-solid fa-qrcode"></i> Zero App Download - Scans with Any Phone Camera or Google Lens</div>
        </div>
    </div>

    <!-- HOW IT WORKS - 3 STEP QUICK FLOW -->
    <section id="how-it-works" style="padding: 5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Scan To Call & WhatsApp</span>
                <h2 class="section-title">How Vehicle Sampark Protects You in 3 Easy Steps</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <div class="step-card">
                    <div class="step-num-badge" style="background: #ecfdf5; color: var(--primary); border: 2px solid #a7f3d0;">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">1. Scan the QR Code</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.65;">
                        Anyone can scan the Vehicle Sampark sticker on your windshield using any smartphone camera — zero app download required.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-num-badge" style="background: #fff7ed; color: var(--accent-orange); border: 2px solid #ffedd5;">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">2. Connect Instantly</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.65;">
                        Call the car owner directly or select WhatsApp emergency bot options without exposing personal phone numbers.
                    </p>
                </div>

                <div class="step-card">
                    <div class="step-num-badge" style="background: #ecfdf5; color: var(--primary); border: 2px solid #a7f3d0;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">3. Resolve the Issue</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.65;">
                        Coordinate moving the car, wrong parking, towing, or emergency situations quickly, safely, and peacefully.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CARTOON ANIMATED PRIVACY STORY TIMELINE -->
    <section class="story-section">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 3.5rem;">
                <span class="section-tag" style="color: #f97316;">Zero Personal Number Shared</span>
                <h2 class="section-title" style="color: #ffffff;">How Vehicle Sampark Connects You Safely</h2>
                <p style="color: #94a3b8; font-size: 1.02rem; margin-top: 0.5rem;">
                    Bystanders connect with vehicle owners through our official company WhatsApp gateway — keeping your private phone number 100% hidden!
                </p>
            </div>

            <div class="story-grid">
                <!-- SCENE 1: WRONG PARKING -->
                <div class="story-card">
                    <div class="story-badge-num">SCENE 1</div>
                    <div class="cartoon-scene-box">
                        <div class="car-anim-container">
                            <i class="fa-solid fa-car-side"></i>
                            <div class="alert-bubble-cartoon">🚫 Blocked Exit!</div>
                        </div>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Vehicle Wrongly Parked</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6;">
                        A car blocks a driveway, gate, or another vehicle in a crowded parking area. The driver is away.
                    </p>
                </div>

                <!-- SCENE 2: SCANNING QR STICKER -->
                <div class="story-card">
                    <div class="story-badge-num">SCENE 2</div>
                    <div class="cartoon-scene-box">
                        <div class="phone-scan-container">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <div class="scan-beam-line"></div>
                        </div>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Bystander Scans QR Tag</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6;">
                        The bystander scans the Vehicle Sampark sticker on the car windshield using any phone camera.
                    </p>
                </div>

                <!-- SCENE 3: COMPANY WHATSAPP BOT GATEWAY -->
                <div class="story-card">
                    <div class="story-badge-num">SCENE 3</div>
                    <div class="cartoon-scene-box">
                        <div class="bot-shield-container">
                            <i class="fa-brands fa-whatsapp"></i>
                            <div class="privacy-lock-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        </div>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Company WhatsApp Masking</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6;">
                        Connected via Vehicle Sampark's official WhatsApp Bot line. <strong style="color: #10b981;">No personal phone number is ever exposed to the bystander.</strong>
                    </p>
                </div>

                <!-- SCENE 4: OWNER RELAYED ALERT & SOLVED -->
                <div class="story-card">
                    <div class="story-badge-num">SCENE 4</div>
                    <div class="cartoon-scene-box">
                        <div class="owner-received-container">
                            <i class="fa-solid fa-bell"></i>
                            <div class="success-checkmark-cartoon"><i class="fa-solid fa-check"></i></div>
                        </div>
                    </div>
                    <h3 style="font-size: 1.2rem; font-weight: 800; color: #ffffff; margin-bottom: 0.5rem;">Anonymous Alert & Solved!</h3>
                    <p style="color: #94a3b8; font-size: 0.88rem; line-height: 1.6;">
                        The owner receives an urgent WhatsApp alert, moves their vehicle peacefully, and both parties stay 100% private & safe!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- REAL USE CASES & HAZARDS SOLVED GRID -->
    <section id="hazards" style="padding: 5rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 1240px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Real-World Protection</span>
                <h2 class="section-title">Common Vehicle Emergencies & Services Solved</h2>
            </div>

            <div class="hazard-grid">
                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-ban"></i></div>
                    <h4 class="hazard-title">Prevent Scratching & Vandalism</h4>
                    <p class="hazard-desc">
                        Blocked someone in a tight parking spot? They can WhatsApp or call you instantly instead of scratching your car or getting frustrated.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-truck-pickup"></i></div>
                    <h4 class="hazard-title">Avoid Towing & Expensive Fines</h4>
                    <p class="hazard-desc">
                        Traffic police or neighbors can scan your sticker to warn you to move your car before calling a tow truck or issuing a fine.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-soap"></i></div>
                    <h4 class="hazard-title">Doorstep Cleaning Service</h4>
                    <p class="hazard-desc">
                        Request professional doorstep car washing, eco waterless cleaning, interior detailing, and polishing right at your parking spot with one tap.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-wrench"></i></div>
                    <h4 class="hazard-title">Garage & Mechanic Solution</h4>
                    <p class="hazard-desc">
                        Instant access to nearby verified garages, roadside mechanics, flat tyre puncture repair, battery jumpstart, and emergency towing assistance.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-battery-quarter"></i></div>
                    <h4 class="hazard-title">Battery Drain & Headlights Left ON</h4>
                    <p class="hazard-desc">
                        Left your headlights or cabin lights ON? Good Samaritans can warn you before your battery completely dies.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-cloud-showers-heavy"></i></div>
                    <h4 class="hazard-title">Windows Left Open & Theft Prevention</h4>
                    <p class="hazard-desc">
                        Left your window down before rain or theft? Neighbors can scan your tag to alert you before rain enters or thieves strike.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h4 class="hazard-title">Critical Emergencies & Accidents</h4>
                    <p class="hazard-desc">
                        In urgent crash situations, bystanders scan the tag to instantly alert your family with emergency reports when you can't speak.
                    </p>
                </div>

                <div class="hazard-card">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-paw"></i></div>
                    <h4 class="hazard-title">Fluid Leaks & Animal Safety</h4>
                    <p class="hazard-desc">
                        Oil leaks, smoke, or a stray animal trapped under your car engine can be reported immediately by bystanders.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY VEHICLE SAMPARK VS TRADITIONAL STICKERS -->
    <section id="why-us" style="padding: 5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1040px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Privacy Protection Comparison</span>
                <h2 class="section-title">Vehicle Sampark vs Traditional Paper Stickers</h2>
            </div>

            <div class="compare-card">
                <div class="table-responsive">
                    <table class="compare-table">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th style="color: var(--accent-rose);">Traditional Paper Number Card</th>
                                <th style="color: var(--primary); font-size: 1.05rem;">
                                    Vehicle Sampark Smart Tag 
                                    <span class="badge badge-submitted" style="margin-left: 0.4rem; background: #10b981; color: #ffffff;">
                                        <span class="status-dot active-green" style="width:7px; height:7px; margin-right:3px;"></span> LIVE PROTECTED
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: 700;">Personal Phone Number Privacy</td>
                                <td style="color: var(--accent-rose); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Exposed to strangers & spammers</td>
                                <td style="color: var(--primary); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> 100% Privacy Protected</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Automated Emergency WhatsApp Bot</td>
                                <td style="color: var(--accent-rose); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Not Available</td>
                                <td style="color: var(--primary); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> 4-Option WhatsApp Bot Relay</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Doorstep Cleaning & Garage Support</td>
                                <td style="color: var(--accent-rose); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Not Available</td>
                                <td style="color: var(--primary); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> Integrated Service Access</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Update Phone Number Anytime</td>
                                <td style="color: var(--accent-rose); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Must replace physical paper card</td>
                                <td style="color: var(--primary); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> Instant Online Update</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700;">Tamper & Vandal Proof</td>
                                <td style="color: var(--accent-rose); font-weight: 600;"><i class="fa-solid fa-circle-xmark"></i> Easily torn or scratched off</td>
                                <td style="color: var(--primary); font-weight: 800;"><i class="fa-solid fa-circle-check"></i> Pasted Inside Glass / Durable</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- FREQUENTLY ASKED QUESTIONS (FAQ) -->
    <section id="faq" style="padding: 5rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 880px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 3rem;">
                <span class="section-tag">Got Questions?</span>
                <h2 class="section-title">Frequently Asked Questions</h2>
            </div>

            <div class="faq-item open">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How does Vehicle Sampark protect my personal phone number?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    When someone scans your Vehicle Sampark tag, they see direct "Call Owner" or "Chat on WhatsApp" buttons. The call and WhatsApp messages are routed through our secure platform, so your real phone number and identity are never exposed on the physical sticker.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Does a person scanning my QR sticker need to download an app?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    No! Anyone with a regular smartphone camera, Google Lens, or default QR scanner can scan your tag and connect instantly. Zero app downloads required.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do Doorstep Cleaning and Garage Solutions work?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Vehicle Sampark links your vehicle tag with verified doorstep car cleaning partners and nearby emergency garage/breakdown mechanic services, giving you instant access to maintenance and roadside support.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Can I change my registered phone number or vehicle details later?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Yes! You can update your mobile number, WhatsApp number, or car details anytime without replacing your physical tag sticker.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How does the 4-Option Emergency WhatsApp Bot work?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    When a bystander clicks "Chat on WhatsApp", our bot asks them to confirm the vehicle number and select from 4 emergency issues (Wrong Parked, Accident, Lights On/Window Open, Towing Notice). Once selected, an urgent WhatsApp alert is relayed directly to you!
                </div>
            </div>
        </div>
    </section>

    <!-- DIRECT CONTACT & INQUIRY FORM SECTION -->
    <section id="contact" style="padding: 5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1060px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 3rem;">
                <span class="section-tag">Get In Touch</span>
                <h2 class="section-title">Order Smart QR Tags & Inquiries</h2>
                <p style="color: var(--text-muted); font-size: 0.98rem; margin-top: 0.4rem;">No online payment required. Send us an inquiry or contact us directly via Call or WhatsApp!</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.25rem;">
                <!-- Contact Direct Info -->
                <div>
                    <div class="content-card" style="padding: 2rem; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <h3 style="font-size: 1.3rem; font-weight: 900; color: #0f172a; margin-bottom: 1.5rem;">
                                <i class="fa-solid fa-headset" style="color: var(--primary);"></i> Direct Contact Channels
                            </h3>

                            <div style="margin-bottom: 1.25rem;">
                                <a href="tel:+919876543210" class="btn btn-primary btn-glow" style="width: 100%; padding: 1.05rem; font-size: 1.05rem; background: linear-gradient(135deg, #10b981, #059669); border-radius: var(--radius-lg);">
                                    <i class="fa-solid fa-phone-volume"></i> Call Us: +91 98765 43210
                                </a>
                            </div>

                            <div style="margin-bottom: 1.75rem;">
                                <a href="https://wa.me/919876543210?text=Hi%20Vehicle%20Sampark!%20I%20want%20to%20order%20smart%20vehicle%20tags" target="_blank" class="btn btn-primary" style="width: 100%; padding: 1.05rem; font-size: 1.05rem; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: var(--radius-lg);">
                                    <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Chat on WhatsApp
                                </a>
                            </div>
                        </div>

                        <div style="background: #f8fafc; padding: 1.15rem; border-radius: var(--radius-lg); border: 1px solid #e2e8f0;">
                            <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 0.2rem;">
                                <i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Email Us Directly
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-muted); font-weight: 700;">contact@vehiclesampark.com</div>
                        </div>
                    </div>
                </div>

                <!-- Landing Page Inquiry Form -->
                <div class="content-card" style="padding: 2rem;">
                    <h3 style="font-size: 1.3rem; font-weight: 900; color: #0f172a; margin-bottom: 1.25rem;">
                        <i class="fa-solid fa-paper-plane" style="color: var(--accent-orange);"></i> Send Us an Inquiry
                    </h3>

                    <div id="contactFormStatus"></div>

                    <form id="landingContactForm" action="contact_handler.php" method="POST">
                        <div class="form-group">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Mobile / WhatsApp Number <span class="required">*</span></label>
                            <input type="tel" name="mobile" class="form-control" placeholder="+91 98765 43210" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Number of Vehicle Tags Needed</label>
                            <select name="quantity" class="form-control">
                                <option value="1">1 Tag (Personal Car/Bike)</option>
                                <option value="5">5 Tags (Family Fleet)</option>
                                <option value="10">10 Tags (Small Fleet)</option>
                                <option value="50+">50+ Tags (Commercial Fleet / Business)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Message / Delivery Address (Optional)</label>
                            <textarea name="message" class="form-control" rows="3" placeholder="Tell us your location or questions..."></textarea>
                        </div>

                        <button type="submit" id="btnContactSubmit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.95rem; font-size: 1.05rem; border-radius: var(--radius-lg);">
                            <span id="btnContactTxt"><i class="fa-solid fa-paper-plane"></i> Submit Inquiry</span>
                            <span id="btnContactLoader" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> Sending Inquiry...</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FLOATING WHATSAPP QUICK CONNECT WIDGET (BOTTOM RIGHT) -->
    <div class="floating-wa-wrapper">
        <div class="wa-chat-bubble" id="waChatBubble">
            <button class="wa-bubble-close" onclick="document.getElementById('waChatBubble').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
            <div style="font-weight: 800; color: #047857; margin-bottom: 3px;">
                <i class="fa-brands fa-whatsapp"></i> Instant Connect
            </div>
            Hi! Have a question or want to order smart vehicle tags? Chat with us directly on WhatsApp!
        </div>
        <a href="https://wa.me/919876543210?text=Hi%20Vehicle%20Sampark!%20I%20want%20to%20get%20smart%20vehicle%20QR%20tags" target="_blank" class="wa-btn-circle" title="Chat on WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
    </div>

    <!-- FOOTER -->
    <footer style="background: #0f172a; color: #ffffff; padding: 3rem 1.5rem; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="margin-bottom: 1rem;">
                <span style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 900; color: #ffffff;">
                    Vehicle <span style="color: var(--primary);">Sampark</span>
                </span>
            </div>
            <p style="color: #94a3b8; font-size: 0.88rem; margin-bottom: 1.5rem;">
                Connecting Mobility & Smart Emergency Vehicle Safety &bull; Built with ❤️ for Indian Roads &bull; &copy; <?= date('Y') ?>
            </p>
            <div style="display: flex; gap: 1.25rem; justify-content: center; font-size: 0.88rem; color: #94a3b8; flex-wrap: wrap;">
                <a href="#how-it-works" style="color: #94a3b8; text-decoration: none;">How It Works</a> &bull;
                <a href="#hazards" style="color: #94a3b8; text-decoration: none;">Emergency Cases</a> &bull;
                <a href="#why-us" style="color: #94a3b8; text-decoration: none;">Why Vehicle Sampark</a> &bull;
                <a href="#faq" style="color: #94a3b8; text-decoration: none;">FAQ</a> &bull;
                <a href="#contact" style="color: #94a3b8; text-decoration: none;">Contact Us</a>
            </div>
        </div>
    </footer>

    <script>
    // 1. LIVE HERO SCAN ANIMATION SEQUENCE
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('liveHeroScanToast');
        const dot = document.getElementById('liveHeroDot');
        const msg = document.getElementById('liveHeroMsg');
        const pills = document.getElementById('liveHeroPills');

        const states = [
            {
                dotClass: 'status-dot',
                toastClass: 'scan-status-toast',
                text: '🔍 Scanning Vehicle Tag QRC-SAMPARK-01...',
                showPills: false
            },
            {
                dotClass: 'status-dot active-green',
                toastClass: 'scan-status-toast connected',
                text: '⚡ Tag Verified! Fetching Owner Details...',
                showPills: false
            },
            {
                dotClass: 'status-dot active-green',
                toastClass: 'scan-status-toast connected',
                text: '✅ Connected to Vehicle Owner!',
                showPills: true
            }
        ];

        let stepIndex = 0;

        setInterval(function() {
            stepIndex = (stepIndex + 1) % states.length;
            const currentState = states[stepIndex];

            dot.className = currentState.dotClass;
            toast.className = currentState.toastClass;
            msg.textContent = currentState.text;

            if (currentState.showPills) {
                pills.classList.add('show');
            } else {
                pills.classList.remove('show');
            }
        }, 1800);
    });

    function toggleFaq(element) {
        const item = element.parentElement;
        item.classList.toggle('open');
    }

    document.getElementById('landingContactForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const btn = document.getElementById('btnContactSubmit');
        const txt = document.getElementById('btnContactTxt');
        const loader = document.getElementById('btnContactLoader');
        const statusDiv = document.getElementById('contactFormStatus');

        btn.disabled = true;
        txt.style.display = 'none';
        loader.style.display = 'inline-block';
        statusDiv.innerHTML = '';

        const formData = new FormData(form);

        fetch('contact_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            txt.style.display = 'inline-block';
            loader.style.display = 'none';

            if (data.success) {
                statusDiv.innerHTML = `
                    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 0.95rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.25rem; font-weight: 700;">
                        <i class="fa-solid fa-circle-check"></i> ${data.message}
                    </div>
                `;
                form.reset();
            } else {
                statusDiv.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.95rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.25rem; font-weight: 700;">
                        <i class="fa-solid fa-triangle-exclamation"></i> ${data.message}
                    </div>
                `;
            }
        })
        .catch(err => {
            btn.disabled = false;
            txt.style.display = 'inline-block';
            loader.style.display = 'none';
            statusDiv.innerHTML = `
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.95rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.25rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Network error. Please try again or call us directly.
                </div>
            `;
        });
    });
    </script>
</body>
</html>
