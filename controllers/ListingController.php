<?php
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Review.php';

class ListingController {
    private $db;
    private $listingModel;
    private $categoryModel;
    private $reviewModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->listingModel = new Listing($db);
        $this->categoryModel = new Category($db);
        $this->reviewModel = new Review($db);
    }
    
    public function show() {
        $listingId = $_GET['listing_id'] ?? 0;
        
        // Increment views
        $this->listingModel->incrementViews($listingId);
        
        $listing = $this->listingModel->find($listingId);
        if (!$listing) {
            SessionManager::setMessage('error', 'Listing not found');
            redirect('?page=home');
        }
        
        $reviews = $this->reviewModel->getByListing($listingId);
        
        // Handle review submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
            $this->handleReviewSubmission($listingId);
        }
        
        view('listing/show', [
            'listing' => $listing,
            'reviews' => $reviews,
            'pageTitle' => $listing['title'] . ' - Fiji Web Directory'
        ]);
    }
    
    public function create() {
        // Check if user is logged in
        if (!SessionManager::isLoggedIn()) {
            SessionManager::setMessage('error', 'Please login to submit a listing');
            redirect('?page=login');
        }
        
        $categories = $this->categoryModel->getAll();
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_listing') {
            $this->handleListingSubmission();
        }
        
        view('listing/submit', [
            'categories' => $categories,
            'pageTitle' => 'Submit Listing - Fiji Web Directory'
        ]);
    }
    
    private function handleReviewSubmission($listingId) {
        if (!SessionManager::isLoggedIn()) {
            SessionManager::setMessage('error', 'Please login to submit a review');
            return;
        }
        
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            SessionManager::setMessage('error', 'Security validation failed');
            return;
        }
        
        $data = [
            'listing_id' => $listingId,
            'user_id' => SessionManager::getUserId(),
            'rating' => intval($_POST['rating']),
            'title' => trim($_POST['review_title'] ?? ''),
            'review_text' => trim($_POST['review_text'] ?? '')
        ];
        
        if ($data['rating'] < 1 || $data['rating'] > 5 || empty($data['review_text'])) {
            SessionManager::setMessage('error', 'Please provide a rating and review text');
            return;
        }
        
        if ($this->reviewModel->create($data)) {
            SessionManager::setMessage('success', 'Review submitted! It will be published after approval.');
        } else {
            SessionManager::setMessage('error', 'Failed to submit review');
        }
        
        redirect('?page=listing&listing_id=' . $listingId);
    }
    
    private function handleListingSubmission() {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            SessionManager::setMessage('error', 'Security validation failed');
            return;
        }
        
        // CAPTCHA validation
        if (!validateCaptcha($_POST['captcha_answer'] ?? 0)) {
            SessionManager::setMessage('error', 'CAPTCHA verification failed');
            return;
        }
        
        // ✅ CHECK IF MAIN IMAGE IS PROVIDED (using 'main_image' field that matches your form)
        if (!isset($_FILES['main_image']) || $_FILES['main_image']['error'] !== UPLOAD_ERR_OK) {
            SessionManager::setMessage('error', 'Main website screenshot is required');
            return;
        }
        
        // ✅ VALIDATE MAIN IMAGE FIRST
        $mainImageResult = $this->handleFileUpload($_FILES['main_image']);
        if (!$mainImageResult) {
            // Error message already set in handleFileUpload
            return;
        }
        
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'url' => trim($_POST['url'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category_id' => intval($_POST['category_id'] ?? 0),
            'region' => trim($_POST['region'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'tags' => trim($_POST['tags'] ?? ''),
            'user_id' => SessionManager::getUserId(),
            'main_image_path' => $mainImageResult // ✅ Store main image
        ];
        
        // ✅ Handle additional optional images
        if (isset($_FILES['image_1']) && $_FILES['image_1']['error'] === UPLOAD_ERR_OK) {
            $image1Result = $this->handleFileUpload($_FILES['image_1']);
            if ($image1Result) {
                $data['image_1_path'] = $image1Result;
            }
        }
        
        if (isset($_FILES['image_2']) && $_FILES['image_2']['error'] === UPLOAD_ERR_OK) {
            $image2Result = $this->handleFileUpload($_FILES['image_2']);
            if ($image2Result) {
                $data['image_2_path'] = $image2Result;
            }
        }
        
        if (isset($_FILES['image_3']) && $_FILES['image_3']['error'] === UPLOAD_ERR_OK) {
            $image3Result = $this->handleFileUpload($_FILES['image_3']);
            if ($image3Result) {
                $data['image_3_path'] = $image3Result;
            }
        }
        
        // Validation
        if (empty($data['title']) || empty($data['url']) || empty($data['description']) || $data['category_id'] <= 0) {
            SessionManager::setMessage('error', 'Please fill all required fields');
            return;
        }
        
        if (!filter_var($data['url'], FILTER_VALIDATE_URL)) {
            SessionManager::setMessage('error', 'Please enter a valid URL');
            return;
        }
        
        if ($this->listingModel->create($data)) {
            SessionManager::setMessage('success', 'Listing submitted successfully! It will be reviewed by our team.');
        } else {
            SessionManager::setMessage('error', 'An error occurred. Please try again.');
        }
        
        redirect('?page=submit');
    }
    
    // Secure file upload handling method
    private function handleFileUpload($file) {
        // ✅ TEMPORARY DEBUG - INSIDE THE FUNCTION
        error_log("=== FILE UPLOAD DEBUG ===");
        error_log("File name: " . $file['name']);
        error_log("File size: " . $file['size']);
        error_log("File error: " . $file['error']);
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Check file size
        if ($file['size'] > $maxSize) {
            error_log("DEBUG: File too large - " . $file['size']);
            SessionManager::setMessage('error', 'File too large. Maximum size: 5MB');
            return false;
        }
        
        // Check if fileinfo functions exist
        if (!function_exists('finfo_open')) {
            error_log("ERROR: fileinfo extension not loaded!");
            SessionManager::setMessage('error', 'Server configuration error: fileinfo extension missing');
            return false;
        }
        
        // MIME type validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            error_log("ERROR: Cannot open fileinfo database");
            SessionManager::setMessage('error', 'Server error: Cannot verify file type');
            return false;
        }
        
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        error_log("Detected MIME type: " . $mime);
        error_log("Allowed types: " . implode(', ', $allowedTypes));
        
        if (!in_array($mime, $allowedTypes)) {
            error_log("DEBUG: Invalid MIME type - " . $mime);
            SessionManager::setMessage('error', 'Invalid file type. Only images allowed (JPEG, PNG, GIF, WebP).');
            return false;
        }
        
        // Generate secure random filename to prevent overwrites
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $secureFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $uploadDir = __DIR__ . '/../uploads/website_images/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $secureFilename;
        
        // Use move_uploaded_file() for secure handling
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            error_log("DEBUG: File uploaded successfully - " . $secureFilename);
            return 'uploads/website_images/' . $secureFilename;
        }
        
        error_log("DEBUG: File upload failed");
        SessionManager::setMessage('error', 'File upload failed. Please try again.');
        return false;
    }
}
?>