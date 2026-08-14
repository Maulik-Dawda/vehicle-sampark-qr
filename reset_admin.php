<?php
// reset_admin.php - One-Click Admin Credentials Reset & Verifier

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}

require_once __DIR__ . '/includes/functions.php';

$message = '';
$error = '';

try {
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Ensure admins table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            full_name TEXT DEFAULT 'Administrator',
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Update password
        $up = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
        $up->execute([$hash, $username]);
        $message = "Admin password successfully reset to 'admin123' for user 'admin'!";
    } else {
        // Create admin user
        $ins = $pdo->prepare("
            INSERT INTO admins (username, email, password_hash, full_name)
            VALUES (?, 'admin@vehiclesampark.com', ?, 'System Administrator')
        ");
        $ins->execute([$username, $hash]);
        $message = "Created new default Admin account (Username: 'admin' | Password: 'admin123')!";
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

$pageTitle = 'Admin Credentials Reset | Vehicle Sampark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            font-family: 'Inter', sans-serif;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 1rem;">Admin Credentials Verifier</h2>
        
        <?php if ($message): ?>
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 1rem; border-radius: 8px; font-weight: 700; margin-bottom: 1.5rem;">
                ✅ <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 8px; font-weight: 700; margin-bottom: 1.5rem;">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div style="background: #f1f5f9; padding: 1.25rem; border-radius: 8px; text-align: left; margin-bottom: 1.5rem; font-size: 0.95rem;">
            <div><strong>Username:</strong> <code>admin</code></div>
            <div style="margin-top: 0.5rem;"><strong>Password:</strong> <code>admin123</code></div>
        </div>

        <a href="login.php" class="btn btn-primary" style="display: block; width: 100%; padding: 0.9rem; text-decoration: none; border-radius: 8px;">
            Go to Admin Login Page &rarr;
        </a>
    </div>
</body>
</html>
