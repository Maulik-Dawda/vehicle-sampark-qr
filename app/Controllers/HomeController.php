<?php
// app/Controllers/HomeController.php - Public Landing Page & Contact Controller

class HomeController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        $pageTitle = 'Vehicle Sampark - Always Within Reach';
        $isLandingPage = true;
        
        include __DIR__ . '/../Views/home/index.php';
    }

    public function handleContactForm() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $mobile = sanitize($_POST['mobile_number'] ?? '');
        $subject = sanitize($_POST['subject'] ?? 'Website Inquiry');
        $message = sanitize($_POST['message'] ?? '');

        if (empty($fullName) || empty($email) || empty($mobile) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Please fill out all required contact fields.']);
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bot_logs (qr_code_id, action_type, payload, user_ip)
                VALUES (NULL, 'contact_inquiry', ?, ?)
            ");
            $payload = json_encode([
                'name' => $fullName,
                'email' => $email,
                'mobile' => $mobile,
                'subject' => $subject,
                'message' => $message
            ], JSON_UNESCAPED_UNICODE);
            $stmt->execute([$payload, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully. Our team will contact you shortly.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to record your message. Please try again.']);
            exit;
        }
    }
}
