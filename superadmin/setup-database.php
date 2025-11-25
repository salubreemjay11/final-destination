<?php
// setup-database.php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if user is logged in as Super Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit();
}

echo "<h2>Database Setup</h2>";

// Create or update role_change_requests table
$sql = "
CREATE TABLE IF NOT EXISTS role_change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    current_role_value VARCHAR(50) NOT NULL,
    requested_role_value VARCHAR(50) NOT NULL,
    request_reason TEXT,
    request_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by_user INT NULL,
    reviewed_at_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>✓ role_change_requests table created/updated successfully</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating role_change_requests table: " . $conn->error . "</p>";
}

// Add status column to users table if it doesn't exist
$check_status = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
if ($check_status->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added status column to users table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding status column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ status column already exists in users table</p>";
}

// Add created_at column to users table if it doesn't exist
$check_created = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($check_created->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✓ Added created_at column to users table</p>";
    } else {
        echo "<p style='color: red;'>✗ Error adding created_at column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color: green;'>✓ created_at column already exists in users table</p>";
}

echo "<h3>Setup Complete</h3>";
echo "<p><a href='superadmin.php'>Go to Super Admin Dashboard</a></p>";

$conn->close();
?>