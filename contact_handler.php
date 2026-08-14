<?php
// contact_handler.php - Landing Page Contact Form & SMTP Email Processor

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/smtp_mailer.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$mobile = sanitize($_POST['mobile'] ?? '');
$quantity = sanitize($_POST['quantity'] ?? '1');
$message = sanitize($_POST['message'] ?? '');

if (empty($name) || empty($mobile)) {
    jsonResponse(false, 'Please provide your name and mobile number.');
}

// Build HTML Email for Company Notification
$subject = "🚨 New Vehicle Sampark Inquiry from $name ($mobile)";
$htmlBody = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; background: #ffffff;'>
    <div style='text-align: center; border-bottom: 2px solid #10b981; padding-bottom: 15px; margin-bottom: 20px;'>
        <h2 style='color: #0f172a; margin: 0;'>Vehicle <span style='color: #10b981;'>Sampark</span> Inquiry</h2>
        <p style='color: #f97316; font-weight: bold; margin-top: 5px;'>Smart Vehicle QR Emergency System</p>
    </div>

    <table style='width: 100%; border-collapse: collapse;'>
        <tr><td style='padding: 8px; font-weight: bold; color: #475569;'>Full Name:</td><td style='padding: 8px; color: #0f172a;'>$name</td></tr>
        <tr><td style='padding: 8px; font-weight: bold; color: #475569;'>Mobile Number:</td><td style='padding: 8px; color: #10b981; font-weight: bold;'>$mobile</td></tr>
        <tr><td style='padding: 8px; font-weight: bold; color: #475569;'>Email Address:</td><td style='padding: 8px; color: #0f172a;'>$email</td></tr>
        <tr><td style='padding: 8px; font-weight: bold; color: #475569;'>Tags Needed:</td><td style='padding: 8px; color: #f97316; font-weight: bold;'>$quantity Tag(s)</td></tr>
        <tr><td style='padding: 8px; font-weight: bold; color: #475569;'>Message:</td><td style='padding: 8px; color: #0f172a;'>$message</td></tr>
    </table>

    <div style='margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center;'>
        Sent via Vehicle Sampark Landing Page Contact Form &bull; " . date('Y-m-d H:i:s') . "
    </div>
</div>
";

// Send SMTP Email Notification
$emailSent = sendSmtpEmail(COMPANY_NOTIFICATION_EMAIL, $subject, $htmlBody, $email);

jsonResponse(true, "Thank you! Your inquiry has been submitted successfully. Our team will contact you shortly.", [
    'email_sent' => $emailSent,
    'mobile' => $mobile
]);
