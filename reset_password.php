<?php
// reset_password.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

echo "<h2>Reset Super Admin Password</h2>";
echo "<style>body { font-family: Arial; margin: 20px; } .success { color: green; } .error { color: red; }</style>";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Reset the password to "password"
$new_password_hash = password_hash('password', PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = ? WHERE email = 'superadmin@orphanfare.com'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $new_password_hash);

if ($stmt->execute()) {
    echo "<p class='success'>✅ Password reset successfully!</p>";
    echo "<p><strong>New Password:</strong> password</p>";
    echo "<p><strong>Email:</strong> superadmin@orphanfare.com</p>";
    
    // Verify the password was updated
    $verify_sql = "SELECT password FROM users WHERE email = 'superadmin@orphanfare.com'";
    $result = $conn->query($verify_sql);
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify('password', $user['password'])) {
            echo "<p class='success'>✅ Password verification successful!</p>";
        } else {
            echo "<p class='error'>❌ Password verification failed!</p>";
        }
    }
} else {
    echo "<p class='error'>❌ Error resetting password: " . $conn->error . "</p>";
}

$stmt->close();
$conn->close();

echo "<br><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a>";
?>