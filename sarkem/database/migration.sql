-- Database Migration Script for Security Enhancement
-- This script updates the database structure for security improvements

-- 1. Update users table to use password hashing
ALTER TABLE users 
ADD COLUMN password_hash VARCHAR(255) AFTER password;

-- 2. Create login attempts table for rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN DEFAULT FALSE,
    user_agent TEXT,
    INDEX idx_ip_time (ip_address, attempt_time),
    INDEX idx_username_time (username, attempt_time)
);

-- 3. Create sessions table for better session management
CREATE TABLE IF NOT EXISTS user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_expires (expires_at)
);

-- 4. Create audit log table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action),
    INDEX idx_created_at (created_at)
);

-- 5. Add indexes for performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- 6. Update existing passwords to use hashing (run this after implementing password hashing)
-- UPDATE users SET password_hash = password WHERE password_hash IS NULL;

-- 7. Add password reset token table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- 8. Add API rate limiting table
CREATE TABLE IF NOT EXISTS api_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    request_count INT DEFAULT 1,
    window_start DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX idx_endpoint_ip (endpoint, ip_address),
    INDEX idx_expires (expires_at)
);

-- 9. Add user activity log
CREATE TABLE IF NOT EXISTS user_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_activity (user_id, activity_type),
    INDEX idx_created_at (created_at)
);

-- 10. Add failed login attempts cleanup procedure
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS cleanup_old_login_attempts()
BEGIN
    DELETE FROM login_attempts 
    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
END$$
DELIMITER ;

-- 11. Create event for automatic cleanup
CREATE EVENT IF NOT EXISTS cleanup_login_attempts
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO CALL cleanup_old_login_attempts();

-- 12. Add security settings to settings table
INSERT INTO settings (setting_key, setting_value, description) VALUES
('max_login_attempts', '5', 'Maximum login attempts before lockout'),
('lockout_duration', '900', 'Lockout duration in seconds (15 minutes)'),
('session_lifetime', '3600', 'Session lifetime in seconds (1 hour)'),
('password_min_length', '8', 'Minimum password length'),
('require_strong_password', '1', 'Require strong password policy'),
('enable_2fa', '0', 'Enable two-factor authentication'),
('enable_login_notifications', '1', 'Enable email notifications for new logins')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
