<?php
// quick_fix.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

echo "<h2>Quick Fix - Complete System Setup</h2>";
echo "<style>
    body { font-family: Arial; margin: 20px; } 
    .success { color: green; } 
    .error { color: red; } 
    .info { background: #e7f3ff; padding: 10px; margin: 10px 0; }
    .account-box { background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
    .demo-accounts { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
    .role-info { background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
</style>";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Ensure database and tables exist with updated schema
echo "<p class='info'>Step 1: Checking/updating database structure...</p>";

$setup_sql = "
CREATE DATABASE IF NOT EXISTS $dbname;
USE $dbname;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'user', 'Social Worker', 'Social Welfare Assistant') DEFAULT 'user',
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
    echo "<p class='success'>✅ Database and tables verified/created</p>";
}

// Step 2: Create accounts with different roles
echo "<p class='info'>Step 2: Creating accounts with different roles...</p>";

$accounts = [
    // Super Admin accounts
    ['superadmin@orphanfare.com', 'superadmin', 'password', 'super_admin'],
    ['admin@orphanfare.com', 'admin', 'admin123', 'admin'],
    ['superuser@orphanfare.com', 'superuser', 'superuser123', 'super_admin'],
    
    // Regular admin accounts
    ['manager@orphanfare.com', 'manager', 'manager123', 'admin'],
    
    // Social Worker roles
    ['socialworker@orphanfare.com', 'socialworker', 'social123', 'Social Worker'],
    ['maria.santos@orphanfare.com', 'mariasantos', 'maria123', 'Social Worker'],
    
    // Social Welfare Assistant
    ['assistant@orphanfare.com', 'assistant', 'assist123', 'Social Welfare Assistant'],
    ['john.doe@orphanfare.com', 'johndoe', 'john123', 'Social Welfare Assistant'],
    
    // Regular users
    ['user@orphanfare.com', 'regularuser', 'user123', 'user'],
    ['staff@orphanfare.com', 'staff', 'staff123', 'user']
];

foreach ($accounts as $account) {
    $email = $account[0];
    $username = $account[1];
    $password = $account[2];
    $role = $account[3];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, role, status) 
            VALUES (?, ?, ?, ?, 'active')
            ON DUPLICATE KEY UPDATE 
            password = VALUES(password),
            role = VALUES(role),
            status = VALUES(status)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $password_hash, $role);
    
    if ($stmt->execute()) {
        echo "<p class='success'>✅ $role account: $email / $password</p>";
    } else {
        echo "<p class='error'>❌ Failed to create $email: " . $conn->error . "</p>";
    }
    $stmt->close();
}

// Step 3: Verify all accounts are working
echo "<p class='info'>Step 3: Verifying all accounts...</p>";

$verify_sql = "SELECT username, email, password, role, status FROM users ORDER BY role, username";
$result = $conn->query($verify_sql);

if ($result->num_rows > 0) {
    echo "<div class='account-box'>";
    echo "<h3>All System Accounts:</h3>";
    
    $current_role = '';
    while ($user = $result->fetch_assoc()) {
        if ($user['role'] !== $current_role) {
            $current_role = $user['role'];
            echo "<h4 style='color: #155724; margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #28a745;'>$current_role Accounts:</h4>";
        }
        
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<p><strong>Username:</strong> " . $user['username'] . "</p>";
        echo "<p><strong>Email:</strong> " . $user['email'] . "</p>";
        echo "<p><strong>Role:</strong> " . $user['role'] . "</p>";
        echo "<p><strong>Status:</strong> " . $user['status'] . "</p>";
        
        // Test passwords
        $test_passwords = [
            'superadmin@orphanfare.com' => 'password',
            'admin@orphanfare.com' => 'admin123', 
            'superuser@orphanfare.com' => 'superuser123',
            'manager@orphanfare.com' => 'manager123',
            'socialworker@orphanfare.com' => 'social123',
            'maria.santos@orphanfare.com' => 'maria123',
            'assistant@orphanfare.com' => 'assist123',
            'john.doe@orphanfare.com' => 'john123',
            'user@orphanfare.com' => 'user123',
            'staff@orphanfare.com' => 'staff123'
        ];
        
        $test_password = $test_passwords[$user['email']] ?? 'password';
        
        if (password_verify($test_password, $user['password'])) {
            echo "<p class='success'><strong>✅ Password Test:</strong> Works with '$test_password'</p>";
        } else {
            echo "<p class='error'><strong>❌ Password Test:</strong> Failed with '$test_password'</p>";
        }
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='error'>❌ No accounts found in database!</p>";
}

$admin_tables_sql = "
USE $dbname;

CREATE TABLE IF NOT EXISTS children (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id VARCHAR(20) UNIQUE,
    name VARCHAR(100) NOT NULL,
    status ENUM('In Care', 'Adopted', 'Reintegrated') DEFAULT 'In Care',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_id VARCHAR(20) UNIQUE,
    status ENUM('Open', 'Closed', 'Under Investigation') DEFAULT 'Open',
    priority ENUM('urgent', 'high', 'medium', 'low') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO children (child_id, name, status) VALUES 
('CH-2024-001', 'Juan Dela Cruz', 'In Care'),
('CH-2024-002', 'Maria Santos', 'In Care'),
('CH-2024-003', 'Pedro Reyes', 'Adopted');

INSERT IGNORE INTO cases (case_id, status, priority) VALUES 
('CS-2024-001', 'Open', 'urgent'),
('CS-2024-002', 'Under Investigation', 'high'),
('CS-2024-003', 'Open', 'medium');
";

if ($conn->multi_query($admin_tables_sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    echo "<p class='success'>✅ Admin dashboard tables created</p>";
}

$conn->close();

echo "<div class='role-info'>";
echo "<h3>🎯 Role-Based Dashboard Access:</h3>";
echo "<p><strong>Super Admin</strong> → Super Admin Dashboard (superadmin/superadmin.php) - Full system control</p>";
echo "<p><strong>Admin</strong> → Main Dashboard (dashboard.php) - Administrative access</p>";
echo "<p><strong>Social Worker</strong> → Main Dashboard (admin/dashboard.php) - Case management access</p>";
echo "<p><strong>Social Welfare Assistant</strong> → Main Dashboard (dashboard.php) - Basic access</p>";
echo "<p><strong>User</strong> → Main Dashboard (dashboard.php) - Limited access</p>";
echo "</div>";

echo "<div class='demo-accounts'>";
echo "<h3>🔑 Test Login Credentials:</h3>";
echo "<p><strong>Super Admin:</strong> superadmin@orphanfare.com / password</p>";
echo "<p><strong>Admin:</strong> admin@orphanfare.com / admin123</p>";
echo "<p><strong>Social Worker:</strong> socialworker@orphanfare.com / social123</p>";
echo "<p><strong>Assistant:</strong> assistant@orphanfare.com / assist123</p>";
echo "<p><strong>Regular User:</strong> user@orphanfare.com / user123</p>";
echo "<p><em>Each role will be redirected to the appropriate dashboard!</em></p>";
echo "</div>";

echo "<div class='info'>";
echo "<h3>🔧 What this fix does:</h3>";
echo "<ul>";
echo "<li>Updates database schema to support Social Worker roles</li>";
echo "<li>Creates accounts with ALL role types (super_admin, admin, Social Worker, etc.)</li>";
echo "<li>Sets all accounts to 'active' status</li>";
echo "<li>Verifies passwords work correctly</li>";
echo "<li>Enables role-based dashboard redirection</li>";
echo "</ul>";
echo "</div>";

echo "<br>";
echo "<a href='login.php' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; margin-right: 10px;'>🚀 Go to Login Page</a>";
echo "<a href='diagnose_login.php' style='background: #17a2b8; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px; margin-right: 10px;'>🔍 Diagnose Login</a>";
echo "<a href='superadmin/superadmin.php' style='background: #007bff; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-size: 16px;'>📊 Go to Dashboard</a>";
?>