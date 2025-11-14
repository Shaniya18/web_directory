<?php
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Contact.php';

class StaffDashboardController {
    private $db;
    private $listingModel;
    private $reviewModel;
    private $contactModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->listingModel = new Listing($db);
        $this->reviewModel = new Review($db);
        $this->contactModel = new Contact($db);
    }
    
    public function index() {
        // Handle CSV download
        if (isset($_GET['download']) && $_GET['download'] === 'csv') {
            $this->downloadStats();
            return; // Stop execution after download
        }
        
        $section = $_GET['section'] ?? 'dashboard';
        
        // Handle actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleActions();
        }
        
        $data = [];
        
        switch ($section) {
            case 'listings':
                $data['listings'] = $this->listingModel->getPending();
                $template = 'listings';
                break;
            case 'reviews':
                $data['reviews'] = $this->reviewModel->getPending();
                $template = 'reviews';
                break;
            case 'messages':
                $data['messages'] = $this->contactModel->getAll();
                $template = 'messages';
                break;
            default:
                $data['stats'] = $this->getDashboardStats();
                $data['topListings'] = $this->getTopListings();
                $template = 'dashboard';
        }
        
        // Extract data to variables for the template
        extract($data);
        
        // Use staff layout
        include __DIR__ . '/../views/staff/layout.php';
    }
    
    private function getDashboardStats() {
        $stats = [];
        
        // Total listings
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 1");
        $stats['total_listings'] = $result->fetch_assoc()['cnt'];
        
        // Pending listings
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM listings WHERE approved = 0");
        $stats['pending_listings'] = $result->fetch_assoc()['cnt'];
        
        // Total registered users
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM users");
        $stats['total_users'] = $result->fetch_assoc()['cnt'];
        
        // Pending reviews
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM reviews WHERE approved = 0");
        $stats['pending_reviews'] = $result->fetch_assoc()['cnt'];
        
        // Total messages
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM contact_messages");
        $stats['total_messages'] = $result->fetch_assoc()['cnt'];
        
        return $stats;
    }
    
    private function getTopListings() {
        $result = $this->db->query("
            SELECT l.*, c.name as category_name 
            FROM listings l 
            LEFT JOIN categories c ON l.category_id = c.id 
            WHERE l.approved = 1 AND l.rating_count > 0 
            ORDER BY l.rating_avg DESC, l.views DESC 
            LIMIT 10
        ");
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function downloadStats() {
        // Get all statistics
        $stats = $this->getDashboardStats();
        $topListings = $this->getTopListings();
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=fiji_directory_stats_' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        // Main Statistics
        fputcsv($output, ['FIJI WEB DIRECTORY - STATISTICS REPORT']);
        fputcsv($output, ['Generated on:', date('F j, Y g:i A')]);
        fputcsv($output, []); // Empty row
        
        fputcsv($output, ['MAIN STATISTICS']);
        fputcsv($output, ['Metric', 'Count']);
        fputcsv($output, ['Total Listings', $stats['total_listings']]);
        fputcsv($output, ['Pending Listings', $stats['pending_listings']]);
        fputcsv($output, ['Registered Users', $stats['total_users']]);
        fputcsv($output, ['Pending Reviews', $stats['pending_reviews']]);
        fputcsv($output, ['Contact Messages', $stats['total_messages']]);
        fputcsv($output, []); // Empty row
        
        // Top Rated Listings
        fputcsv($output, ['TOP RATED LISTINGS']);
        fputcsv($output, ['Title', 'Category', 'Rating', 'Reviews', 'Views']);
        
        foreach ($topListings as $listing) {
            fputcsv($output, [
                $listing['title'],
                $listing['category_name'] ?? 'Unknown',
                $listing['rating_avg'],
                $listing['rating_count'],
                $listing['views']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    private function handleActions() {
        // Handle approve/delete actions
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return;
        }
        
        $action = $_POST['action'] ?? '';
        $id = intval($_POST['id'] ?? 0);
        
        switch ($action) {
            case 'approve_listing':
                $this->listingModel->approve($id);
                break;
            case 'delete_listing':
                $this->listingModel->delete($id);
                break;
            case 'approve_review':
                $this->reviewModel->approve($id);
                break;
            case 'delete_review':
                $this->reviewModel->delete($id);
                break;
            case 'delete_message':
                $this->contactModel->delete($id);
                break;
        }
    }
}
?>