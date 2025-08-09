-- Security Enhancement Seed Data
-- This script adds sample data for testing security features

-- Insert test users with hashed passwords
-- Password: admin123
INSERT INTO users (username, password, role, nama, email, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrator', 'admin@sarkem.com', 'active'),
('owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'owner', 'Owner', 'owner@sarkem.com', 'active'),
('teknisi1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi', 'Teknisi 1', 'teknisi1@sarkem.com', 'active'),
('teknisi2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teknisi', 'Teknisi 2', 'teknisi2@sarkem.com', 'active');

-- Insert security settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('max_login_attempts', '5', 'Maximum login attempts before lockout'),
('lockout_duration', '900', 'Lockout duration in seconds (15 minutes)'),
('session_lifetime', '3600', 'Session lifetime in seconds (1 hour)'),
('password_min_length', '8', 'Minimum password length'),
('require_strong_password', '1', 'Require strong password policy'),
('enable_2fa', '0', 'Enable two-factor authentication'),
('enable_login_notifications', '1', 'Enable email notifications for new logins'),
('enable_audit_log', '1', 'Enable audit logging'),
('enable_rate_limiting', '1', 'Enable rate limiting for login attempts'),
('enable_session_management', '1', 'Enable advanced session management');

-- Insert sample audit logs
INSERT INTO audit_logs (user_id, action, table_name, record_id, description, ip_address) VALUES
(1, 'LOGIN', 'users', 1, 'User login successful', '127.0.0.1'),
(1, 'CREATE', 'pelanggan', 1, 'Created new customer record', '127.0.0.1'),
(1, 'UPDATE', 'users', 1, 'Updated user profile', '127.0.0.1');

-- Insert sample user activities
INSERT INTO user_activities (user_id, activity_type, description, ip_address) VALUES
(1, 'LOGIN', 'User logged in successfully', '127.0.0.1'),
(1, 'VIEW', 'Viewed dashboard', '127.0.0.1'),
(1, 'CREATE', 'Created new customer record', '127.0.0.1');

-- Insert sample security settings
INSERT INTO settings (setting_key, setting_value, description) VALUES
('security_level', 'high', 'Current security level'),
('enable_captcha', '0', 'Enable CAPTCHA for login'),
('enable_ip_whitelist', '0', 'Enable IP whitelist for admin access'),
('enable_brute_force_protection', '1', 'Enable brute force protection'),
('enable_session_timeout', '1', 'Enable session timeout'),
('enable_password_expiry', '0', 'Enable password expiry policy'),
('password_expiry_days', '90', 'Password expiry in days'),
('enable_login_history', '1', 'Enable login history tracking');

-- Create indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
CREATE INDEX idx_user_activities_created_at ON user_activities(created_at);
