<?php
class PasswordReset {
    private $db;
    
    public function __construct($conn) {
        $this->db = $conn;
    }
    
    public function createToken($email) {
        // Delete any existing tokens for this email
        $this->db->execute(
            "DELETE FROM password_resets WHERE user_id IN (SELECT id FROM users WHERE email = ?)",
            [$email],
            "s"
        );
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Get user ID
        $user = $this->getUserByEmail($email);
        if (!$user) return false;
        
        // Insert token
        $result = $this->db->execute(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$user['id'], $token, $expires],
            "iss"
        );
        
        return $result ? $token : false;
    }
    
    public function validateToken($token) {
        $result = $this->db->query(
            "SELECT pr.*, u.email, u.username 
             FROM password_resets pr 
             JOIN users u ON pr.user_id = u.id 
             WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0",
            [$token],
            "s"
        );
        
        return $result->fetch_assoc();
    }
    
    public function markTokenUsed($token) {
        return $this->db->execute(
            "UPDATE password_resets SET used = 1 WHERE token = ?",
            [$token],
            "s"
        );
    }
    
    public function getUserByEmail($email) {
        $result = $this->db->query(
            "SELECT id, email, username FROM users WHERE email = ?",
            [$email],
            "s"
        );
        return $result->fetch_assoc();
    }
    
    public function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hash, $userId],
            "si"
        );
    }
}
?>