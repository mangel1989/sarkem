<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid CSRF token';
    } else {
        // Get client IP for rate limiting
        $client_ip = $_SERVER['REMOTE_ADDR'];
        
        // Check rate limiting
        if (!Security::checkLoginAttempts($client_ip)) {
            $error_message = 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.';
        } else {
            // Sanitize input
            $username = Security::sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Validate input
            if (empty($username) || empty($password)) {
                $error_message = 'Username dan password harus diisi';
            } else {
                try {
                    // Use prepared statement to prevent SQL injection
                    $stmt = $conn->prepare("SELECT id, username, password, role, nama, status FROM users WHERE username = ? AND status = 'active'");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Verify password using password_verify
                        if (password_verify($password, $user['password'])) {
                            // Reset login attempts
                            Security::resetLoginAttempts($client_ip);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'] ?? $user['nama'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['nama'] = $user['nama'];
                            $_SESSION['login_time'] = time();
                            
                            // Regenerate session ID for security
                            session_regenerate_id(true);
                            
                            // Redirect based on role
                            switch ($user['role']) {
                                case 'admin':
                                    header('Location: admin/dashboard.php');
                                    break;
                                case 'owner':
                                    header('Location: owner/dashboard.php');
                                    break;
                                case 'teknisi':
                                    header('Location: teknisi/dashboard.php');
                                    break;
                                default:
                                    $error_message = 'Role tidak valid';
                            }
                            exit();
                        } else {
                            // Increment login attempts
                            Security::incrementLoginAttempts($client_ip);
                            $error_message = 'Username atau password salah';
                        }
                    } else {
                        // Increment login attempts
                        Security::incrementLoginAttempts($client_ip);
                        $error_message = 'Username atau password salah';
                    }
                    
                    $stmt->close();
                } catch (Exception $e) {
                    $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                    // Log error for debugging
                    error_log("Login error: " . $e->getMessage());
                    // Debug output
                    echo "Debug: " . $e->getMessage();
                }
            }
        }
    }
}

// Generate CSRF token for form
$csrf_token = Security::generateCSRFToken();

// Return to login page with error
$_SESSION['login_error'] = $error_message;
header('Location: ../index.php');
exit();
?>
