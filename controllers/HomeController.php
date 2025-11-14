<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Review.php';

class HomeController {
    private $db;
    private $categoryModel;
    private $listingModel;
    private $reviewModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->categoryModel = new Category($db);
        $this->listingModel = new Listing($db);
        $this->reviewModel = new Review($db);
    }
    
    public function index() {
        $categories = $this->categoryModel->getAllWithSubcategories();
        
        $stats = [
            'total_listings' => $this->listingModel->getTotalApproved(),
            'total_categories' => $this->categoryModel->getTotalMainCategories(),
            'total_reviews' => $this->reviewModel->getTotalApproved()
        ];
        
        view('home', [
            'categories' => $categories,
            'stats' => $stats,
            'pageTitle' => 'Fiji Web Directory - Home'
        ]);
    }
    
    public function about() {
        view('about', [
            'pageTitle' => 'About - Fiji Web Directory'
        ]);
    }
}
?>