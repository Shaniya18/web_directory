<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Listing.php';

class CategoryController {
    private $db;
    private $categoryModel;
    private $listingModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->categoryModel = new Category($db);
        $this->listingModel = new Listing($db);
    }
    
    public function show() {
        $categoryId = $_GET['id'] ?? 0;
        $page = max(1, intval($_GET['p'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $category = $this->categoryModel->find($categoryId);
        if (!$category) {
            SessionManager::setMessage('error', 'Category not found');
            redirect('?page=home');
        }
        
        // Build breadcrumbs
        $breadcrumbs = $this->buildBreadcrumbs($categoryId);
        
        $subcategories = $this->categoryModel->getSubcategories($categoryId);
        $listings = $this->listingModel->getByCategory($categoryId, $perPage, $offset);
        $totalListings = $this->listingModel->countByCategory($categoryId);
        
        view('category/show', [
            'category' => $category,
            'subcategories' => $subcategories,
            'listings' => $listings,
            'totalListings' => $totalListings,
            'currentPage' => $page,
            'perPage' => $perPage,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => $category['name'] . ' - Fiji Web Directory'
        ]);
    }
    
    private function buildBreadcrumbs($categoryId) {
        $breadcrumbs = [];
        $currentId = $categoryId;
        
        while ($currentId) {
            $category = $this->categoryModel->find($currentId);
            if ($category) {
                array_unshift($breadcrumbs, [
                    'name' => $category['name'],
                    'url' => '?page=category&id=' . $category['id']
                ]);
                $currentId = $category['parent_id'];
            } else {
                break;
            }
        }
        
        return $breadcrumbs;
    }
}
?>