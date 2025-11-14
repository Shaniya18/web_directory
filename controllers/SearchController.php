<?php
require_once __DIR__ . '/../models/Listing.php';

class SearchController {
    private $db;
    private $listingModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->listingModel = new Listing($db);
        
        // DEBUG
        error_log("SearchController initialized");
    }
    
    public function index() {
        error_log("=== SEARCH CONTROLLER CALLED ===");
        error_log("Search term: " . ($_GET['search'] ?? 'EMPTY'));
        
        $search = trim($_GET['search'] ?? '');
        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        if (empty($search)) {
            $results = [];
            $total = 0;
            $message = 'Please enter a search term to find listings.';
            error_log("Empty search term");
        } else {
            error_log("Searching for: '$search'");
            $results = $this->listingModel->search($search, $perPage, $offset);
            $total = $this->listingModel->countSearch($search);
            $message = $total > 0 ? "Found $total result(s)" : "No results found. Try different keywords.";
            
            error_log("Search results count: " . count($results));
            error_log("Total results: $total");
            
            // DEBUG: Check what's in results
            foreach ($results as $index => $result) {
                error_log("Result $index: " . $result['title'] . " | Category: " . ($result['category_name'] ?? 'MISSING'));
            }
        }
        
        view('search/results', [
            'searchTerm' => $search,
            'results' => $results,
            'totalResults' => $total,
            'currentPage' => $page,
            'perPage' => $perPage,
            'message' => $message,
            'pageTitle' => 'Search: ' . $search . ' - Fiji Web Directory'
        ]);
    }
}
?>