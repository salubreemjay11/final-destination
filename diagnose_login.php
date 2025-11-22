<?php
// diagnose_login.php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

echo "<h2>Login System Diagnosis</h2>";
echo "<style>body { font-family: Arial; margin: 20px; } .success { color: green; } .error { color: red; } .info { background: #f0f0f0; padding: 10px; margin: 10px 0; }</style>";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit();
} else {
    echo "<p class='success'>✅ Database connected successfully</p>";
}

// Check if database exists
$db_check = $conn->select_db($dbname);
if (!$db_check) {
    echo "<p class='error'>❌ Database '$dbname' does not exist!</p>";
    echo "<p>Run setup_database.php first</p>";
    exit();
}

// Check tables
$tables = ['users', 'audit_logs', 'role_change_requests'];
foreach ($tables as $table) {
    $table_check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($table_check->num_rows > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' does not exist!</p>";
    }
}

// Check super admin user
$sql = "SELECT id, username, email, password, role, status FROM users WHERE role = 'super_admin'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<p class='success'>✅ Super admin user(s) found:</p>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['role'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
        
        // Test password
        $test_password = 'password';
        if (password_verify($test_password, $row['password'])) {
            echo "<tr><td colspan='5' class='success'>✅ Password 'password' works for this user</td></tr>";
        } else {
            echo "<tr><td colspan='5' class='error'>❌ Password 'password' does NOT work for this user</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No super admin users found in database!</p>";
}

$conn->close();

echo "<div class='info'>";
echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>If no super admin exists, run the fix below</li>";
echo "<li>If password doesn't work, reset it</li>";
echo "<li>If tables don't exist, run setup_database.php</li>";
echo "</ul>";
echo "</div>";

echo "<br><a href='fix_superadmin.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fix Super Admin User</a>";
?>