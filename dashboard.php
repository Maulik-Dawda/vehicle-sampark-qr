<?php
// dashboard.php - Vehicle Sampark Smart QR Code Admin Dashboard

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$admin = getLoggedInAdmin();
$pageTitle = 'Dashboard';

// Filters & Pagination
$batchFilter = isset($_GET['batch']) ? (int)$_GET['batch'] : 0;
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Fetch Analytics Stats
$stats = [
    'total_qrs' => $pdo->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn(),
    'submitted_qrs' => $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'submitted'")->fetchColumn(),
    'pending_qrs' => $pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'pending'")->fetchColumn(),
    'total_batches' => $pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn(),
    'total_bot_logs' => $pdo->query("SELECT COUNT(*) FROM bot_logs")->fetchColumn(),
];

// Fetch Batches for Dropdown
$batchesStmt = $pdo->query("SELECT id, batch_name, form_title FROM batches ORDER BY id DESC");
$allBatches = $batchesStmt->fetchAll();

// Build Query for QR Codes
$whereClauses = [];
$queryParams = [];

if ($batchFilter > 0) {
    $whereClauses[] = "q.batch_id = :batch_id";
    $queryParams[':batch_id'] = $batchFilter;
}

if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'submitted'])) {
    $whereClauses[] = "q.status = :status";
    $queryParams[':status'] = $statusFilter;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(q.code_number LIKE :search OR b.batch_name LIKE :search OR b.form_title LIKE :search)";
    $queryParams[':search'] = '%' . $searchQuery . '%';
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Count Total Filtered Rows
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM qr_codes q
    JOIN batches b ON q.batch_id = b.id
    $whereSql
");
$countStmt->execute($queryParams);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch Filtered QR Codes
$sql = "
    SELECT q.*, b.batch_name, b.form_title,
           (SELECT COUNT(*) FROM submissions s WHERE s.qr_code_id = q.id) as submission_count
    FROM qr_codes q
    JOIN batches b ON q.batch_id = b.id
    $whereSql
    ORDER BY q.id DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($queryParams);
$qrCodes = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <!-- PAGE HEADER -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-gauge-high" style="color: var(--primary);"></i> Admin Dashboard</h1>
            <p class="page-subtitle">Welcome back, <strong><?= htmlspecialchars($admin['full_name']) ?></strong>! Manage Vehicle QR Tags & Registrations.</p>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <?php if ($batchFilter > 0): ?>
                <a href="batch_pdf.php?batch_id=<?= $batchFilter ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-file-pdf"></i> Download Batch PDF (#<?= $batchFilter ?>)
                </a>
            <?php endif; ?>
            <button type="button" id="btnOpenGenerator" class="btn btn-primary btn-glow">
                <i class="fa-solid fa-plus"></i> Generate QR Code Batch
            </button>
        </div>
    </div>

    <!-- STATS OVERVIEW CARDS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-primary"><i class="fa-solid fa-qrcode"></i></div>
            <div class="stat-number"><?= number_format($stats['total_qrs']) ?></div>
            <div class="stat-label">Total Generated QR Tags</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-success"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-number"><?= number_format($stats['submitted_qrs']) ?></div>
            <div class="stat-label">Active Registered Vehicles</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-warning"><i class="fa-solid fa-clock"></i></div>
            <div class="stat-number"><?= number_format($stats['pending_qrs']) ?></div>
            <div class="stat-label">Unregistered Tags</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-danger"><i class="fa-solid fa-robot"></i></div>
            <div class="stat-number"><?= number_format($stats['total_bot_logs']) ?></div>
            <div class="stat-label">WhatsApp Bot Reports</div>
        </div>
    </div>

    <!-- SEARCH & FILTER TOOLBAR -->
    <div class="content-card mb-4" style="padding: 1.25rem;">
        <form action="dashboard.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 2; min-width: 220px;">
                <input type="text" name="search" class="form-control" placeholder="Search by Code (e.g. QRC-...) or Batch Name" value="<?= htmlspecialchars($searchQuery) ?>">
            </div>

            <div style="flex: 1; min-width: 160px;">
                <select name="batch" class="form-control">
                    <option value="0">All Form Batches</option>
                    <?php foreach ($allBatches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $batchFilter == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['batch_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 140px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending Registration</option>
                    <option value="submitted" <?= $statusFilter === 'submitted' ? 'selected' : '' ?>>Active / Registered</option>
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <?php if (!empty($searchQuery) || $batchFilter > 0 || !empty($statusFilter)): ?>
                    <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- QR CODES DATA TABLE -->
    <div class="content-card">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin: 0;">
                <i class="fa-solid fa-list" style="color: var(--primary);"></i> Vehicle QR Codes Listing (<?= $totalRecords ?> Total)
            </h3>
        </div>

        <?php if (empty($qrCodes)): ?>
            <div style="text-align: center; padding: 3rem 1.5rem;">
                <i class="fa-solid fa-qrcode empty-icon"></i>
                <h4 style="color: var(--text-main); margin-bottom: 0.5rem;">No QR Codes Found</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    No QR codes match your search criteria. Click "Generate QR Code Batch" to create new tags.
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>QR Code Serial</th>
                            <th>Form Batch</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Public Scan URL</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($qrCodes as $qr): ?>
                            <?php $scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($qr['code_number']); ?>
                            <tr>
                                <td>
                                    <span class="code-badge"><?= htmlspecialchars($qr['code_number']) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($qr['batch_name']) ?></div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($qr['form_title']) ?></div>
                                </td>
                                <td>
                                    <?php if ($qr['status'] === 'submitted'): ?>
                                        <span class="badge badge-submitted"><i class="fa-solid fa-check"></i> Registered</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Unregistered</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y g:i A', strtotime($qr['created_at'])) ?></td>
                                <td>
                                    <a href="<?= $scanUrl ?>" target="_blank" class="scan-link">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Public Scanner
                                    </a>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="viewQRCodeModal('<?= htmlspecialchars($qr['code_number']) ?>')">
                                            <i class="fa-solid fa-qrcode"></i> Tag Card
                                        </button>
                                        
                                        <?php if ($qr['status'] === 'submitted'): ?>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="viewSubmissionDetails('<?= htmlspecialchars($qr['code_number']) ?>')">
                                                <i class="fa-solid fa-user"></i> Owner Details
                                            </button>
                                        <?php endif; ?>

                                        <a href="qr.php?code=<?= urlencode($qr['code_number']) ?>&download=1" class="btn btn-secondary btn-sm">
                                            <i class="fa-solid fa-download"></i> Single PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 1rem;">
                    <div style="font-size: 0.88rem; color: var(--text-muted);">
                        Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= $totalRecords ?> total records)
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <?php if ($page > 1): ?>
                            <a href="dashboard.php?page=<?= $page - 1 ?>&search=<?= urlencode($searchQuery) ?>&batch=<?= $batchFilter ?>&status=<?= $statusFilter ?>" class="btn btn-outline btn-sm">
                                &laquo; Previous
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="dashboard.php?page=<?= $page + 1 ?>&search=<?= urlencode($searchQuery) ?>&batch=<?= $batchFilter ?>&status=<?= $statusFilter ?>" class="btn btn-outline btn-sm">
                                Next &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- SIMPLIFIED 1-STEP GENERATOR MODAL -->
<div class="modal-backdrop" id="generatorModal">
    <div class="modal-card" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary);"></i> Generate Vehicle QR Batch</h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 1.25rem;">
                Enter the quantity and batch details. Every generated QR tag comes pre-configured with <strong>Full Name, Mobile, WhatsApp, Emergency Mobile, Car Number (e.g. GJ-03-NL-0104), Car Name, and Car Model</strong>.
            </p>

            <div class="form-group">
                <label class="form-label">Number of QR Tags <span class="required">*</span></label>
                <input type="number" id="qrQuantity" class="form-control" value="10" min="1" max="500" required>
                <span class="form-help">Enter quantity (1 to 500 tags per batch).</span>
            </div>

            <div class="form-group">
                <label class="form-label">Form Title <span class="required">*</span></label>
                <input type="text" id="formTitle" class="form-control" value="Vehicle QR Tag Registration" required>
            </div>

            <div class="form-group">
                <label class="form-label">Form Instructions (Optional)</label>
                <textarea id="formDescription" class="form-control" rows="2" placeholder="Scan QR code to register vehicle owner details..."></textarea>
            </div>

            <div style="background: #f8fafc; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-top: 1rem;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.3rem;">
                    <i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Fixed Vehicle Fields Included:
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4;">
                    Full Name &bull; Mobile Number &bull; WhatsApp Number &bull; Emergency Mobile &bull; Car Number &bull; Car Name &bull; Car Model
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Cancel</button>
            <button type="button" id="btnGeneratorSubmit" class="btn btn-primary btn-glow">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes
            </button>
        </div>
    </div>
</div>

<!-- QR TAG PREVIEW MODAL -->
<div class="modal-backdrop" id="qrViewModal">
    <div class="modal-card" style="max-width: 640px;">
        <div class="modal-header">
            <h3 id="qrCodeTitle">Vehicle Tag Preview</h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body text-center" id="qrImageContainer" style="padding: 1.5rem;">
            <!-- Dynamic Tag HTML renders here -->
        </div>
        <div class="modal-footer" style="justify-content: space-between;">
            <div style="flex: 1; max-width: 320px; display: flex; gap: 0.4rem;">
                <input type="text" id="qrScanUrlInput" class="form-control form-control-sm" readonly>
                <button type="button" class="btn btn-outline btn-sm" onclick="copyScanUrl()"><i class="fa-solid fa-copy"></i></button>
            </div>
            <div>
                <a id="qrDownloadBtn" href="#" class="btn btn-primary btn-sm"><i class="fa-solid fa-download"></i> Download Single PDF Tag</a>
            </div>
        </div>
    </div>
</div>

<!-- OWNER DETAILS VIEW MODAL -->
<div class="modal-backdrop" id="detailsViewModal">
    <div class="modal-card" style="max-width: 580px;">
        <div class="modal-header">
            <h3 id="detailsModalTitle">Registered Owner Details</h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailsModalBody">
            <!-- Dynamic Owner Submission Information -->
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
