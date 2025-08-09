<?php
// Setup Database Script
// This script will guide you through setting up the database

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sarkem_db';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected successfully\n";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$database' created successfully\n";
} else {
    echo "❌ Error creating database: " . $conn->error . "\n";
}

// Select database
$conn->select_db($database);

// Read and execute main database schema
$mainSchema = file_get_contents('database/sarkem.sql');
if ($mainSchema) {
    if ($conn->multi_query($mainSchema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "✅ Main database schema imported successfully\n";
    } else {
        echo "❌ Error importing main schema: " . $conn->error . "\n";
    }
}

// Read and execute migration schema
$migrationSchema = file_get_contents('database/migration.sql');
if ($migrationSchema) {
    if ($conn->multi_query($migrationSchema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "✅ Migration schema imported successfully\n";
    } else {
        echo "❌ Error importing migration schema: " . $conn->error . "\n";
    }
}

// Read and execute performance optimization
$performanceSchema = file_get_contents('database/performance_optimization.sql');
if ($performanceSchema) {
    if ($conn->multi_query($performanceSchema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "✅ Performance optimization imported successfully\n";
    } else {
        echo "❌ Error importing performance optimization: " . $conn->error . "\n";
    }
}

// Read and execute seed data
$seedData = file_get_contents('database/seed_security.sql');
if ($seedData) {
    if ($conn->multi_query($seedData)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        echo "✅ Seed data imported successfully\n";
    } else {
        echo "❌ Error importing seed data: " . $conn->error . "\n";
    }
}

// Update existing passwords to use hashing
$updatePasswords = [
    "UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin'",
    "UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'owner'",
    "UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'teknisi1'",
    "UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'teknisi2'"
];

foreach ($updatePasswords as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Password updated successfully\n";
    } else {
        echo "❌ Error updating password: " . $conn->error . "\n";
    }
}

// Create cache directory if not exists
if (!is_dir('cache')) {
    mkdir('cache', 0755, true);
    echo "✅ Cache directory created\n";
}

// Create logs directory if not exists
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
    echo "✅ Logs directory created\n";
}

// Create uploads directory if not exists
if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
    echo "✅ Uploads directory created\n";
}

// Create backups directory if not exists
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
    echo "✅ Backups directory created\n";
}

// Display final instructions
echo "\n🎉 Database setup completed!\n\n";
echo "📋 Next steps:\n";
echo "1. Login dengan credentials berikut:\n";
echo "   - Username: admin, Password: password\n";
echo "   - Username: owner, Password: password\n";
echo "   - Username: teknisi1, Password: password\n";
