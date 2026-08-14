<?php
// qr.php - Authentic Logo-Embedded PNG QR Code Streamer & Downloader

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$codeNumber = sanitize($_GET['code'] ?? '');

if (empty($codeNumber)) {
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing QR code identifier';
    exit;
}

$scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber);

$options = new QROptions();
$options->outputType = QRCode::OUTPUT_IMAGE_PNG;
$options->eccLevel = QRCode::ECC_H; // High error correction for center logo scannability
$options->scale = 8;
$options->imageBase64 = false;

try {
    $qrcode = new QRCode($options);
    $rawPng = $qrcode->render($scanUrl);
    
    // Embed Vehicle Sampark Logo in center
    $finalPng = embedLogoInQRCode($rawPng);

    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($finalPng));

    if (isset($_GET['download']) && $_GET['download'] == '1') {
        header('Content-Disposition: attachment; filename="' . $codeNumber . '.png"');
    } else {
        header('Content-Disposition: inline; filename="' . $codeNumber . '.png"');
    }

    echo $finalPng;
    exit;

} catch (\Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'QR Generation Error: ' . $e->getMessage();
    exit;
}
