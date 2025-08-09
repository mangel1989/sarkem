-- Insert default admin user with hashed password
INSERT INTO users (nama, no_wa, password, role, id_cabang) VALUES (
    'Admin Utama',
    '081234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    NULL
);
