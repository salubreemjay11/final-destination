<?php
// setup_database.php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS orphanfare_db";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("orphanfare_db");

// SQL schema
$sql = "
-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Role change requests table
CREATE TABLE IF NOT EXISTS role_change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('admin', 'user') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    requested_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample super admin (use a proper hashed password in production)
INSERT IGNORE INTO users (username, email, password, role, status) 
VALUES ('superadmin', 'superadmin@orphanfare.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'super_admin', 'active');

-- Insert some sample users
INSERT IGNORE INTO users (username, email, password, role, status) VALUES 
('john_doe', 'john@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'user', 'active'),
('jane_smith', 'jane@example.com', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'admin', 'active');
";

// Execute multi query
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "Tables created and sample data inserted successfully!<br>";
} else {
    echo "Error creating tables: " . $conn->error . "<br>";
}

$conn->close();
echo "Database setup complete! <a href='superadmin.php'>Go to Dashboard</a>";
?>