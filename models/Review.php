<?php
class Review {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getByListing($listingId) {
        $result = $this->db->query(
            "SELECT r.*, u.username 
             FROM reviews r 
             LEFT JOIN users u ON r.user_id = u.id 
             WHERE r.listing_id = ? AND r.approved = 1 
             ORDER BY r.created_at DESC",
            [$listingId],
            "i"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    // ✅ ADDED: Get reviews by user ID
    public function getByUserId($userId) {
        $result = $this->db->query(
            "SELECT r.*, l.title as listing_title, l.url as listing_url 
             FROM reviews r 
             LEFT JOIN listings l ON r.listing_id = l.id 
             WHERE r.user_id = ? 
             ORDER BY r.created_at DESC",
            [$userId],
            "i"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function create($data) {
        return $this->db->execute(
            "INSERT INTO reviews (listing_id, user_id, rating, title, review_text, approved) 
             VALUES (?, ?, ?, ?, ?, 0)",
            [
                $data['listing_id'],
                $data['user_id'],
                $data['rating'],
                $data['title'],
                $data['review_text']
            ],
            "iiiss"
        );
    }
    
    public function getPending() {
        $result = $this->db->query("
            SELECT r.*, l.title as listing_title 
            FROM reviews r 
            JOIN listings l ON r.listing_id = l.id 
            WHERE r.approved = 0 
            ORDER BY r.id DESC
        ");
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function approve($id) {
        $result = $this->db->execute(
            "UPDATE reviews SET approved = 1 WHERE id = ?",
            [$id],
            "i"
        );
        
        if ($result) {
            // Update listing rating
            $review = $this->find($id);
            if ($review) {
                $this->updateListingRating($review['listing_id']);
            }
        }
        
        return $result;
    }
    
    public function delete($id) {
        return $this->db->execute("DELETE FROM reviews WHERE id = ?", [$id], "i");
    }
    
    public function find($id) {
        $result = $this->db->query("SELECT * FROM reviews WHERE id = ?", [$id], "i");
        return $result ? $result->fetch_assoc() : null;
    }
    
    public function getTotalApproved() {
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM reviews WHERE approved = 1");
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['cnt'];
        }
        return 0;
    }
    
    private function updateListingRating($listingId) {
        // Calculate new average rating
        $result = $this->db->query(
            "SELECT AVG(rating) as avg_rating, COUNT(*) as count 
             FROM reviews 
             WHERE listing_id = ? AND approved = 1",
            [$listingId],
            "i"
        );
        
        if ($result) {
            $stats = $result->fetch_assoc();
            
            // Update listing
            $this->db->execute(
                "UPDATE listings SET rating_avg = ?, rating_count = ? WHERE id = ?",
                [
                    floatval($stats['avg_rating']),
                    intval($stats['count']),
                    $listingId
                ],
                "dii"
            );
        }
    }
}
?>