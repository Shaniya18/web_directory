<?php
class User {
    private $db;
    
    public function __construct($conn) {
        $this->db = $conn;
    }
    
    public function findById($id) {
        $result = $this->db->query("SELECT * FROM users WHERE id = ?", [$id], "i");
        return $result->fetch_assoc();
    }
    
    public function findByEmail($email) {
        $result = $this->db->query("SELECT * FROM users WHERE email = ?", [$email], "s");
        return $result->fetch_assoc();
    }
    
    public function findByUsernameOrEmail($username, $email) {
        $result = $this->db->query(
            "SELECT id FROM users WHERE username = ? OR email = ?",
            [$username, $email],
            "ss"
        );
        return $result->fetch_assoc();
    }
    
    public function create($data) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));
        
        $result = $this->db->execute(
            "INSERT INTO users (username, email, password, full_name, verification_token, is_verified) 
             VALUES (?, ?, ?, ?, ?, 1)",
            [
                $data['username'],
                $data['email'],
                $password_hash,
                $data['full_name'],
                $verification_token
            ],
            "sssss"
        );
        
        if ($result) {
            $userId = $this->db->lastInsertId();
            
            // Store password in history
            $this->addToPasswordHistory($userId, $password_hash);
            
            return $userId;
        }
        
        return false;
    }
    
    public function validateLogin($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Check if account is locked
        if ($this->isAccountLocked($user['id'])) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function getAdminByUsername($username) {
        $result = $this->db->query("SELECT * FROM admin_users WHERE username = ?", [$username], "s");
        return $result->fetch_assoc();
    }
    
    // Security methods for account lockout
    public function incrementLoginAttempts($userId) {
        $this->db->execute(
            "UPDATE users SET login_attempts = login_attempts + 1, last_login_attempt = NOW() 
             WHERE id = ?",
            [$userId], "i"
        );
    }
    
    public function resetLoginAttempts($userId) {
        $this->db->execute(
            "UPDATE users SET login_attempts = 0, locked_until = NULL, last_login_attempt = NULL WHERE id = ?",
            [$userId], "i"
        );
    }
    
    public function lockAccount($userId) {
        $this->db->execute(
            "UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?",
            [$userId], "i"
        );
    }
    
    public function isAccountLocked($userId) {
        $result = $this->db->query(
            "SELECT locked_until FROM users WHERE id = ? AND locked_until > NOW()",
            [$userId], "i"
        );
        
        if ($result && $result->num_rows > 0) {
            return true;
        }
        return false;
    }
    
    public function getLockTimeRemaining($userId) {
        $result = $this->db->query(
            "SELECT TIMESTAMPDIFF(SECOND, NOW(), locked_until) as seconds_remaining 
             FROM users WHERE id = ? AND locked_until > NOW()",
            [$userId], "i"
        );
        
        if ($result && $row = $result->fetch_assoc()) {
            $minutes = ceil($row['seconds_remaining'] / 60);
            return "$minutes minutes";
        }
        
        return "0 minutes";
    }
    
    public function getLoginAttempts($userId) {
        $result = $this->db->query(
            "SELECT login_attempts FROM users WHERE id = ?",
            [$userId], "i"
        );
        
        if ($result && $row = $result->fetch_assoc()) {
            return $row['login_attempts'];
        }
        
        return 0;
    }
    
    // Password history methods
    public function addToPasswordHistory($userId, $hashedPassword) {
        $this->db->execute(
            "INSERT INTO password_history (user_id, password_hash, created_at) 
             VALUES (?, ?, NOW())",
            [$userId, $hashedPassword], "is"
        );
        
        // Keep only last 5 passwords
        $this->db->execute(
            "DELETE FROM password_history 
             WHERE user_id = ? AND id NOT IN (
                 SELECT id FROM (
                     SELECT id FROM password_history 
                     WHERE user_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 5
                 ) AS recent
             )",
            [$userId, $userId], "ii"
        );
    }
    
    // NEW METHOD: Check if password has been used by ANY user
    public function isPasswordUsedByAnyUser($password) {
        // Check all current user passwords
        $users = $this->db->query("SELECT password FROM users", [], "");
        if ($users) {
            $allPasswords = $users->fetch_all(MYSQLI_ASSOC);
            foreach ($allPasswords as $userPassword) {
                if (password_verify($password, $userPassword['password'])) {
                    return true;
                }
            }
        }
        
        // Check password history across all users
        $history = $this->db->query(
            "SELECT password_hash FROM password_history ORDER BY created_at DESC LIMIT 100",
            [], ""
        );
        
        if ($history) {
            $historicalPasswords = $history->fetch_all(MYSQLI_ASSOC);
            foreach ($historicalPasswords as $oldPassword) {
                if (password_verify($password, $oldPassword['password_hash'])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Method to check password history for existing user (for password changes)
    public function isPasswordInHistoryForUser($userId, $password) {
        $history = $this->db->query(
            "SELECT password_hash FROM password_history 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 5",
            [$userId], "i"
        );
        
        if ($history) {
            $passwords = $history->fetch_all(MYSQLI_ASSOC);
            foreach ($passwords as $oldPassword) {
                if (password_verify($password, $oldPassword['password_hash'])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    // Additional utility methods
    public function updateLastLogin($userId) {
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$userId], "i"
        );
    }
    
    public function getUserStats($userId) {
        $result = $this->db->query(
            "SELECT login_attempts, locked_until, last_login, last_login_attempt, created_at 
             FROM users WHERE id = ?",
            [$userId], "i"
        );
        
        return $result ? $result->fetch_assoc() : null;
    }
    
    // Method to check if user exists by username
    public function findByUsername($username) {
        $result = $this->db->query(
            "SELECT * FROM users WHERE username = ?",
            [$username], "s"
        );
        return $result ? $result->fetch_assoc() : null;
    }
    
    // Method to update user profile
    public function updateProfile($userId, $data) {
        $result = $this->db->execute(
            "UPDATE users SET full_name = ?, email = ? WHERE id = ?",
            [$data['full_name'], $data['email'], $userId],
            "ssi"
        );
        return $result;
    }
    
    // Method to change password with history check
    public function changePassword($userId, $newPassword) {
        // Check if password is in history
        if ($this->isPasswordInHistoryForUser($userId, $newPassword)) {
            return false; // Password found in history
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $result = $this->db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [$hashedPassword, $userId],
            "si"
        );
        
        if ($result) {
            // Add to password history
            $this->addToPasswordHistory($userId, $hashedPassword);
            return true;
        }
        
        return false;
    }
    
    // Method to get user's password history count
    public function getPasswordHistoryCount($userId) {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM password_history WHERE user_id = ?",
            [$userId], "i"
        );
        
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        
        return 0;
    }
}
?>