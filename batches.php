<?php
// batches.php - Vehicle Sampark QR Code Batches & Form Generator Management

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pageTitle = 'Form Batches Management';
include __DIR__ . '/includes/header.php';

// Fetch all batches
$stmt = $pdo->query("
    SELECT b.*, 
           COUNT(q.id) as total_qrs,
           SUM(CASE WHEN q.status = 'submitted' THEN 1 ELSE 0 END) as submitted_qrs,
           SUM(CASE WHEN q.status = 'pending' THEN 1 ELSE 0 END) as pending_qrs
    FROM batches b
    LEFT JOIN qr_codes q ON b.id = q.batch_id
    GROUP BY b.id
    ORDER BY b.id DESC
");
$batches = $stmt->fetchAll();
?>

<div class="page-container">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Form Batches</h1>
            <p class="page-subtitle">Manage generated vehicle QR batches and form configurations</p>
        </div>
        <div>
            <button type="button" id="btnOpenGenerator" class="btn btn-primary btn-glow">
                <i class="fa-solid fa-plus"></i> Generate New QR Batch
            </button>
        </div>
    </div>

    <?php if (empty($batches)): ?>
        <div class="content-card" style="text-align: center; padding: 3rem 1.5rem;">
            <i class="fa-solid fa-qrcode empty-icon"></i>
            <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">No Batches Generated Yet</h3>
            <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 1.5rem auto;">
                Click "Generate New QR Batch" above to create Vehicle QR Tags with fixed vehicle owner fields.
            </p>
            <button type="button" onclick="document.getElementById('btnOpenGenerator').click()" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Generate First Batch
            </button>
        </div>
    <?php else: ?>
        <div class="content-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Batch ID</th>
                            <th>Batch Name & Form Title</th>
                            <th>Total Tags</th>
                            <th>Registered</th>
                            <th>Pending</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($batches as $batch): ?>
                            <tr>
                                <td><span class="code-badge">#BATCH-<?= $batch['id'] ?></span></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($batch['batch_name']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($batch['form_title']) ?></div>
                                </td>
                                <td><strong style="color: var(--primary);"><?= $batch['total_qrs'] ?> Tags</strong></td>
                                <td><span class="badge badge-submitted"><?= $batch['submitted_qrs'] ?> Active</span></td>
                                <td><span class="badge badge-pending"><?= $batch['pending_qrs'] ?> Pending</span></td>
                                <td><?= date('M j, Y g:i A', strtotime($batch['created_at'])) ?></td>
                                <td>
                                    <a href="dashboard.php?batch=<?= $batch['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fa-solid fa-eye"></i> View Tags
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
