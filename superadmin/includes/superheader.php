<?php
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

// Handle logout
if (isset($_GET['logout'])) {
    // Record logout in audit logs
    if (function_exists('recordAuditLog')) {
        recordAuditLog($conn, $_SESSION['user_id'], 'logout', 'User logged out of the system', $_SERVER['REMOTE_ADDR']);
    }
    
    session_destroy();
    header("Location: ../login.php");
    exit();
}

// Function declarations
if (!function_exists('recordAuditLog')) {
    function recordAuditLog($conn, $user_id, $action, $description = null, $ip_address = null) {
        try {
            $ip_address = $ip_address ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt = $conn->prepare("
                INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
            $stmt->execute();
            return true;
            
        } catch (Exception $e) {
            // Log error but don't break the application
            error_log("Audit log error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('recordAdminAuditLog')) {
    function recordAdminAuditLog($conn, $user_id, $action, $description = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $stmt = $conn->prepare("
                INSERT INTO audit_log_admin (user_id, action, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
            $stmt->execute();
            return true;
            
        } catch (Exception $e) {
            error_log("Admin audit log error: " . $e->getMessage());
            return false;
        }
    }
}

// Function to get total active users
function getTotalActiveUsers($conn) {
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check->num_rows == 0) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) as total FROM users WHERE status = 'active'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to get new users this month
function getNewUsersThisMonth($conn) {
    $table_check = $conn->query("SHOW TABLES LIKE 'users'");
    if ($table_check->num_rows == 0) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) as total FROM users WHERE status = 'active' 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Function to get pending role change requests
function getPendingRoleRequests($conn) {
    $table_check = $conn->query("SHOW TABLES LIKE 'role_change_requests'");
    if ($table_check->num_rows == 0) {
        return 0;
    }
    
    $sql = "SELECT COUNT(*) as total FROM role_change_requests WHERE status = 'pending'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    return 0;
}

// Helper function to get badge class based on action type
function getActionBadgeClass($action) {
    $action = strtolower($action);
    switch ($action) {
        case 'login': return 'status-active';
        case 'create': return 'status-approved';
        case 'edit': return 'status-progress';
        case 'delete': return 'status-urgent';
        case 'logout': return 'status-mild';
        default: return 'status-common';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Super Admin - Orphanfare'; ?></title>
    <link rel="stylesheet" href="../css/superadmin.css">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Top Header -->
        <div class="top-header">
            <img src="../img/logo-system.jpg" alt="Orphanfare Logo" class="logo-image">
            <div class="user-info">
                <span class="user-status">Logged in as: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?> (<?php echo htmlspecialchars($_SESSION['role'] === 'super_admin' ? 'Super Admin' : 'Admin'); ?>)</span>
                <!-- Theme Toggle Button -->
                <button id="themeToggle" class="theme-toggle">
                    <span class="theme-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4a90e2" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </span>
                    <span class="text">Dark Mode</span>
                </button>
                <button class="logout-btn" onclick="logout()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Logout
                </button>
            </div>
        </div>

        <!-- Sidebar Navigation-->
        <div class="sidebar">
            <div class="nav-section">
                <div class="nav-title">NAVIGATION</div>
                <a href="superadmin.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'superadmin.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Dashboard
                </a>
                <a href="user-management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'user-management.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    User management
                </a>
                <a href="role-permissions.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'role-permissions.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <circle cx="12" cy="8" r="7"/>
                        <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                    </svg>
                    Role & Permissions
                </a>
                <a href="event-types.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'event-types.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Event Types
                </a>
                <a href="system-configuration.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'system-configuration.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    System Configuration
                </a>
                <a href="audits-logs.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'audits-logs.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Audits Logs
                </a>
                <a href="custom-field.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'custom-field.php' ? 'active' : ''; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                        <line x1="3" y1="9" x2="21" y2="9"/>
                        <line x1="9" y1="21" x2="9" y2="9"/>
                    </svg>
                    Custom Field
                </a>
            </div>
        </div>

        <!--Main Content-->
        <div class="main-content">
            <script>
            function logout() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = '?logout=true';
                }
            }

            // Theme management
            function initializeTheme() {
                const savedTheme = localStorage.getItem('theme') || 'light';
                const htmlElement = document.documentElement;
                
                // Set initial theme
                htmlElement.setAttribute('data-theme', savedTheme);
                updateThemeToggle(savedTheme);
                
                // Add theme toggle event listener
                const themeToggle = document.getElementById('themeToggle');
                if (themeToggle) {
                    themeToggle.addEventListener('click', toggleTheme);
                }
            }

            function toggleTheme() {
                const htmlElement = document.documentElement;
                const currentTheme = htmlElement.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                // Update theme
                htmlElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeToggle(newTheme);
            }

            function updateThemeToggle(theme) {
                const themeToggle = document.getElementById('themeToggle');
                if (!themeToggle) return;
                
                const icon = themeToggle.querySelector('.theme-icon');
                const text = themeToggle.querySelector('.text');
                
                if (theme === 'dark') {
                    icon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
                    text.textContent = 'Light Mode';
                } else {
                    icon.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
                    text.textContent = 'Dark Mode';
                }
            }

            // Initialize theme when page loads
            document.addEventListener('DOMContentLoaded', initializeTheme);
            </script>