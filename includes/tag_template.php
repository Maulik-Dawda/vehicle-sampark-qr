<?php
// includes/tag_template.php - Vehicle Sampark Physical Tag Sticker Card (Green & Orange Theme)

require_once __DIR__ . '/functions.php';

function renderVehicleSamparkTagHTML($codeNumber, $formTitle = 'Vehicle Contact Tag') {
    $qrBase64 = getQRCodeBase64($codeNumber);
    $logoPath = __DIR__ . '/../assets/images/logo.jpg';
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
    }

    $cssBlock = '<style>' . getVehicleSamparkTagCSS() . '</style>';

    return $cssBlock . '
    <div class="sampark-tag-box">
        <table class="sampark-tag-table">
            <tr>
                <!-- LEFT PANEL (White Background) -->
                <td class="tag-left-cell">
                    <div class="tag-brand-header">
                        <table style="width: 100%;">
                            <tr>
                                <td style="vertical-align: middle;">
                                    <span class="tag-brand-title">VEHICLE SAMPARK</span>
                                    <div class="tag-yellow-bar"></div>
                                </td>
                                ' . ($logoBase64 ? '<td style="text-align: right; vertical-align: middle;"><img src="' . $logoBase64 . '" class="tag-logo-thumb" style="height:32px; max-height:32px; width:auto; max-width:100px; object-fit:contain; border-radius:4px; display:block; margin-left:auto;"></td>' : '') . '
                            </tr>
                        </table>
                    </div>
                    <div class="tag-sub-caption">Vehicle Sampark tag &bull; Scan to contact the vehicle owner.</div>

                    <div class="tag-main-headline">
                        Scan the code<br>
                        to <span class="tag-highlight">contact the</span><br>
                        vehicle owner.
                    </div>

                    <div class="tag-bottom-notice">
                        SCAN USING PHONE CAMERA, GOOGLE LENS OR ANY QR SCANNER APP.
                    </div>
                </td>

                <!-- RIGHT PANEL (Vibrant Orange Panel) -->
                <td class="tag-right-cell">
                    <div class="qr-white-card">
                        <img src="' . $qrBase64 . '" class="tag-qr-img" alt="QR Code">
                    </div>
                    
                    <table style="width: 100%; margin-top: 6px;">
                        <tr>
                            <td style="font-size: 8px; font-weight: bold; color: #ffffff; text-align: left;">
                                <span class="nfc-badge">SMART TAG</span>
                            </td>
                            <td style="font-family: monospace; font-size: 10px; font-weight: bold; color: #ffffff; text-align: right;">
                                ' . htmlspecialchars($codeNumber) . '
                            </td>
                        </tr>
                    </table>

                    <div class="tag-panel-footer" style="margin-top: 8px;">
                        Wrong Parking, Emergency Contact,<br>
                        any issue with the vehicle, Scan the QR.
                    </div>
                </td>
            </tr>
        </table>
    </div>
    ';
}

function getVehicleSamparkTagCSS() {
    return '
    .sampark-tag-box {
        width: 100%;
        max-width: 580px;
        margin: 0 auto 15px auto;
        border: 2px solid #000000;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        font-family: Helvetica, Arial, sans-serif;
        text-align: left;
    }
    .sampark-tag-table {
        width: 100%;
        border-collapse: collapse;
    }
    .tag-left-cell {
        width: 58%;
        background: #ffffff;
        padding: 14px 16px;
        vertical-align: top;
        color: #000000;
        border-right: 2px solid #000000;
    }
    .tag-right-cell {
        width: 42%;
        background: #f97316; /* Energetic Orange Panel */
        padding: 12px 14px;
        vertical-align: top;
        text-align: center;
        color: #ffffff;
    }
    .tag-brand-title {
        font-size: 19px;
        font-weight: 900;
        letter-spacing: -0.5px;
        color: #000000;
        text-transform: uppercase;
        display: block;
    }
    .tag-logo-thumb {
        height: 32px !important;
        max-height: 32px !important;
        width: auto !important;
        max-width: 100px !important;
        object-fit: contain !important;
        border-radius: 4px;
        display: block;
        margin-left: auto;
    }
    .tag-yellow-bar {
        height: 4px;
        background: #10b981; /* Emerald Green Bar */
        width: 100%;
        margin-top: 2px;
        border-radius: 2px;
    }
    .tag-sub-caption {
        font-size: 8.5px;
        color: #475569;
        margin-top: 4px;
        margin-bottom: 10px;
    }
    .tag-main-headline {
        font-size: 21px;
        font-weight: 900;
        line-height: 1.15;
        color: #000000;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }
    .tag-highlight {
        text-decoration: underline;
        text-decoration-color: #10b981; /* Green underline */
        text-decoration-thickness: 3px;
    }
    .tag-bottom-notice {
        font-size: 7.5px;
        font-weight: bold;
        color: #64748b;
        letter-spacing: 0.2px;
        line-height: 1.3;
    }
    .qr-white-card {
        background: #ffffff;
        border-radius: 8px;
        padding: 5px;
        border: 2px solid #000000;
        display: inline-block;
    }
    .tag-qr-img {
        width: 125px;
        height: 125px;
        max-width: 100%;
        display: block;
        border-radius: 4px;
    }
    .nfc-badge {
        background: #000000;
        color: #ffffff;
        padding: 1px 5px;
        border-radius: 3px;
        font-size: 8px;
        letter-spacing: 0.5px;
    }
    .tag-panel-footer {
        font-size: 8.5px;
        font-weight: bold;
        color: #ffffff;
        margin-top: 6px;
        line-height: 1.25;
    }
    ';
}
