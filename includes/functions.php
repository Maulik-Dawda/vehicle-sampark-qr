<?php
// includes/functions.php - Vehicle Sampark Helper Functions & Center-Logo QR Generator

require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Returns dynamic App Base URL without trailing slashes or dots
 */
function getAppBaseUrl() {
    $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $script = str_replace('\\', '/', $script);
    
    if ($script === '.' || $script === '/' || $script === '\\') {
        $script = '';
    } else {
        $script = '/' . trim($script, '/.');
    }
    
    return "$scheme://$host" . $script;
}

/**
 * Generates unique QR Code alphanumeric identifier (e.g. QRC-A7F9-82XB)
 */
function generateUniqueCode($prefix = 'QRC') {
    $bytes = random_bytes(4);
    $hex = strtoupper(bin2hex($bytes));
    return $prefix . '-' . substr($hex, 0, 4) . '-' . substr($hex, 4, 4);
}

/**
 * Embeds Vehicle Sampark Logo into center of QR code GD image
 */
function embedLogoInQRCode($qrPngBytes) {
    if (!extension_loaded('gd')) {
        return $qrPngBytes;
    }

    $logoPath = __DIR__ . '/../assets/images/logo.jpg';
    if (!file_exists($logoPath)) {
        return $qrPngBytes;
    }

    try {
        $qrImg = @imagecreatefromstring($qrPngBytes);
        $logoImg = @imagecreatefromstring(file_get_contents($logoPath));

        if (!$qrImg || !$logoImg) {
            return $qrPngBytes;
        }

        $qrW = imagesx($qrImg);
        $qrH = imagesy($qrImg);
        $logoW = imagesx($logoImg);
        $logoH = imagesy($logoImg);

        // Logo size: 22% of QR width for high camera scannability
        $targetSize = (int)($qrW * 0.22);
        $targetX = (int)(($qrW - $targetSize) / 2);
        $targetY = (int)(($qrH - $targetSize) / 2);

        // White background box behind center logo
        $bgMargin = 3;
        $white = imagecolorallocate($qrImg, 255, 255, 255);
        imagefilledrectangle(
            $qrImg, 
            $targetX - $bgMargin, 
            $targetY - $bgMargin, 
            $targetX + $targetSize + $bgMargin, 
            $targetY + $targetSize + $bgMargin, 
            $white
        );

        // Copy logo into QR center
        imagecopyresampled(
            $qrImg, $logoImg, 
            $targetX, $targetY, 
            0, 0, 
            $targetSize, $targetSize, 
            $logoW, $logoH
        );

        ob_start();
        imagepng($qrImg);
        $finalPng = ob_get_clean();

        imagedestroy($qrImg);
        imagedestroy($logoImg);

        return $finalPng;

    } catch (\Throwable $e) {
        return $qrPngBytes;
    }
}

/**
 * Returns Base64 PNG data URI string with center embedded logo
 */
function getQRCodeBase64($codeNumber) {
    $scanUrl = getAppBaseUrl() . '/scan.php?code=' . urlencode($codeNumber);
    
    try {
        $options = new QROptions();
        $options->outputType = QRCode::OUTPUT_IMAGE_PNG;
        $options->eccLevel = QRCode::ECC_H; // High 30% error correction
        $options->scale = 6;
        $options->imageBase64 = false;

        $qrcode = new QRCode($options);
        $rawPng = $qrcode->render($scanUrl);
        $logoPng = embedLogoInQRCode($rawPng);

        return 'data:image/png;base64,' . base64_encode($logoPng);
    } catch (\Throwable $e) {
        return '';
    }
}

/**
 * Returns JSON AJAX response
 */
function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => (bool)$success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Sanitizes input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}
