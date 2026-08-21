<?php
// app/Controllers/AuthController.php - Admin Authentication Controller

require_once __DIR__ . '/../Models/AdminModel.php';

class AuthController {
    private $pdo;
    private $adminModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function login() {
        if (isAdminLoggedIn()) {
            header('Location: admin-qr-dashboard');
            exit;
        }

        $error = '';
        $pageTitle = 'Admin Login';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                $error = 'Please enter both username and password.';
            } else {
                $admin = $this->adminModel->getAdminByUsername($username);

                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];

                    header('Location: admin-qr-dashboard');
                    exit;
                } else {
                    $error = 'Invalid username or password.';
                }
            }
        }

        include __DIR__ . '/../Views/auth/login.php';
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();

        header('Location: admin-qr-login');
        exit;
    }

    public function forgotPassword() {
        $error = '';
        $success = '';
        $pageTitle = 'Forgot Password';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            if (empty($email)) {
                $error = 'Please enter your registered email address.';
            } else {
                $admin = $this->adminModel->getAdminByUsername($email);
                if ($admin) {
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $this->adminModel->setResetToken($admin['email'], $token, $expiresAt);
                    $success = 'Password reset instructions have been set. Use token: ' . $token;
                } else {
                    $error = 'Email address not found in system.';
                }
            }
        }

        include __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function resetPassword() {
        $token = sanitize($_GET['token'] ?? '');
        $error = '';
        $success = '';
        $pageTitle = 'Reset Password';

        $admin = $token ? $this->adminModel->getByResetToken($token) : null;

        if (!$admin && !empty($token)) {
            $error = 'Invalid or expired password reset token.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword) || strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->adminModel->updatePassword($admin['id'], $hashedPassword);
                $this->adminModel->setResetToken($admin['email'], null, null);
                $success = 'Password updated successfully! You can now log in.';
            }
        }

        include __DIR__ . '/../Views/auth/reset_password.php';
    }
}
