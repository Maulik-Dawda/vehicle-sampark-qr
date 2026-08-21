<?php
// app/Views/errors/404.php - 404 Not Found View
http_response_code(404);
$pageTitle = '404 - Page Not Found | Vehicle Sampark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code-badge">404</div>
        <h2 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">Page Not Found</h2>
        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.75rem; line-height: 1.5;">
            The requested page or vehicle QR tag does not exist or may have been removed.
        </p>
        <a href="index.php" class="btn btn-primary btn-glow" style="padding: 0.85rem 1.75rem; border-radius: 12px;">
            <i class="fa-solid fa-house"></i> Return to Homepage
        </a>
    </div>
</body>
</html>
