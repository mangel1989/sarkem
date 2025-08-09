
<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sarkem_db');

// Security configurations
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// Create MySQLi connection using object-oriented approach
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection and handle errors
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset to utf8mb4 for full UTF-8 support
$conn->set_charset("utf8mb4");

// Set timezone
$conn->query("SET time_zone = '+07:00'");

// Include security helper
require_once __DIR__ . '/helpers/Security.php';

// Initialize secure session
Security::secureSession();
session_start();

// Regenerate session ID for security
if (!isset($_SESSION['initialized'])) {
    session_regenerate_id(true);
    $_SESSION['initialized'] = true;
}
?>
