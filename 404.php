<?php
// 404.php - Vehicle Sampark Modern Enterprise 404 Page Not Found
http_response_code(404);
$pageTitle = '404 - Page Not Found | Vehicle Sampark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    
    <style>
        body {
            background: radial-gradient(circle at 50% 0%, #f8fafc 0%, #edf2f7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            font-family: var(--font-body);
        }
        .error-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 3rem 2.25rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        .error-code-badge {
            font-family: var(--font-heading);
            font-size: 5.5rem;
            font-weight: 900;
            line-height: 1;
            background: linear-gradient(135deg, #10b981 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -2px;
        }
        .error-icon-box {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(249, 115, 22, 0.1);
            color: #f97316;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.25rem auto;
            border: 2px solid rgba(249, 115, 22, 0.25);
        }
    </style>
</head>
<body>

    <div class="error-card">
        <div class="error-icon-box">
            <i class="fa-solid fa-compass-drafting"></i>
        </div>

        <div class="error-code-badge">404</div>

        <h1 style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-bottom: 0.6rem;">
            Page Not Found
        </h1>

        <p style="color: var(--text-muted); font-size: 0.98rem; line-height: 1.65; margin-bottom: 2rem;">
            The page or URL you requested does not exist or direct file access is restricted. Please return to the main landing page.
        </p>

        <div style="display: flex; gap: 0.85rem; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary btn-glow" style="padding: 0.85rem 1.65rem; border-radius: 12px; font-weight: 700;">
                <i class="fa-solid fa-house"></i> Return to Home
            </a>
        </div>

        <div style="margin-top: 2.25rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; font-size: 0.82rem; color: #94a3b8;">
            Vehicle <strong style="color: #10b981;">Sampark</strong> &bull; Connecting Mobility Security
        </div>
    </div>

</body>
</html>
