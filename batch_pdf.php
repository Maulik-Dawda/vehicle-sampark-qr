<?php
// batch_pdf.php - Line-by-Line Multi-Tag Batch PDF Streamer for Vehicle Sampark

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
    margin: 15px;
}
body {
    font-family: Helvetica, Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: #ffffff;
}
.batch-header-title {
    text-align: center;
    font-size: 13px;
    font-weight: bold;
    color: #475569;
    margin-bottom: 12px;
    padding-bottom: 6px;
    border-bottom: 1px solid #cbd5e1;
}
.tag-line-wrapper {
    margin-bottom: 22px;
    page-break-inside: avoid;
}
.page-break {
    page-break-after: always;
}
</style></head><body>';

$html .= '<div class="batch-header-title">Vehicle Sampark Batch Tags PDF - ' . htmlspecialchars($batch['batch_name']) . ' (' . count($qrCodes) . ' Total Tags)</div>';

$total = count($qrCodes);
foreach ($qrCodes as $index => $codeNumber) {
    $html .= '<div class="tag-line-wrapper">';
    $html .= renderVehicleSamparkTagHTML($codeNumber, $batch['form_title']);
    $html .= '</div>';
    
    // Clean page break after every 2 stacked tags for A4 printing
    if (($index + 1) % 2 === 0 && ($index + 1) < $total) {
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
