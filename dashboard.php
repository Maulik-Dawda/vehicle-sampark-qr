<?php
// dashboard.php - Vehicle Sampark QR Codes Admin Dashboard (Protected)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Enforce Admin Authentication
requireAdminLogin();

$pageTitle = 'Vehicle Sampark - Admin Dashboard';

// Handle Search and Filter Input
$search = sanitize($_GET['search'] ?? '');
$filter = sanitize($_GET['filter'] ?? 'all');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build Dynamic SQL Query with Parameters
$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(q.code_number LIKE :search OR b.form_title LIKE :search OR b.batch_name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($filter === 'pending') {
    $whereClauses[] = "q.status = 'pending'";
} elseif ($filter === 'submitted') {
    $whereClauses[] = "q.status = 'submitted'";
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 1. Get Total Items Count Across Entire Database for Pagination
$countSql = "
    SELECT COUNT(*) 
    FROM qr_codes q
    JOIN batches b ON q.batch_id = b.id
    $whereSql
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();

$totalPages = max(1, ceil($totalItems / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// 2. Fetch Paginated 20 Rows for Current Page
$dataSql = "
    SELECT q.*, b.form_title, b.batch_name
    FROM qr_codes q
    JOIN batches b ON q.batch_id = b.id
    $whereSql
    ORDER BY q.id DESC
    LIMIT $perPage OFFSET $offset
";
$dataStmt = $pdo->prepare($dataSql);
$dataStmt->execute($params);
$qrCodes = $dataStmt->fetchAll();

// Overall Stats for Top Cards
$totalQrsCount = (int)$pdo->query("SELECT COUNT(*) FROM qr_codes")->fetchColumn();
$pendingQrsCount = (int)$pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'pending'")->fetchColumn();
$submittedQrsCount = (int)$pdo->query("SELECT COUNT(*) FROM qr_codes WHERE status = 'submitted'")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<!-- Statistics Overview -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-qrcode"></i>
        </div>
        <div class="stat-info">
            <h4>Total Vehicle Tags</h4>
            <div class="stat-number"><?= number_format($totalQrsCount) ?></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon emerald">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="stat-info">
            <h4>Registered Owners</h4>
            <div class="stat-number"><?= number_format($submittedQrsCount) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div class="stat-info">
            <h4>Unclaimed Tags</h4>
            <div class="stat-number"><?= number_format($pendingQrsCount) ?></div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="content-card">
    <div class="card-header">
        <div class="card-title-group">
            <h2><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Vehicle QR Codes Directory</h2>
            <p>Showing 20 items per page with database-wide search filter</p>
        </div>

        <div class="filter-controls">
            <!-- Server-Side Database Search Form -->
            <form action="dashboard.php" method="GET" class="search-box" id="searchForm">
                <?php if ($filter !== 'all'): ?>
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <?php endif; ?>
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Search code, form, or batch..." value="<?= htmlspecialchars($search) ?>">
            </form>

            <div class="tab-group">
                <a href="dashboard.php?filter=all<?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= ($filter === 'all') ? 'active' : '' ?>">All</a>
                <a href="dashboard.php?filter=submitted<?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= ($filter === 'submitted') ? 'active' : '' ?>">Registered</a>
                <a href="dashboard.php?filter=pending<?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= ($filter === 'pending') ? 'active' : '' ?>">Unclaimed</a>
            </div>
        </div>
    </div>

    <?php if (empty($qrCodes)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-open empty-icon"></i>
            <h3>No QR codes found</h3>
            <p><?= $search ? 'No results matched your search term "' . htmlspecialchars($search) . '".' : 'Click "Generate QR Code" to create your first batch.' ?></p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>QR Code Serial #</th>
                        <th>Linked Form Title</th>
                        <th>Batch Name</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($qrCodes as $qr): ?>
                        <tr>
                            <td>
                                <span class="code-badge"><?= htmlspecialchars($qr['code_number']) ?></span>
                            </td>
                            <td style="font-weight: 600; color: var(--text-main);">
                                <?= htmlspecialchars($qr['form_title']) ?>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?= htmlspecialchars($qr['batch_name']) ?>
                            </td>
                            <td>
                                <?php if ($qr['status'] === 'submitted'): ?>
                                    <span class="badge badge-submitted">
                                        <i class="fa-solid fa-check-circle"></i> Registered
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-pending">
                                        <i class="fa-solid fa-clock"></i> Unclaimed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                <?= $qr['submitted_at'] ? date('M j, Y g:i A', strtotime($qr['submitted_at'])) : '&mdash;' ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-buttons" style="justify-content: flex-end;">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="viewQRCodeModal('<?= htmlspecialchars($qr['code_number']) ?>')" title="View QR Tag">
                                        <i class="fa-solid fa-qrcode"></i> Tag
                                    </button>

                                    <?php if ($qr['status'] === 'submitted'): ?>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="viewSubmissionDetails('<?= htmlspecialchars($qr['code_number']) ?>')" title="View Owner Details">
                                            <i class="fa-solid fa-eye"></i> Details
                                        </button>
                                        <a href="download_pdf.php?code=<?= urlencode($qr['code_number']) ?>" class="btn btn-outline btn-sm" title="Download PDF Report">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </a>
                                    <?php else: ?>
                                        <a href="scan.php?code=<?= urlencode($qr['code_number']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Open Scan Page">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Register
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-bar">
            <div class="pagination-info">
                Showing <strong><?= count($qrCodes) ?></strong> of <strong><?= number_format($totalItems) ?></strong> items &bull; Page <?= $page ?> of <?= $totalPages ?>
            </div>
            
            <div class="pagination-buttons">
                <?php if ($page > 1): ?>
                    <a href="dashboard.php?page=<?= $page - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $filter !== 'all' ? '&filter='.urlencode($filter) : '' ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <span class="pagination-page-num">Page <?= $page ?> / <?= $totalPages ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="dashboard.php?page=<?= $page + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?><?= $filter !== 'all' ? '&filter='.urlencode($filter) : '' ?>" class="btn btn-secondary btn-sm">
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal 1: Generator & Form Builder Wizard -->
<div class="modal-backdrop" id="generatorModal">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <h3><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary);"></i> Generate QR Codes & Form Builder</h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div id="wizardStep1">
                <div class="form-group" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); border: 1px solid #e2e8f0; margin-bottom: 1.25rem;">
                    <label class="form-label" style="margin-bottom: 0;">How many QR Codes do you want to generate? <span class="required">*</span></label>
                    <input type="number" id="qrQuantity" class="form-control" value="5" min="1" max="500" style="width: 110px;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">Each generated QR Code will get a unique serial number.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Form Title <span class="required">*</span></label>
                    <input type="text" id="formTitle" class="form-control" value="Vehicle QR Tag Registration" placeholder="e.g. Vehicle Contact Tag">
                </div>

                <div class="form-group">
                    <label class="form-label">Form Description / Instructions (Optional)</label>
                    <textarea id="formDescription" class="form-control" placeholder="Optional instructions for vehicle owner registration...">Scan QR code to register vehicle owner details for direct call and WhatsApp contact.</textarea>
                </div>
            </div>

            <div id="wizardStep2" style="display: none;">
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.75rem; font-weight: 600;">
                    Form Fields for Vehicle Registration (Customizable):
                </p>

                <div class="field-palette">
                    <button type="button" class="btn-field-add" data-type="text"><i class="fa-solid fa-font"></i> Textfield</button>
                    <button type="button" class="btn-field-add" data-type="mobile"><i class="fa-solid fa-phone"></i> Mobile Number</button>
                    <button type="button" class="btn-field-add" data-type="email"><i class="fa-solid fa-envelope"></i> Email</button>
                    <button type="button" class="btn-field-add" data-type="radio"><i class="fa-solid fa-circle-dot"></i> Radio Button</button>
                    <button type="button" class="btn-field-add" data-type="dropdown"><i class="fa-solid fa-caret-down"></i> Dropdown</button>
                    <button type="button" class="btn-field-add" data-type="checkbox"><i class="fa-solid fa-square-check"></i> Checkbox</button>
                    <button type="button" class="btn-field-add" data-type="textarea"><i class="fa-solid fa-align-left"></i> Textarea</button>
                    <button type="button" class="btn-field-add" data-type="image"><i class="fa-solid fa-image"></i> Upload Image</button>
                    <button type="button" class="btn-field-add" data-type="file"><i class="fa-solid fa-file-arrow-up"></i> Upload File</button>
                    <button type="button" class="btn-field-add" data-type="date"><i class="fa-solid fa-calendar-days"></i> Date Picker</button>
                </div>

                <div id="builderFieldsContainer" class="builder-fields-list"></div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Cancel</button>
            <button type="button" class="btn btn-secondary" id="btnWizardBack" style="display: none;">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <button type="button" class="btn btn-primary" id="btnWizardNext">
                Next: Build Form Fields <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" class="btn btn-primary btn-glow" id="btnWizardSubmit" style="display: none;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes
            </button>
        </div>
    </div>
</div>

<!-- Modal 2: View QR Code Tag Card -->
<div class="modal-backdrop" id="qrViewModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-qrcode" style="color: var(--primary);"></i> <span id="qrCodeTitle">Vehicle Tag</span></h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <div id="qrImageContainer" style="margin-bottom: 1.5rem;">
                <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
            </div>
            
            <div class="form-group" style="text-align: left;">
                <label class="form-label">Public Scanner URL</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="qrScanUrlInput" class="form-control" readonly>
                    <button type="button" class="btn btn-secondary" onclick="copyScanUrl()"><i class="fa-solid fa-copy"></i></button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
            <a href="#" id="qrDownloadBtn" class="btn btn-primary">
                <i class="fa-solid fa-download"></i> Download PNG
            </a>
        </div>
    </div>
</div>

<!-- Modal 3: View Registered Owner Details -->
<div class="modal-backdrop" id="detailsViewModal">
    <div class="modal-card modal-lg">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clipboard-check" style="color: var(--primary);"></i> <span id="detailsModalTitle">Registered Details</span></h3>
            <button type="button" class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailsModalBody">
            <div style="text-align: center; padding: 2rem;">
                <i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
