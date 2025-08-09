<?php
class Security {
    // Password hashing
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Verify password
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Generate CSRF token
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    // Validate CSRF token
    public static function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            die('Invalid CSRF token');
        }
    }
    
    // Sanitize input
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    // Validate email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Rate limiting
    public static function checkLoginAttempts($ip, $maxAttempts = 5, $lockoutTime = 900) {
        $key = "login_attempts_$ip";
        $attempts = $_SESSION[$key] ?? 0;
        $lastAttempt = $_SESSION["last_attempt_$ip"] ?? 0;
        
        if (time() - $lastAttempt < $lockoutTime && $attempts >= $maxAttempts) {
            return false;
        }
        
        if (time() - $lastAttempt >= $lockoutTime) {
            $_SESSION[$key] = 0;
        }
        
        return true;
    }
    
    public static function incrementLoginAttempts($ip) {
        $key = "login_attempts_$ip";
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION["last_attempt_$ip"] = time();
    }
    
    public static function resetLoginAttempts($ip) {
        unset($_SESSION["login_attempts_$ip"]);
        unset($_SESSION["last_attempt_$ip"]);
    }
    
    // Session security
    public static function secureSession() {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.cookie_samesite', 'Strict');
    }
    
    // Generate secure random string
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}
?>
