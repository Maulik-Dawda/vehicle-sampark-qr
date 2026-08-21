<?php
// app/Controllers/DashboardController.php - Admin Dashboard & Profile Controller

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../Models/QRCodeModel.php';
require_once __DIR__ . '/../Models/BatchModel.php';
require_once __DIR__ . '/../Models/AdminModel.php';

class DashboardController {
    private $pdo;
    private $qrModel;
    private $batchModel;
    private $adminModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->qrModel = new QRCodeModel($pdo);
        $this->batchModel = new BatchModel($pdo);
        $this->adminModel = new AdminModel($pdo);
    }

    public function index() {
        requireAdminLogin();
        $admin = getLoggedInAdmin();
        $pageTitle = 'Dashboard';

        $batchFilter = isset($_GET['batch']) ? (int)$_GET['batch'] : 0;
        $statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
        $searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $stats = $this->batchModel->getStats();
        $allBatches = $this->batchModel->getAllBatches();

        $totalRecords = $this->qrModel->countFiltered($batchFilter, $statusFilter, $searchQuery);
        $totalPages = ceil($totalRecords / $limit);

        $qrCodes = $this->qrModel->listFiltered($batchFilter, $statusFilter, $searchQuery, $limit, $offset);

        include __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function profile() {
        requireAdminLogin();
        $admin = getLoggedInAdmin();
        $pageTitle = 'Admin Profile & Security Settings';
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email)) {
                $error = 'Username and email cannot be empty.';
            } else {
                $this->adminModel->updateProfile($admin['id'], $username, $email);
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;

                if (!empty($newPassword)) {
                    if (strlen($newPassword) < 6) {
                        $error = 'New password must be at least 6 characters long.';
                    } elseif ($newPassword !== $confirmPassword) {
                        $error = 'Passwords do not match.';
                    } else {
                        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                        $this->adminModel->updatePassword($admin['id'], $hashed);
                        $success = 'Profile and password updated successfully!';
                    }
                } else {
                    $success = 'Profile settings updated successfully!';
                }
            }

            $admin = $this->adminModel->getAdminById($admin['id']);
        }

        include __DIR__ . '/../Views/admin/profile.php';
    }
}
