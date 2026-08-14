<?php
// batches.php - Vehicle Sampark Form Batches Directory (Admin Protected)

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Enforce Admin Authentication
requireAdminLogin();

$pageTitle = 'Vehicle Sampark - Form Batches Directory';

$search = sanitize($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$whereClauses = [];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(batch_name LIKE :search OR form_title LIKE :search OR form_description LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// 1. Get Total Count Across Entire Database for Batches
$countSql = "SELECT COUNT(*) FROM batches $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalItems = (int)$countStmt->fetchColumn();

$totalPages = max(1, ceil($totalItems / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}

// 2. Fetch Paginated 20 Batches
$dataSql = "
    SELECT b.*, 
           (SELECT COUNT(*) FROM qr_codes WHERE batch_id = b.id AND status = 'submitted') AS submitted_qrs
    FROM batches b
    $whereSql
    ORDER BY b.id DESC
    LIMIT $perPage OFFSET $offset
";
$dataStmt = $pdo->prepare($dataSql);
$dataStmt->execute($params);
$batches = $dataStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="content-card">
    <div class="card-header">
        <div class="card-title-group">
            <h2><i class="fa-solid fa-layer-group" style="color: var(--accent-orange);"></i> Form Batches Directory</h2>
            <p>List of all created QR code batches & printable physical sheet downloads (20 items/page)</p>
        </div>

        <div class="filter-controls">
            <!-- Server-Side Search Form -->
            <form action="batches.php" method="GET" class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Search batch name or title..." value="<?= htmlspecialchars($search) ?>">
            </form>
        </div>
    </div>

    <?php if (empty($batches)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-folder-open empty-icon"></i>
            <h3>No Form Batches Found</h3>
            <p><?= $search ? 'No batches matched your search term "' . htmlspecialchars($search) . '".' : 'Click "Generate QR Code" to create your first batch.' ?></p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Batch Name</th>
                        <th>Linked Form Title</th>
                        <th>Total Vehicle Tags</th>
                        <th>Registered Owners</th>
                        <th>Creation Date</th>
                        <th style="text-align: right;">Download Printable Sheet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batches as $batch): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--text-main);">
                                <?= htmlspecialchars($batch['batch_name']) ?>
                            </td>
                            <td style="color: var(--accent-orange); font-weight: 600;">
                                <?= htmlspecialchars($batch['form_title']) ?>
                            </td>
                            <td>
                                <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;">
                                    <?= number_format($batch['total_qrs']) ?> Tags
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-submitted">
                                    <i class="fa-solid fa-check"></i> <?= number_format($batch['submitted_qrs']) ?> / <?= number_format($batch['total_qrs']) ?>
                                </span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">
                                <?= date('M j, Y g:i A', strtotime($batch['created_at'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <div class="action-buttons" style="justify-content: flex-end;">
                                    <!-- Download Printable Physical Tag Sheet PDF -->
                                    <a href="download_batch_pdf.php?batch_id=<?= $batch['id'] ?>" class="btn btn-primary btn-sm btn-glow" title="Download All Physical QR Tags PDF Sheet">
                                        <i class="fa-solid fa-file-pdf"></i> Download Tag Sheet PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 20 Items Per Page Server-Side Pagination Bar -->
        <div class="pagination-bar">
            <div class="pagination-info">
                Showing <strong><?= count($batches) ?></strong> of <strong><?= number_format($totalItems) ?></strong> batches &bull; Page <?= $page ?> of <?= $totalPages ?>
            </div>
            
            <div class="pagination-buttons">
                <?php if ($page > 1): ?>
                    <a href="batches.php?page=<?= $page - 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>

                <span class="pagination-page-num">Page <?= $page ?> / <?= $totalPages ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="batches.php?page=<?= $page + 1 ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="btn btn-secondary btn-sm">
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
