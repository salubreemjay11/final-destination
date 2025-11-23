<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Login Debug Test</h1>";
echo "<div style='background: #ff4444; color: white; padding: 20px; margin: 10px;'>";

// Get current user data
$test_email = "admin@orphanfare.org"; // Change to your test email
$sql = "SELECT id, username, email, failed_attempts, account_locked FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

echo "<h3>Current User Data:</h3>";
echo "User ID: " . ($user['id'] ?? 'Not found') . "<br>";
echo "Username: " . ($user['username'] ?? 'Not found') . "<br>";
echo "Failed Attempts: " . ($user['failed_attempts'] ?? '0') . "<br>";
echo "Account Locked: " . ($user['account_locked'] ?? '0') . "<br>";

$stmt->close();

// Form to manually update failed attempts
echo "<h3>Manual Update:</h3>";
echo "<form method='POST'>";
echo "Set failed attempts to: <input type='number' name='manual_attempts' value='".($user['failed_attempts'] ?? 0)."'>";
echo "<button type='submit' name='update'>Update</button>";
echo "</form>";

if (isset($_POST['update'])) {
    $manual_attempts = $_POST['manual_attempts'];
    $update_sql = "UPDATE users SET failed_attempts = ? WHERE email = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("is", $manual_attempts, $test_email);
    
    if ($update_stmt->execute()) {
        echo "<p style='color: green;'>Updated failed_attempts to: $manual_attempts</p>";
    } else {
        echo "<p style='color: red;'>Update failed</p>";
    }
    $update_stmt->close();
}

echo "</div>";

// Test the actual login process
echo "<h3>Test Login Process:</h3>";
echo "<form method='POST' action='login.php'>";
echo "Email: <input type='email' name='email' value='$test_email'><br>";
echo "Password: <input type='password' name='password' value='wrongpassword'><br>";
echo "<button type='submit'>Test Failed Login</button>";
echo "</form>";

$conn->close();
?>