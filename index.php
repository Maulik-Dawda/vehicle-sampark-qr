<?php
$theReq = $_SERVER['THE_REQUEST'] ?? '';
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($theReq, 'index.php') !== false || strpos($reqUri, 'index.php') !== false) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
// index.php - Vehicle Sampark Modern Enterprise Visual Landing Page

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
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
        /* LANDING PAGE HIGH-AESTHETIC STYLES */
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
            font-size: 3.25rem;
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
            font-size: 1.15rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2.25rem;
            max-width: 580px;
        }

        /* HERO ANIMATED SCANNER CONTAINER */
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
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(249, 115, 22, 0.25));
            border-radius: 24px;
            filter: blur(25px);
            z-index: 0;
            animation: heroGlowPulse 4s infinite ease-in-out;
        }

        @keyframes heroGlowPulse {
            0%, 100% { opacity: 0.4; transform: scale(0.98); }
            50% { opacity: 0.85; transform: scale(1.02); }
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
            padding: 0.95rem 0;
            position: relative;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.2);
            border-top: 1px solid rgba(255,255,255,0.05);
            border-bottom: 1px solid rgba(255,255,255,0.05);
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
            gap: 0.65rem;
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
            padding: 5.5rem 1.5rem;
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
            padding: 2.25rem 1.5rem 1.75rem 1.5rem;
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
            padding: 0.28rem 0.95rem;
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
            font-size: 2.4rem;
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
            transform: translateY(-6px);
            box-shadow: 0 18px 35px rgba(16, 185, 129, 0.12);
            border-color: #10b981;
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

        /* COMPARISON SHOWCASE CARDS */
        .compare-showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2rem;
        }

        .compare-box-item {
            border-radius: 20px;
            padding: 2.25rem 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .compare-box-old {
            background: #fff1f2;
            border: 2px solid #fecaca;
        }

        .compare-box-new {
            background: #ecfdf5;
            border: 2.5px solid #10b981;
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.15);
        }

        /* FAQ ACCORDION */
        .faq-item {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        .faq-item.open {
            border-color: #10b981;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.1);
        }

        .faq-question {
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1rem;
        }

        .faq-answer {
            padding: 0 1.5rem 1.35rem 1.5rem;
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.65;
            display: none;
        }

        .faq-item.open .faq-answer {
            display: block;
        }

        .faq-icon {
            transition: transform 0.3s ease;
            color: var(--primary);
        }

        .faq-item.open .faq-icon {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>

    <!-- TOP ANNOUNCEMENT BAR -->
    <div class="announcement-bar">
        🚀 Instant 1-Click WhatsApp Bot & Call Relay &bull; 100% Mobile Number Privacy Protection Guaranteed!
    </div>

    <!-- TOP NAVIGATION NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand notranslate" translate="no">
                <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" class="brand-logo-img notranslate" translate="no" onerror="this.src='assets/images/logo-icon.svg'">
                <div class="brand-text notranslate" translate="no">
                    <span class="brand-name notranslate" translate="no">Vehicle <span class="notranslate" translate="no">Sampark</span></span>
                    <span class="brand-tag notranslate" translate="no">Connecting Mobility</span>
                </div>
            </a>

            <div class="nav-actions">
                <a href="#how-it-works" class="nav-link"><i class="fa-solid fa-list-check"></i> How It Works</a>
                <a href="#hazards" class="nav-link"><i class="fa-solid fa-triangle-exclamation"></i> Hazards Solved</a>
                <a href="#why-us" class="nav-link"><i class="fa-solid fa-shield-halved"></i> Why Us</a>
                <a href="#faq" class="nav-link"><i class="fa-solid fa-circle-question"></i> FAQ</a>
                <a href="#contact" class="nav-link"><i class="fa-solid fa-envelope"></i> Contact Us</a>

                <!-- CUSTOM MULTI-LANGUAGE DROPDOWN COMPONENT (100% STYLED & NOTRANSLATE) -->
                <div class="custom-lang-dropdown notranslate" translate="no" id="customLangDropdown">
                    <button type="button" class="custom-lang-btn" id="customLangBtn" onclick="toggleLangDropdown(event)" aria-expanded="false" aria-haspopup="true">
                        <i class="fa-solid fa-globe lang-globe-icon"></i>
                        <span class="custom-lang-current" id="customLangCurrent">🇬🇧 English</span>
                        <i class="fa-solid fa-chevron-down lang-arrow-icon"></i>
                    </button>

                    <div class="custom-lang-menu" id="customLangMenu">
                        <div class="lang-menu-item active" data-lang="en" onclick="selectCustomLang('en')">
                            <span class="lang-flag">🇬🇧</span> <span class="lang-name">English</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="hi" onclick="selectCustomLang('hi')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">हिंदी (Hindi)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="gu" onclick="selectCustomLang('gu')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">ગુજરાતી (Gujarati)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="mr" onclick="selectCustomLang('mr')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">मराठी (Marathi)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="ta" onclick="selectCustomLang('ta')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">தமிழ் (Tamil)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="bn" onclick="selectCustomLang('bn')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">বাংলা (Bengali)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="ml" onclick="selectCustomLang('ml')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">മലയാളം (Malayalam)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="kn" onclick="selectCustomLang('kn')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">ಕನ್ನಡ (Kannada)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                        <div class="lang-menu-item" data-lang="pa" onclick="selectCustomLang('pa')">
                            <span class="lang-flag">🇮🇳</span> <span class="lang-name">ਪੰਜਾਬੀ (Punjabi)</span> <i class="fa-solid fa-check lang-check"></i>
                        </div>
                    </div>

                    <!-- Hidden native select for translation engine handler -->
                    <select class="lang-select-box" id="langSelectBox" onchange="onLangSelectChange(this)" style="display:none !important;" aria-label="Select Language">
                        <option value="en" selected>English</option>
                        <option value="hi">Hindi</option>
                        <option value="gu">Gujarati</option>
                        <option value="mr">Marathi</option>
                        <option value="ta">Tamil</option>
                        <option value="bn">Bengali</option>
                        <option value="ml">Malayalam</option>
                        <option value="kn">Kannada</option>
                        <option value="pa">Punjabi</option>
                    </select>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION WITH DYNAMIC MOVING VISUALS -->
    <section class="hero-section" style="position: relative; overflow: hidden; padding: 4.5rem 1.5rem;">
        <!-- DYNAMIC AMBIENT GLOW MESH ORBS -->
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>

        <div style="max-width: 1280px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3.5rem; align-items: center; position: relative; z-index: 2;">
            <div>
                <!-- LIVE ANIMATED PRIVACY BADGE -->
                <div class="hero-badge hero-badge-animated">
                    <span class="live-dot-pulse"></span>
                    <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> Protect Your Vehicle & Personal Privacy
                </div>

                <!-- DYNAMIC ANIMATED HEADLINE WITH CYCLING TEXT -->
                <h1 class="hero-title">
                    <span id="heroTitleMain">Let Anyone Reach You With</span>
                    <span id="heroDynamicWord" class="gradient-text hero-dynamic-word">a Quick QR Scan ⚡</span>
                </h1>

                <p class="hero-subtitle">
                    A simple scan lets anyone contact you instantly. Get real-time WhatsApp alerts, 4-option emergency bot reports, and direct calls if your vehicle needs attention — without revealing your personal phone number.
                </p>

                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="#contact" class="btn btn-primary btn-glow btn-hero-pulse" style="padding: 1.1rem 2rem; font-size: 1.05rem; border-radius: var(--radius-lg); font-weight: 700;">
                        <i class="fa-solid fa-phone-volume"></i> Get Smart QR Tag Now
                    </a>
                    <a href="https://wa.me/919876543210?text=Hi%20Vehicle%20Sampark!%20I%20want%20to%20get%20smart%20vehicle%20QR%20tags" target="_blank" class="btn btn-secondary" style="padding: 1.1rem 2rem; font-size: 1.05rem; color: #ea580c; border-color: #ffedd5; border-radius: var(--radius-lg); font-weight: 700;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Chat on WhatsApp
                    </a>
                </div>

                <!-- TRUST METRICS STRIP -->
                <div class="hero-trust-strip">
                    <div class="trust-item">
                        <div class="trust-val color-emerald">100% Private</div>
                        <div class="trust-lbl">No Phone Exposed</div>
                    </div>
                    <div class="trust-item">
                        <div class="trust-val color-orange">Instant Relay</div>
                        <div class="trust-lbl">Call & WhatsApp Bot</div>
                    </div>
                    <div class="trust-item">
                        <div class="trust-val color-slate">Smart Badge</div>
                        <div class="trust-lbl">Center Logo Tag Card</div>
                    </div>
                </div>
            </div>

            <!-- HERO PERFECTLY ALIGNED DYNAMIC SCANNER MOCKUP -->
            <div style="text-align: center; position: relative;">
                <div class="hero-scanner-container">
                    <!-- FLOATING BADGE 1: TOP LEFT -->
                    <div class="floating-hero-badge float-top-left">
                        <i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% Phone Privacy
                    </div>

                    <!-- FLOATING BADGE 2: TOP RIGHT -->
                    <div class="floating-hero-badge float-top-right">
                        <i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-Sec Connect
                    </div>

                    <!-- SONAR PING BACKDROP -->
                    <div class="hero-sonar-ping"></div>

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
                        <div style="display: flex; align-items: center; gap: 0.65rem; justify-content: center;">
                            <span class="status-dot live-dot-pulse" id="liveHeroDot"></span>
                            <span id="liveHeroMsg">Scanning Vehicle Tag QRC-SAMPARK-01...</span>
                        </div>
                        
                        <div class="action-pills-live" id="liveHeroPills">
                            <span class="badge badge-submitted" style="background: #10b981; color: #ffffff; padding: 4px 10px;">
                                <i class="fa-solid fa-phone-volume"></i> Call Owner
                            </span>
                            <span class="badge" style="background: #f97316; color: #ffffff; padding: 4px 10px;">
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
    <section id="how-it-works" style="padding: 5.5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Scan To Call & WhatsApp</span>
                <h2 class="section-title">How Vehicle Sampark Protects You in 3 Easy Steps</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
                <div class="step-card hover-lift-card">
                    <div class="step-num-badge" style="background: #ecfdf5; color: var(--primary); border: 2px solid #a7f3d0;">
                        <i class="fa-solid fa-qrcode"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">1. Scan the QR Code</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.65;">
                        Anyone can scan the Vehicle Sampark sticker on your windshield using any smartphone camera — zero app download required.
                    </p>
                </div>

                <div class="step-card hover-lift-card">
                    <div class="step-num-badge" style="background: #fff7ed; color: var(--accent-orange); border: 2px solid #ffedd5;">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">2. Connect Instantly</h3>
                    <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.65;">
                        Call the car owner directly or select WhatsApp emergency bot options without exposing personal phone numbers.
                    </p>
                </div>

                <div class="step-card hover-lift-card">
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
                <div class="story-card scene-card-visual">
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
                <div class="story-card scene-card-visual">
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
                <div class="story-card scene-card-visual">
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
                <div class="story-card scene-card-visual">
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
    <section id="hazards" style="padding: 5.5rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 1240px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Real-World Protection</span>
                <h2 class="section-title">Common Vehicle Emergencies & Services Solved</h2>
            </div>

            <div class="hazard-grid">
                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-ban"></i></div>
                    <h4 class="hazard-title">Prevent Scratching & Vandalism</h4>
                    <p class="hazard-desc">
                        Blocked someone in a tight parking spot? They can WhatsApp or call you instantly instead of scratching your car or getting frustrated.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-truck-pickup"></i></div>
                    <h4 class="hazard-title">Avoid Towing & Expensive Fines</h4>
                    <p class="hazard-desc">
                        Traffic police or neighbors can scan your sticker to warn you to move your car before calling a tow truck or issuing a fine.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-soap"></i></div>
                    <h4 class="hazard-title">Doorstep Cleaning Service</h4>
                    <p class="hazard-desc">
                        Request professional doorstep car washing, eco waterless cleaning, interior detailing, and polishing right at your parking spot with one tap.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-wrench"></i></div>
                    <h4 class="hazard-title">Garage & Mechanic Solution</h4>
                    <p class="hazard-desc">
                        Instant access to nearby verified garages, roadside mechanics, flat tyre puncture repair, battery jumpstart, and emergency towing assistance.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-battery-quarter"></i></div>
                    <h4 class="hazard-title">Battery Drain & Headlights Left ON</h4>
                    <p class="hazard-desc">
                        Left your headlights or cabin lights ON? Good Samaritans can warn you before your battery completely dies.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-cloud-showers-heavy"></i></div>
                    <h4 class="hazard-title">Windows Left Open & Theft Prevention</h4>
                    <p class="hazard-desc">
                        Left your window down before rain or theft? Neighbors can scan your tag to alert you before rain enters or thieves strike.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h4 class="hazard-title">Critical Emergencies & Accidents</h4>
                    <p class="hazard-desc">
                        In urgent crash situations, bystanders scan the tag to instantly alert your family with emergency reports when you can't speak.
                    </p>
                </div>

                <div class="hazard-card hazard-box-interactive">
                    <div class="hazard-icon-box icon-box-green"><i class="fa-solid fa-paw"></i></div>
                    <h4 class="hazard-title">Fluid Leaks & Animal Safety</h4>
                    <p class="hazard-desc">
                        Oil leaks, smoke, or a stray animal trapped under your car engine can be reported immediately by bystanders.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- WHY VEHICLE SAMPARK VS TRADITIONAL STICKERS SHOWCASE -->
    <section id="why-us" style="padding: 5.5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1100px; margin: 0 auto;">
            <div class="section-header">
                <span class="section-tag">Privacy Protection Comparison</span>
                <h2 class="section-title">Vehicle Sampark vs Traditional Paper Stickers</h2>
            </div>

            <div class="compare-showcase-grid">
                <!-- TRADITIONAL PAPER STICKER (OLD WAY) -->
                <div class="compare-box-item compare-box-old">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #e11d48; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">
                        ❌ Traditional Method
                    </div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 800; color: #9f1239; margin-bottom: 1.25rem;">
                        Paper Mobile Number Sticker
                    </h3>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.9rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #881337; font-size: 0.92rem; font-weight: 600;">
                            <i class="fa-solid fa-xmark" style="color: #e11d48; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>Personal phone number exposed to every stranger & spammer.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #881337; font-size: 0.92rem; font-weight: 600;">
                            <i class="fa-solid fa-xmark" style="color: #e11d48; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>No automated WhatsApp bot alerts or emergency classifications.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #881337; font-size: 0.92rem; font-weight: 600;">
                            <i class="fa-solid fa-xmark" style="color: #e11d48; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>Cannot update phone number without buying a new sticker.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #881337; font-size: 0.92rem; font-weight: 600;">
                            <i class="fa-solid fa-xmark" style="color: #e11d48; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>Paper fades, tears, or peels off easily in rain.</span>
                        </li>
                    </ul>
                </div>

                <!-- VEHICLE SAMPARK SMART TAG (NEW WAY) -->
                <div class="compare-box-item compare-box-new">
                    <div style="font-size: 0.85rem; font-weight: 800; color: #047857; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                        <span>✅ Vehicle Sampark</span>
                        <span style="background: #10b981; color: #ffffff; padding: 2px 8px; border-radius: 12px; font-size: 0.72rem;">RECOMMENDED</span>
                    </div>
                    <h3 style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 800; color: #065f46; margin-bottom: 1.25rem;">
                        Smart QR Privacy Tag
                    </h3>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.9rem;">
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #064e3b; font-size: 0.92rem; font-weight: 700;">
                            <i class="fa-solid fa-check" style="color: #10b981; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>100% Phone Number Privacy — Zero stranger exposure.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #064e3b; font-size: 0.92rem; font-weight: 700;">
                            <i class="fa-solid fa-check" style="color: #10b981; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>4-Option Emergency WhatsApp Bot Relay & Direct Call line.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #064e3b; font-size: 0.92rem; font-weight: 700;">
                            <i class="fa-solid fa-check" style="color: #10b981; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>Instant online phone number update anytime via website.</span>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 0.65rem; color: #064e3b; font-size: 0.92rem; font-weight: 700;">
                            <i class="fa-solid fa-check" style="color: #10b981; font-size: 1.1rem; margin-top: 2px;"></i>
                            <span>Durable, UV-resistant tag card pasted inside windshield.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FREQUENTLY ASKED QUESTIONS (FAQ) -->
    <section id="faq" style="padding: 5.5rem 1.5rem; background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <div style="max-width: 880px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 3rem;">
                <span class="section-tag">Got Questions?</span>
                <h2 class="section-title">Frequently Asked Questions</h2>
            </div>

            <div class="faq-item faq-card-modern open">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How does Vehicle Sampark protect my personal phone number?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    When someone scans your Vehicle Sampark tag, they see direct "Call Owner" or "Chat on WhatsApp" buttons. The call and WhatsApp messages are routed through our secure platform, so your real phone number and identity are never exposed on the physical sticker.
                </div>
            </div>

            <div class="faq-item faq-card-modern">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Does a person scanning my QR sticker need to download an app?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    No! Anyone with a regular smartphone camera, Google Lens, or default QR scanner can scan your tag and connect instantly. Zero app downloads required.
                </div>
            </div>

            <div class="faq-item faq-card-modern">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>How do Doorstep Cleaning and Garage Solutions work?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Vehicle Sampark links your vehicle tag with verified doorstep car cleaning partners and nearby emergency garage/breakdown mechanic services, giving you instant access to maintenance and roadside support.
                </div>
            </div>

            <div class="faq-item faq-card-modern">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Can I change my registered phone number or vehicle details later?</span>
                    <i class="fa-solid fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Yes! You can update your mobile number, WhatsApp number, or car details anytime without replacing your physical tag sticker.
                </div>
            </div>

            <div class="faq-item faq-card-modern">
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
    <section id="contact" style="padding: 5.5rem 1.5rem; background: #ffffff;">
        <div style="max-width: 1060px; margin: 0 auto;">
            <div class="section-header" style="margin-bottom: 3rem;">
                <span class="section-tag">Get In Touch</span>
                <h2 class="section-title">Order Smart QR Tags & Inquiries</h2>
                <p style="color: var(--text-muted); font-size: 0.98rem; margin-top: 0.4rem;">No online payment required. Send us an inquiry or contact us directly via Call or WhatsApp!</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.25rem;">
                <!-- Contact Direct Info -->
                <div>
                    <div class="content-card" style="padding: 2.25rem 2rem; height: 100%; display: flex; flex-direction: column; justify-content: space-between; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.25); background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); box-shadow: 0 12px 35px rgba(0,0,0,0.05); position: relative; overflow: hidden;">
                        
                        <!-- Subtle Emerald Glow Backdrop Accent -->
                        <div style="position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(0,0,0,0) 70%); pointer-events: none;"></div>

                        <div>
                            <!-- Header with Live 24/7 Support Dot -->
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.75rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 1rem;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                                    <span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                        <i class="fa-solid fa-headset"></i>
                                    </span>
                                    Direct Contact Channels
                                </h3>

                                <span style="background: #ecfdf5; color: #047857; font-size: 0.78rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 6px;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span>
                                    Live 24/7 Support
                                </span>
                            </div>

                            <!-- VISUAL CHANNEL 1: DIRECT VOICE CALL -->
                            <div style="margin-bottom: 1.35rem;">
                                <a href="tel:+919876543210" class="contact-channel-box" style="display: block; text-decoration: none; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; padding: 1.25rem 1.4rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3); transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.2); position: relative; overflow: hidden;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; backdrop-filter: blur(4px); flex-shrink: 0; border: 1px solid rgba(255,255,255,0.3);">
                                                <i class="fa-solid fa-phone-volume" style="color: #ffffff;"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.9; margin-bottom: 2px;">
                                                    <i class="fa-solid fa-bolt" style="color: #fef08a;"></i> Instant Call Line
                                                </div>
                                                <div style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; letter-spacing: 0.2px;">
                                                    +91 98765 43210
                                                </div>
                                            </div>
                                        </div>
                                        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- VISUAL CHANNEL 2: OFFICIAL WHATSAPP BOT -->
                            <div style="margin-bottom: 1.75rem;">
                                <a href="https://wa.me/919876543210?text=Hi%20Vehicle%20Sampark!%20I%20want%20to%20order%20smart%20vehicle%20tags" target="_blank" class="contact-channel-box" style="display: block; text-decoration: none; background: linear-gradient(135deg, #ea580c 0%, #f97316 100%); color: #ffffff; padding: 1.25rem 1.4rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(249, 115, 22, 0.3); transition: all 0.3s ease; border: 1px solid rgba(255, 255, 255, 0.2); position: relative; overflow: hidden;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.55rem; backdrop-filter: blur(4px); flex-shrink: 0; border: 1px solid rgba(255,255,255,0.3);">
                                                <i class="fa-brands fa-whatsapp" style="color: #ffffff;"></i>
                                            </div>
                                            <div>
                                                <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.9; margin-bottom: 2px;">
                                                    <i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-Click WhatsApp Bot
                                                </div>
                                                <div style="font-family: var(--font-heading); font-size: 1.18rem; font-weight: 800;">
                                                    Chat on WhatsApp
                                                </div>
                                            </div>
                                        </div>
                                        <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- VISUAL CHANNEL 3: EMAIL SUPPORT CARD -->
                        <div class="email-support-card" style="background: #0f172a; padding: 1.25rem 1.35rem; border-radius: 16px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; gap: 0.85rem; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 4px 15px rgba(15, 23, 42, 0.12);">
                            <div style="display: flex; align-items: center; gap: 0.85rem; min-width: 0;">
                                <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Email Support Line</div>
                                    <div style="font-size: 0.88rem; font-weight: 700; color: #ffffff; word-break: break-all;">contact@vehiclesampark.com</div>
                                </div>
                            </div>
                            <a href="mailto:contact@vehiclesampark.com" class="email-btn-link" style="color: #34d399; font-size: 0.85rem; padding: 8px 14px; background: rgba(52, 211, 153, 0.12); border-radius: 8px; text-decoration: none; font-weight: 700; border: 1px solid rgba(52, 211, 153, 0.2); text-align: center; white-space: nowrap; flex-shrink: 0;">
                                Send Email
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Landing Page Inquiry Form (Redesigned & Attractive) -->
                <div class="content-card" style="padding: 2.25rem 2rem; border-radius: 20px; border: 1px solid rgba(249, 115, 22, 0.25); background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%); box-shadow: 0 12px 35px rgba(0,0,0,0.05); position: relative; overflow: hidden;">
                    
                    <!-- Subtle Orange Backdrop Accent -->
                    <div style="position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, rgba(0,0,0,0) 70%); pointer-events: none;"></div>

                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid #ffedd5; padding-bottom: 1rem;">
                        <h3 style="font-family: var(--font-heading); font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                <i class="fa-solid fa-paper-plane"></i>
                            </span>
                            Send Us an Inquiry
                        </h3>
                        <span style="font-size: 0.78rem; font-weight: 700; color: #c2410c; background: #fff7ed; padding: 4px 10px; border-radius: 20px; border: 1px solid #ffedd5;">
                            ⚡ Fast Response
                        </span>
                    </div>

                    <div id="contactFormStatus"></div>

                    <form id="landingContactForm" action="contact_handler.php" method="POST">
                        <div class="form-group" style="margin-bottom: 1.15rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                                Full Name <span class="required" style="color: #f43f5e;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="text" name="name" class="form-control" placeholder="e.g. Rahul Sharma" required style="padding-left: 2.6rem; border-radius: 12px; height: 46px;">
                                <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #10b981; font-size: 0.95rem;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.15rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                                Mobile / WhatsApp Number <span class="required" style="color: #f43f5e;">*</span>
                            </label>
                            <div style="position: relative;">
                                <input type="tel" name="mobile" class="form-control" placeholder="+91 98765 43210" required style="padding-left: 2.6rem; border-radius: 12px; height: 46px;">
                                <i class="fa-solid fa-mobile-screen-button" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #f97316; font-size: 1rem;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.15rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                                Email Address <span style="font-weight: normal; color: #94a3b8;">(Optional)</span>
                            </label>
                            <div style="position: relative;">
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" style="padding-left: 2.6rem; border-radius: 12px; height: 46px;">
                                <i class="fa-solid fa-envelope" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #38bdf8; font-size: 0.95rem;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.15rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                                Number of Vehicle Tags Needed
                            </label>
                            <div style="position: relative;">
                                <select name="quantity" class="form-control" style="padding-left: 2.6rem; border-radius: 12px; height: 46px; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394A3B8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem center; background-size: 0.65rem auto;">
                                    <option value="1">🚗 1 Tag (Personal Car/Bike)</option>
                                    <option value="5">👨‍👩‍👧‍👦 5 Tags (Family Fleet)</option>
                                    <option value="10">🏢 10 Tags (Small Fleet)</option>
                                    <option value="50+">🚛 50+ Tags (Commercial Fleet / Business)</option>
                                </select>
                                <i class="fa-solid fa-tags" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #a855f7; font-size: 0.95rem;"></i>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.35rem;">
                            <label class="form-label" style="font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem; display: block;">
                                Delivery Address / Questions <span style="font-weight: normal; color: #94a3b8;">(Optional)</span>
                            </label>
                            <div style="position: relative;">
                                <textarea name="message" class="form-control" rows="3" placeholder="Tell us your city or any questions..." style="padding-left: 2.6rem; border-radius: 12px; padding-top: 0.75rem;"></textarea>
                                <i class="fa-solid fa-location-dot" style="position: absolute; left: 1rem; top: 1rem; color: #f43f5e; font-size: 0.95rem;"></i>
                            </div>
                        </div>

                        <button type="submit" id="btnContactSubmit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.95rem; font-size: 1.05rem; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #f97316, #ea580c); box-shadow: 0 10px 25px rgba(249, 115, 22, 0.35);">
                            <span id="btnContactTxt" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                <i class="fa-solid fa-paper-plane"></i> Submit Inquiry Now
                            </span>
                            <span id="btnContactLoader" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> Sending Inquiry...</span>
                        </button>
                    </form>

                    <div style="font-size: 0.78rem; color: #64748b; text-align: center; margin-top: 1.15rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% Safe & Private. No spam, guaranteed.
                    </div>
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
    <footer style="background: #0f172a; color: #ffffff; padding: 3.5rem 1.5rem 2.5rem 1.5rem; text-align: center; border-top: 1px solid rgba(255,255,255,0.08);">
        <div style="max-width: 1200px; margin: 0 auto;">
            <div style="margin-bottom: 1.25rem;">
                <span class="notranslate" translate="no" style="font-family: var(--font-heading); font-size: 1.75rem; font-weight: 900; color: #ffffff;">
                    Vehicle <span class="notranslate" translate="no" style="color: var(--primary);">Sampark</span>
                </span>
                <div class="notranslate" translate="no" style="font-size: 0.82rem; color: #10b981; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-top: 2px;">
                    Connecting Mobility
                </div>
            </div>
            <p style="color: #94a3b8; font-size: 0.92rem; margin-bottom: 1.75rem; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                Smart Emergency Vehicle Safety & Privacy Tag System &bull; Built with ❤️ for Indian Roads &bull; &copy; <?= date('Y') ?> Vehicle Sampark.
            </p>
            <div style="display: flex; gap: 1.25rem; justify-content: center; font-size: 0.9rem; color: #94a3b8; flex-wrap: wrap;">
                <a href="#how-it-works" style="color: #94a3b8; text-decoration: none; font-weight: 600;">How It Works</a> &bull;
                <a href="#hazards" style="color: #94a3b8; text-decoration: none; font-weight: 600;">Emergency Cases</a> &bull;
                <a href="#why-us" style="color: #94a3b8; text-decoration: none; font-weight: 600;">Why Vehicle Sampark</a> &bull;
                <a href="#faq" style="color: #94a3b8; text-decoration: none; font-weight: 600;">FAQ</a> &bull;
                <a href="#contact" style="color: #94a3b8; text-decoration: none; font-weight: 600;">Contact Us</a>
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
            txt.style.display = 'flex';
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
                        <i class="fa-solid fa-circle-exclamation"></i> ${data.message || 'Something went wrong.'}
                    </div>
                `;
            }
        })
        .catch(err => {
            btn.disabled = false;
            txt.style.display = 'flex';
            loader.style.display = 'none';
            statusDiv.innerHTML = `
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.95rem; border-radius: var(--radius-md); font-size: 0.9rem; margin-bottom: 1.25rem; font-weight: 700;">
                    <i class="fa-solid fa-circle-exclamation"></i> Network error. Please try calling or WhatsApp.
                </div>
            `;
        });
    });
    </script>

    <!-- HIDDEN GOOGLE TRANSLATE ENGINE CONTAINER -->
    <div id="google_translate_element" style="display:none; visibility:hidden; position:absolute; left:-9999px;"></div>

    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,hi,gu,mr,ta,bn,ml,kn,pa',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
    }
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <!-- MAIN APP & TRANSLATION ENGINE SCRIPT -->
    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
