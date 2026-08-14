<?php
// batch_pdf.php - Medium-Width Centered 4-Tags-Per-Page Batch PDF Streamer

require_once __DIR__ . '/vendor/autoload.php';
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/config/database.sample.php')) {
    require_once __DIR__ . '/config/database.sample.php';
}
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/tag_template.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (strpos($_SERVER['REQUEST_URI'] ?? '', 'batch_pdf.php') !== false) {
    $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: admin-qr-batch-pdf' . $qs);
    exit;
}

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
    margin: 12px 16px;
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
/* Medium Width Centered Tag Card Wrapper (4 Tags Per Page) */
.batch-tag-wrapper {
    margin-bottom: 14px;
    page-break-inside: avoid;
    text-align: center;
}
.batch-tag-wrapper .sampark-tag-box {
    margin: 0 auto !important;
    width: 480px !important;
    max-width: 480px !important;
    border-width: 2px !important;
}
.batch-tag-wrapper .tag-left-cell {
    width: 58% !important;
    padding: 10px 14px !important;
}
.batch-tag-wrapper .tag-right-cell {
    width: 42% !important;
    padding: 10px 12px !important;
}
.batch-tag-wrapper .tag-brand-title {
    font-size: 16px !important;
}
.batch-tag-wrapper .tag-main-headline {
    font-size: 17px !important;
    margin-bottom: 8px !important;
    line-height: 1.15 !important;
}
.batch-tag-wrapper .tag-qr-img {
    width: 105px !important;
    height: 105px !important;
}
.batch-tag-wrapper .tag-sub-caption {
    font-size: 8px !important;
    margin-bottom: 6px !important;
}
.batch-tag-wrapper .tag-bottom-notice {
    font-size: 7px !important;
}
.batch-tag-wrapper .tag-panel-footer {
    font-size: 8px !important;
    margin-top: 5px !important;
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
