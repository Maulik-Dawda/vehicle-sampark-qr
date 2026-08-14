<?php
// batch_pdf.php - Full-Page Edge-to-Edge Batch PDF Streamer (Only Batch # on Top Corner)

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
    margin: 10px 14px;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #ffffff;
}
/* Small Batch Identifier in Top Right Corner */
.batch-corner-id {
    text-align: right;
    font-size: 8px;
    font-weight: bold;
    color: #94a3b8;
    margin-bottom: 6px;
    font-family: monospace;
}
/* Full-Page Tag Card Wrapper (4 Tags Cover Whole A4 Page) */
.batch-tag-wrapper {
    margin-bottom: 12px;
    page-break-inside: avoid;
}
.batch-tag-wrapper .sampark-tag-box {
    margin-bottom: 0 !important;
    max-width: 100% !important;
    border-width: 2px !important;
}
.batch-tag-wrapper .tag-left-cell {
    padding: 12px 16px !important;
}
.batch-tag-wrapper .tag-right-cell {
    padding: 12px 14px !important;
}
.batch-tag-wrapper .tag-brand-title {
    font-size: 18px !important;
}
.batch-tag-wrapper .tag-main-headline {
    font-size: 20px !important;
    margin-bottom: 10px !important;
    line-height: 1.15 !important;
}
.batch-tag-wrapper .tag-qr-img {
    width: 120px !important;
    height: 120px !important;
}
.batch-tag-wrapper .tag-sub-caption {
    font-size: 8.5px !important;
    margin-bottom: 8px !important;
}
.batch-tag-wrapper .tag-bottom-notice {
    font-size: 7.5px !important;
}
.batch-tag-wrapper .tag-panel-footer {
    font-size: 8.5px !important;
    margin-top: 6px !important;
}
.page-break {
    page-break-after: always;
}
</style></head><body>';

$total = count($qrCodes);
foreach ($qrCodes as $index => $codeNumber) {
    // Show tiny batch ID at the top right of each page
    if ($index % 4 === 0) {
        $html .= '<div class="batch-corner-id">#BATCH-' . $batchId . '</div>';
    }

    $html .= '<div class="batch-tag-wrapper">';
    $html .= renderVehicleSamparkTagHTML($codeNumber, $batch['form_title']);
    $html .= '</div>';
    
    // Page break after every 4 tags to fill full A4 page
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
