<?php
// includes/smtp_mailer.php - Vehicle Sampark SMTP Mailer & Inquiry Notification Engine

// Hostinger / Standard SMTP Server Configuration Settings
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587); // 587 for TLS, 465 for SSL
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USER', 'contact@vehiclesampark.com'); // Your Hostinger Email Address
define('SMTP_PASS', 'YourPassword123!');           // Your Hostinger Email Password
define('SMTP_FROM_NAME', 'Vehicle Sampark Web Inquiry');
define('COMPANY_NOTIFICATION_EMAIL', 'contact@vehiclesampark.com'); // Destination Email

/**
 * Sends HTML Email via SMTP Socket Connection with mail() Fallback
 */
function sendSmtpEmail($toEmail, $subject, $htmlBody, $replyTo = null) {
    if (!SMTP_ENABLED) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_USER . '>' . "\r\n";
        if ($replyTo) $headers .= 'Reply-To: ' . $replyTo . "\r\n";
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }

    try {
        $socket = @fsockopen((SMTP_SECURE === 'ssl' ? 'ssl://' : '') . SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
        if (!$socket) {
            // Socket failed - fallback to PHP mail()
            $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . SMTP_USER . ">\r\n";
            if ($replyTo) $headers .= "Reply-To: $replyTo\r\n";
            return @mail($toEmail, $subject, $htmlBody, $headers);
        }

        fgets($socket, 512);
        fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
        fgets($socket, 512);

        if (SMTP_SECURE === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            fgets($socket, 512);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($socket, "EHLO " . SMTP_HOST . "\r\n");
            fgets($socket, 512);
        }

        fputs($socket, "AUTH LOGIN\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode(SMTP_USER) . "\r\n");
        fgets($socket, 512);
        fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
        $authResponse = fgets($socket, 512);

        fputs($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
        fgets($socket, 512);
        fputs($socket, "RCPT TO: <" . $toEmail . ">\r\n");
        fgets($socket, 512);
        fputs($socket, "DATA\r\n");
        fgets($socket, 512);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_USER . ">\r\n";
        $headers .= "To: <" . $toEmail . ">\r\n";
        if ($replyTo) $headers .= "Reply-To: <" . $replyTo . ">\r\n";
        $headers .= "Subject: " . $subject . "\r\n";

        fputs($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");
        fgets($socket, 512);
        fputs($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    } catch (Exception $e) {
        // Fallback to mail()
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . SMTP_USER . ">\r\n";
        if ($replyTo) $headers .= "Reply-To: $replyTo\r\n";
        return @mail($toEmail, $subject, $htmlBody, $headers);
    }
}
