<?php
require_once __DIR__ . '/../models/Contact.php';

class ContactController {
    private $db;
    private $contactModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->contactModel = new Contact($db);
    }
    
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact_submit') {
            $this->handleContactSubmission();
        }
        
        view('contact', [
            'pageTitle' => 'Contact Us - Fiji Web Directory'
        ]);
    }
    
    private function handleContactSubmission() {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            SessionManager::setMessage('error', 'Security validation failed');
            return;
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'subject' => trim($_POST['subject'] ?? ''),
            'message' => trim($_POST['message'] ?? ''),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        // Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
            SessionManager::setMessage('error', 'All fields are required');
            return;
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            SessionManager::setMessage('error', 'Invalid email address');
            return;
        }
        
        if ($this->contactModel->create($data)) {
            SessionManager::setMessage('success', 'Message sent successfully! We will get back to you soon.');
        } else {
            SessionManager::setMessage('error', 'Failed to send message. Please try again.');
        }
        
        redirect('?page=contact');
    }
}
?>