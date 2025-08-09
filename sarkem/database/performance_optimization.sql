-- Performance Optimization Script
-- This script adds indexes and optimizations for better performance

-- 1. Add indexes for frequently queried columns
CREATE INDEX idx_users_nama ON users(nama);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_password ON users(password);
CREATE INDEX idx_users_password_hash ON users(password_hash);

-- 2. Add indexes for foreign keys
CREATE INDEX idx_perbaikan_user_id ON perbaikan(user_id);
CREATE INDEX idx_perbaikan_tanggal ON perbaikan(tanggal);
CREATE INDEX idx_perbaikan_status ON perbaikan(status);

CREATE INDEX idx_absensi_user_id ON absensi(user_id);
CREATE INDEX idx_absensi_tanggal ON absensi(tanggal);

CREATE INDEX idx_gaji_user_id ON gaji(user_id);
CREATE INDEX idx_gaji_periode ON gaji(periode);

CREATE INDEX idx_kasbon_user_id ON kasbon(user_id);
CREATE INDEX idx_kasbon_tanggal ON kasbon(tanggal);

CREATE INDEX idx_pelanggan_created_at ON pelanggan(created_at);
CREATE INDEX idx_pelanggan_status ON pelanggan(status);

CREATE INDEX idx_barang_kategori ON barang(kategori);
CREATE INDEX idx_barang_stok ON barang(stok);

-- 3. Add composite indexes for complex queries
CREATE INDEX idx_perbaikan_user_tanggal ON perbaikan(user_id, tanggal);
CREATE INDEX idx_absensi_user_tanggal ON absensi(user_id, tanggal);
CREATE INDEX idx_gaji_user_periode ON gaji(user_id, periode);
CREATE INDEX idx_kasbon_user_tanggal ON kasbon(user_id, tanggal);

-- 4. Add indexes for search functionality
CREATE FULLTEXT INDEX idx_pelanggan_search ON pelanggan(nama, alamat, no_telepon);
CREATE FULLTEXT INDEX idx_barang_search ON barang(nama_barang, deskripsi);

-- 5. Add indexes for reporting
CREATE INDEX idx_perbaikan_status_tanggal ON perbaikan(status, tanggal);
CREATE INDEX idx_absensi_status_tanggal ON absensi(status, tanggal);
CREATE INDEX idx_gaji_status_periode ON gaji(status, periode);

-- 6. Add indexes for dashboard statistics
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_perbaikan_created_at ON perbaikan(created_at);
CREATE INDEX idx_pelanggan_created_at ON pelanggan(created_at);

-- 7. Optimize settings table
CREATE INDEX idx_settings_key ON settings(setting_key);
CREATE INDEX idx_settings_created_at ON settings(created_at);

-- 8. Add indexes for audit logs
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);

-- 9. Add indexes for user activities
CREATE INDEX idx_user_activities_user_id ON user_activities(user_id);
CREATE INDEX idx_user_activities_activity_type ON user_activities(activity_type);
CREATE INDEX idx_user_activities_created_at ON user_activities(created_at);

-- 10. Add indexes for login attempts
CREATE INDEX idx_login_attempts_ip_address ON login_attempts(ip_address);
CREATE INDEX idx_login_attempts_username ON login_attempts(username);
CREATE INDEX idx_login_attempts_attempt_time ON login_attempts(attempt_time);

-- 11. Add indexes for API rate limits
CREATE INDEX idx_api_rate_limits_endpoint_ip ON api_rate_limits(endpoint, ip_address);
CREATE INDEX idx_api_rate_limits_expires_at ON api_rate_limits(expires_at);

-- 12. Add indexes for password reset tokens
CREATE INDEX idx_password_reset_tokens_token ON password_reset_tokens(token);
CREATE INDEX idx_password_reset_tokens_user_id ON password_reset_tokens(user_id);
CREATE INDEX idx_password_reset_tokens_expires_at ON password_reset_tokens(expires_at);

-- 13. Add indexes for user sessions
CREATE INDEX idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_user_sessions_expires_at ON user_sessions(expires_at);
CREATE INDEX idx_user_sessions_created_at ON user_sessions(created_at);

-- 14. Add indexes for performance monitoring
CREATE INDEX idx_performance_logs_created_at ON performance_logs(created_at);
CREATE INDEX idx_performance_logs_endpoint ON performance_logs(endpoint);

-- 15. Add indexes for error logs
CREATE INDEX idx_error_logs_created_at ON error_logs(created_at);
CREATE INDEX idx_error_logs_severity ON error_logs(severity);

-- 16. Add indexes for cache tables
CREATE INDEX idx_cache_keys_key ON cache_keys(cache_key);
CREATE INDEX idx_cache_keys_expires_at ON cache_keys(expires_at);

-- 17. Add indexes for notification tables
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
CREATE INDEX idx_notifications_status ON notifications(status);

-- 18. Add indexes for backup logs
CREATE INDEX idx_backup_logs_created_at ON backup_logs(created_at);
CREATE INDEX idx_backup_logs_status ON backup_logs(status);

-- 19. Add indexes for system logs
CREATE INDEX idx_system_logs_created_at ON system_logs(created_at);
CREATE INDEX idx_system_logs_level ON system_logs(level);

-- 20. Add indexes for user preferences
CREATE INDEX idx_user_preferences_user_id ON user_preferences(user_id);
CREATE INDEX idx_user_preferences_key ON user_preferences(preference_key);

-- 21. Add indexes for file uploads
CREATE INDEX idx_file_uploads_user_id ON file_uploads(user_id);
CREATE INDEX idx_file_uploads_created_at ON file_uploads(created_at);
CREATE INDEX idx_file_uploads_type ON file_uploads(file_type);

-- 22. Add indexes for email logs
CREATE INDEX idx_email_logs_created_at ON email_logs(created_at);
CREATE INDEX idx_email_logs_status ON email_logs(status);

-- 23. Add indexes for SMS logs
CREATE INDEX idx_sms_logs_created_at ON sms_logs(created_at);
CREATE INDEX idx_sms_logs_status ON sms_logs(status);

-- 24. Add indexes for API logs
CREATE INDEX idx_api_logs_created_at ON api_logs(created_at);
CREATE INDEX idx_api_logs_endpoint ON api_logs(endpoint);

-- 25. Add indexes for security logs
CREATE INDEX idx_security_logs_created_at ON security_logs(created_at);
CREATE INDEX idx_security_logs_type ON security_logs(log_type);

-- 26. Add indexes for maintenance logs
CREATE INDEX idx_maintenance_logs_created_at ON maintenance_logs(created_at);
CREATE INDEX idx_maintenance_logs_status ON maintenance_logs(status);

-- 27. Add indexes for system settings
CREATE INDEX idx_system_settings_key ON system_settings(setting_key);
CREATE INDEX idx_system_settings_created_at ON system_settings(created_at);

-- 28. Add indexes for user roles
CREATE INDEX idx_user_roles_user_id ON user_roles(user_id);
CREATE INDEX idx_user_roles_role_id ON user_roles(role_id);

-- 29. Add indexes for permissions
CREATE INDEX idx_permissions_resource ON permissions(resource);
CREATE INDEX idx_permissions_action ON permissions(action);

-- 30. Add indexes for role permissions
CREATE INDEX idx_role_permissions_role_id ON role_permissions(role_id);
CREATE INDEX idx_role_permissions_permission_id ON role_permissions(permission_id);

-- 31. Add indexes for user permissions
CREATE INDEX idx_user_permissions_user_id ON user_permissions(user_id);
CREATE INDEX idx_user_permissions_permission_id ON user_permissions(permission_id);

-- 32. Add indexes for system configurations
CREATE INDEX idx_system_configurations_key ON system_configurations(config_key);
CREATE INDEX idx_system_configurations_created_at ON system_configurations(created_at);

-- 33. Add indexes for application logs
CREATE INDEX idx_application_logs_created_at ON application_logs(created_at);
CREATE INDEX idx_application_logs_level ON application_logs(level);

-- 34. Add indexes for database logs
CREATE INDEX idx_database_logs_created_at ON database_logs(created_at);
CREATE INDEX idx_database_logs_type ON database_logs(log_type);

-- 35. Add indexes for query logs
CREATE INDEX idx_query_logs_created_at ON query_logs(created_at);
CREATE INDEX idx_query_logs_duration ON query_logs(duration);

-- 36. Add indexes for performance metrics
CREATE INDEX idx_performance_metrics_created_at ON performance_metrics(created_at);
CREATE INDEX idx_performance_metrics_metric_type ON performance_metrics(metric_type);

-- 37. Add indexes for cache performance
CREATE INDEX idx_cache_performance_created_at ON cache_performance(created_at);
CREATE INDEX idx_cache_performance_cache_key ON cache_performance(cache_key);

-- 38. Add indexes for database performance
CREATE INDEX idx_database_performance_created_at ON database_performance(created_at);
CREATE INDEX idx_database_performance_table_name ON database_performance(table_name);

-- 39. Add indexes for system performance
CREATE INDEX idx_system_performance_created_at ON system_performance(created_at);
CREATE INDEX idx_system_performance_metric_type ON system_performance(metric_type);

-- 40. Add indexes for user performance
CREATE INDEX idx_user_performance_created_at ON user_performance(created_at);
CREATE INDEX idx_user_performance_user_id ON user_performance(user_id);

-- 41. Add indexes for application performance
CREATE INDEX idx_application_performance_created_at ON application_performance(created_at);
CREATE INDEX idx_application_performance_endpoint ON application_performance(endpoint);

-- 42. Add indexes for database optimization
CREATE INDEX idx_database_optimization_created_at ON database_optimization(created_at);
CREATE INDEX idx_database_optimization_table_name ON database_optimization(table_name);

-- 43. Add indexes for system optimization
CREATE INDEX idx_system_optimization_created_at ON system_optimization(created_at);
CREATE INDEX idx_system_optimization_optimization_type ON system_optimization(optimization_type);

-- 44. Add indexes for user optimization
CREATE INDEX idx_user_optimization_created_at ON user_optimization(created_at);
CREATE INDEX idx_user_optimization_user_id ON user_optimization(user_id);

-- 45. Add indexes for application optimization
CREATE INDEX idx_application_optimization_created_at ON application_optimization(created_at);
CREATE INDEX idx_application_optimization_optimization_type ON application_optimization(optimization_type);

-- 46. Add indexes for security optimization
CREATE INDEX idx_security_optimization_created_at ON security_optimization(created_at);
CREATE INDEX idx_security_optimization_optimization_type ON security_optimization(optimization_type);

-- 47. Add indexes for performance monitoring
CREATE INDEX idx_performance_monitoring_created_at ON performance_monitoring(created_at);
CREATE INDEX idx_performance_monitoring_monitoring_type ON performance_monitoring(monitoring_type);

-- 48. Add indexes for security monitoring
CREATE INDEX idx_security_monitoring_created_at ON security_monitoring(created_at);
CREATE INDEX idx_security_monitoring_monitoring_type ON security_monitoring(monitoring_type);

-- 49. Add indexes for system monitoring
CREATE INDEX idx_system_monitoring_created_at ON system_monitoring(created_at);
CREATE INDEX idx_system_monitoring_monitoring_type ON system_monitoring(monitoring_type);

-- 50. Add indexes for user monitoring
CREATE INDEX idx_user_monitoring_created_at ON user_monitoring(created_at);
CREATE INDEX idx_user_monitoring_user_id ON user_monitoring(user_id);
