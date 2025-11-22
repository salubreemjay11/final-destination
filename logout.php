<?php
// logout.php
session_start();

// Record logout in audit logs if user was logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "orphanfare";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if (!$conn->connect_error) {
        $table_check = $conn->query("SHOW TABLES LIKE 'audit_logs'");
        if ($table_check && $table_check->num_rows > 0) {
            $sql = "INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent) VALUES (?, 'logout', 'User logged out of the system', ?, ?)";
            $stmt = $conn->prepare($sql);
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $stmt->bind_param("iss", $_SESSION['user_id'], $_SERVER['REMOTE_ADDR'], $user_agent);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
    }
}

// Destroy all session data
$_SESSION = array();

// Destroy the session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>