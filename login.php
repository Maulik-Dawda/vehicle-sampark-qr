<?php
// login.php - Vehicle Sampark Professional Admin Login Interface with Loader & 2FA

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (substr($reqPath, -4) === '.php') {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

// Logout action handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: admin-qr-login');
    exit;
}

// If already logged in & 2FA verified, redirect to Dashboard
if (isAdminLoggedIn()) {
    header('Location: admin-qr-dashboard');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Set pending login session data
                $_SESSION['pending_admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_fullname'] = $admin['full_name'];
                $_SESSION['admin_email'] = $admin['email'];

                // Update last login timestamp
                $upStmt = $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $upStmt->execute([$admin['id']]);

                // Check if Two-Factor Authentication (2FA) is enabled for this admin
                if (!empty($admin['two_factor_enabled']) && $admin['two_factor_enabled'] == 1) {
                    $_SESSION['admin_2fa_passed'] = false;
                    header('Location: verify_2fa.php');
                    exit;
                } else {
                    // Direct login if 2FA disabled
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_2fa_passed'] = true;
                    header('Location: admin-qr-dashboard');
                    exit;
                }
            } else {
                $error = 'Invalid username or password. Please check your credentials.';
            }
        } catch (Exception $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Vehicle Sampark</title>
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom Responsive CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body style="background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 430px;">
    <!-- Logo & Title Header -->
    <div style="text-align: center; margin-bottom: 1.75rem;">
        <img src="assets/images/logo.jpg" alt="Vehicle Sampark Logo" style="height: 64px; border-radius: var(--radius-md); margin-bottom: 0.75rem;" onerror="this.src='assets/images/logo-icon.svg'">
        <h1 style="font-family: var(--font-heading); font-size: 1.8rem; font-weight: 800; color: #0f172a;">
            Vehicle <span style="color: var(--primary);">Sampark</span>
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; font-weight: 500;">Admin Authentication Portal</p>
    </div>

    <!-- Login Form Card -->
    <div class="content-card" style="padding: 2.25rem; border-radius: var(--radius-xl); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);">
        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.2rem;">Sign In to Dashboard</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Enter your credentials to manage vehicle QR codes & batches</p>
        </div>

        <?php if ($error): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 0.85rem 1rem; border-radius: var(--radius-md); font-size: 0.88rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="admin-qr-login" method="POST" id="loginForm">
            <div class="form-group">
                <label class="form-label" for="username">Username or Email</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-user" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="text" name="username" id="username" class="form-control" style="padding-left: 2.6rem;" placeholder="Enter username or email" value="<?= htmlspecialchars($username) ?>" required autofocus>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.35rem;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                    <a href="forgot_password.php" style="font-size: 0.82rem; font-weight: 600; color: var(--primary); text-decoration: none;">Forgot password?</a>
                </div>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem;"></i>
                    <input type="password" name="password" id="password" class="form-control" style="padding-left: 2.6rem; padding-right: 2.6rem;" placeholder="••••••••" required>
                    <button type="button" id="togglePassword" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.9rem;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div style="margin-top: 1.75rem;">
                <button type="submit" id="btnLoginSubmit" class="btn btn-primary btn-glow" style="width: 100%; padding: 0.85rem; font-size: 1rem;">
                    <span id="btnText"><i class="fa-solid fa-right-to-bracket"></i> Sign In to Dashboard</span>
                    <span id="btnLoader" style="display: none;"><i class="fa-solid fa-circle-notch fa-spin"></i> Authenticating...</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Footer Copy -->
    <div style="text-align: center; margin-top: 1.75rem; font-size: 0.82rem; color: var(--text-muted);">
        &copy; <?= date('Y') ?> <strong style="color: var(--primary);">Vehicle Sampark</strong> &bull; Secured with 2FA Protection
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Interactive Button Loader on Login Submission
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnLoginSubmit');
    const txt = document.getElementById('btnText');
    const loader = document.getElementById('btnLoader');
    btn.disabled = true;
    txt.style.display = 'none';
    loader.style.display = 'inline-block';
});
</script>
</body>
</html>
