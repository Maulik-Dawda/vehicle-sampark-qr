<?php
// batch_pdf.php - 4-Tags-Per-Page Line-by-Line Batch PDF Streamer for Vehicle Sampark

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/tag_template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

requireAdminLogin();

$batchId = (int)($_GET['batch_id'] ?? 0);

if ($batchId <= 0) {
    die("Invalid Batch Identifier");
}

$stmt = $pdo->prepare("SELECT * FROM batches WHERE id = ?");
$stmt->execute([$batchId]);
$batch = $stmt->fetch();

if (!$batch) {
    die("Batch #$batchId not found.");
}

$qrStmt = $pdo->prepare("SELECT code_number FROM qr_codes WHERE batch_id = ? ORDER BY id ASC");
$qrStmt->execute([$batchId]);
$qrCodes = $qrStmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($qrCodes)) {
    die("No QR Codes available in this batch.");
}

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>';
$html .= getVehicleSamparkTagCSS();
$html .= '
@page {
    margin: 12px 15px;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #ffffff;
}
.batch-header-title {
    text-align: center;
    font-size: 11px;
    font-weight: bold;
    color: #475569;
    margin-bottom: 8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #cbd5e1;
}
/* Compact Tag Container for 4 Tags Per A4 Page */
.batch-tag-wrapper {
    margin-bottom: 10px;
    page-break-inside: avoid;
}
.batch-tag-wrapper .sampark-tag-box {
    margin-bottom: 0 !important;
}
.batch-tag-wrapper .tag-left-cell {
    padding: 8px 12px !important;
}
.batch-tag-wrapper .tag-right-cell {
    padding: 8px 10px !important;
}
.batch-tag-wrapper .tag-brand-title {
    font-size: 15px !important;
}
.batch-tag-wrapper .tag-main-headline {
    font-size: 16px !important;
    margin-bottom: 6px !important;
}
.batch-tag-wrapper .tag-qr-img {
    width: 95px !important;
    height: 95px !important;
}
.batch-tag-wrapper .tag-sub-caption {
    font-size: 7.5px !important;
    margin-bottom: 5px !important;
}
.batch-tag-wrapper .tag-bottom-notice {
    font-size: 6.5px !important;
}
.batch-tag-wrapper .tag-panel-footer {
    font-size: 7.5px !important;
    margin-top: 4px !important;
}
.page-break {
    page-break-after: always;
}
</style></head><body>';

$html .= '<div class="batch-header-title">Vehicle Sampark Batch Tags PDF - ' . htmlspecialchars($batch['batch_name']) . ' (' . count($qrCodes) . ' Total Tags &bull; 4 Tags Per Page)</div>';

$total = count($qrCodes);
foreach ($qrCodes as $index => $codeNumber) {
    $html .= '<div class="batch-tag-wrapper">';
    $html .= renderVehicleSamparkTagHTML($codeNumber, $batch['form_title']);
    $html .= '</div>';
    
    // Clean page break after every 4 stacked tags for A4 printing
    if (($index + 1) % 4 === 0 && ($index + 1) < $total) {
        $html .= '<div class="page-break"></div>';
    }
}

$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$cleanBatchName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $batch['batch_name']);
$filename = 'Batch_' . $batchId . '_' . $cleanBatchName . '.pdf';

$dompdf->stream($filename, ['Attachment' => 1]);
exit;
