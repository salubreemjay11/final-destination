<?php
// fix_superadmin.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

echo "<h2>Fixing Super Admin User</h2>";
echo "<style>body { font-family: Arial; margin: 20px; } .success { color: green; } .error { color: red; }</style>";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// First, ensure the database and tables exist
$setup_sql = "
CREATE DATABASE IF NOT EXISTS $dbname;
USE $dbname;

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

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

if ($conn->multi_query($setup_sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "<p class='success'>✅ Database and tables created/verified</p>";
}

// Create or update super admin user
$password_hash = password_hash('password', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (username, email, password, role, status) 
        VALUES ('superadmin', 'superadmin@orphanfare.com', '$password_hash', 'super_admin', 'active')
        ON DUPLICATE KEY UPDATE 
        password = VALUES(password),
        status = VALUES(status)";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<p class='success'>✅ Super admin user created/updated successfully!</p>";
    } else {
        echo "<p class='success'>✅ Super admin user already exists and is up to date</p>";
    }
} else {
    echo "<p class='error'>❌ Error: " . $conn->error . "</p>";
}

// Verify the user was created
$result = $conn->query("SELECT * FROM users WHERE email = 'superadmin@orphanfare.com'");
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Super Admin User Details:</h3>";
    echo "<p><strong>Username:</strong> " . $user['username'] . "</p>";
    echo "<p><strong>Email:</strong> " . $user['email'] . "</p>";
    echo "<p><strong>Role:</strong> " . $user['role'] . "</p>";
    echo "<p><strong>Status:</strong> " . $user['status'] . "</p>";
    echo "<p><strong>Test Password:</strong> password</p>";
    echo "</div>";
}

$conn->close();

echo "<br><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
echo "&nbsp;&nbsp;";
echo "<a href='diagnose_login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Diagnosis Again</a>";
?>