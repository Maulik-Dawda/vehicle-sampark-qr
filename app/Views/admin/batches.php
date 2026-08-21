<?php
// app/Views/admin/batches.php - Responsive Admin Batches View
include __DIR__ . '/../layouts/header.php';
?>

<div class="page-container" style="max-width: 1380px; margin: 0 auto;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title"><i class="fa-solid fa-layer-group" style="color: var(--primary);"></i> Form Batches</h1>
            <p class="page-subtitle">Manage generated vehicle QR batches and download full batch PDF sheets</p>
        </div>
        <div>
            <button type="button" id="btnOpenGenerator" class="btn btn-primary btn-glow">
                <i class="fa-solid fa-plus"></i> Generate New QR Batch
            </button>
        </div>
    </div>

    <?php if (empty($allBatches)): ?>
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
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allBatches as $batch): ?>
                            <tr>
                                <td><span class="code-badge">#BATCH-<?= $batch['id'] ?></span></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($batch['batch_name']) ?></div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($batch['form_title']) ?></div>
                                </td>
                                <td><strong style="color: var(--primary);"><?= $batch['total_qrs'] ?> Tags</strong></td>
                                <td><?= date('M j, Y g:i A', strtotime($batch['created_at'])) ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                        <a href="admin-qr-dashboard?batch=<?= $batch['id'] ?>" class="btn btn-outline btn-sm">
                                            <i class="fa-solid fa-eye"></i> View Tags
                                        </a>
                                        <a href="admin-qr-batch-pdf?batch_id=<?= $batch['id'] ?>" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #10b981, #059669);">
                                            <i class="fa-solid fa-file-pdf"></i> Download Batch PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
