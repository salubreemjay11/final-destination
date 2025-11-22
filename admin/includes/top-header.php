<?php
require_once '../config/database.php';
require_once 'auth.php';
requireLogin();

$currentUser = getCurrentUser();

// Initialize permission system
require_once 'permissions.php';
require_once 'permission-enforcer.php';

$permissionManager = new PermissionManager($pdo, $currentUser['role'], $currentUser['id']);
$permissionEnforcer = new PermissionEnforcer($permissionManager);

// Enforce page access for EVERY page
$permissionEnforcer->enforcePageAccess();

// Simple 2FA check - only redirect if specifically required
if (isset($_SESSION['requires_2fa']) && $_SESSION['requires_2fa'] === true) {
    if (basename($_SERVER['PHP_SELF']) !== 'login-2fa.php') {
        header('Location: login-2fa.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
<div class="dashboard-container">
        <!-- Top Header -->
        <div class="top-header">
            <div class="logo">Orphanfare Dashboard</div>
            <div class="user-info">
                <span class="user-status">Logged in as: <?php echo htmlspecialchars($currentUser['username'] ?? 'Admin'); ?> (<?php 
                    // Properly display role names
                    $roleDisplay = match($currentUser['role']) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'Social Worker' => 'Social Worker',
                        'Social Welfare Assistant' => 'Social Welfare Assistant',
                        default => ucfirst($currentUser['role'])
                    };
                    echo htmlspecialchars($roleDisplay); 
                ?>)</span>
                
                <!-- Add Request Role Button for non-superadmin users -->
                <?php if ($currentUser['role'] !== 'super_admin'): ?>
                    <a href="request-role.php" class="request-role-btn" style="background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; margin-right: 10px;">
                        Request Role Change
                    </a>
                <?php endif; ?>
                
                <!-- Theme Toggle Button -->
                <button id="themeToggle" class="theme-toggle">
                    <span class="icon">🌙</span>
                    <span class="text">Dark Mode</span>
                </button>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>
        </div>
</div>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        }
    </script>
</body>
</html>