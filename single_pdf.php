<?php
$theReq = $_SERVER['THE_REQUEST'] ?? '';
$reqUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($theReq, 'single_pdf.php') !== false || strpos($reqUri, 'single_pdf.php') !== false) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
// single_pdf.php - Single Vehicle Tag PDF Streamer & Downloader

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

$codeNumber = sanitize($_GET['code'] ?? '');

if (empty($codeNumber)) {
    die("Missing QR Code identifier");
}

$stmt = $pdo->prepare("
    SELECT q.*, b.form_title 
    FROM qr_codes q 
    JOIN batches b ON q.batch_id = b.id 
    WHERE q.code_number = ?
");
$stmt->execute([$codeNumber]);
$qr = $stmt->fetch();

if (!$qr) {
    die("Vehicle QR Code not found");
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
    text-align: center;
}
.single-tag-wrapper {
    margin: 20px auto;
    width: 480px;
}
.single-tag-wrapper .sampark-tag-box {
    margin: 0 auto !important;
    width: 480px !important;
    max-width: 480px !important;
}
</style></head><body>';

$html .= '<div class="single-tag-wrapper">';
$html .= renderVehicleSamparkTagHTML($codeNumber, $qr['form_title']);
$html .= '</div>';
$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'landscape');
$dompdf->render();

$filename = 'Vehicle_Tag_' . preg_replace('/[^A-Za-z0-9_\-]/', '', $codeNumber) . '.pdf';

$dompdf->stream($filename, ['Attachment' => 1]);
exit;
