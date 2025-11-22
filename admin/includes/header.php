<?php
if (!ob_get_status()) {
    ob_start();
}

// Suppress errors in production, but log them
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/database.php';
require_once 'auth.php';
requireLogin();

$currentUser = getCurrentUser();

$fieldManager = null;
try {
    $possiblePaths = [
        $_SERVER['DOCUMENT_ROOT'] . '/superadmin/includes/CustomFieldManager.php',
        __DIR__ . '/../../superadmin/includes/CustomFieldManager.php',
        '../superadmin/includes/CustomFieldManager.php'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $fieldManager = new CustomFieldManager($pdo);
            break;
        }
    }
    
    if (!$fieldManager) {
        error_log("CustomFieldManager not found in any path");
    }
} catch (Exception $e) {
    error_log("CustomFieldManager initialization failed: " . $e->getMessage());
    // Continue without custom fields - don't break the site
}

// Force role update from database
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$currentUser['id']]);
    $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Always update session role from database
    if ($dbUser && $dbUser['role']) {
        $_SESSION['role'] = $dbUser['role'];
        // Re-get current user with updated session
        $currentUser = getCurrentUser();
    }
} catch (Exception $e) {
    // Continue anyway
}

// Initialize permission system
require_once 'permissions.php';
require_once 'permission-enforcer.php';

$permissionManager = new PermissionManager($pdo, $currentUser['role'], $currentUser['id']);
$permissionEnforcer = new PermissionEnforcer($permissionManager);

// Enforce page access for EVERY page
$permissionEnforcer->enforcePageAccess();

// Check if current page is accessible
$currentPage = basename($_SERVER['PHP_SELF']);

// DEBUG: Check what's happening
echo "<!-- PERMISSION CHECK: Current page: $currentPage -->";
echo "<!-- PERMISSION CHECK: User role: " . $currentUser['role'] . " -->";

if (!$permissionManager->canAccessPage($currentPage)) {
    echo "<!-- PERMISSION CHECK: ACCESS DENIED - Redirecting to access-denied.php -->";
    header('Location: access-denied.php');
    exit();
}

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
    <title><?php echo $pageTitle ?? 'Orphanfare Dashboard'; ?></title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Top Header -->
        <div class="top-header">
            <img src="../img/logo-system.jpg" alt="Orphanfare Logo" class="logo-image">
            
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

                <?php if ($currentUser['role'] === 'user' || $currentUser['role'] === 'Social Welfare Assistant'): ?>
                    <a href="request-role.php" class="request-role-btn" style="background: #28a745; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; margin-right: 10px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 5px;">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/>
                            <line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Request Role Change
                    </a>
                <?php endif; ?>
                
                <!-- Theme Toggle Button -->
                <button id="themeToggle" class="theme-toggle">
                    <span class="theme-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4a90e2" stroke-width="2" class="moon-icon">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f5a623" stroke-width="2" class="sun-icon" style="display: none;">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                    </span>
                    <span class="text">Dark Mode</span>
                </button>

                <!-- Logout Button -->
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

        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="nav-section">
                <div class="nav-title">NAVIGATION</div>
                
                <!-- Dashboard -->
                <?php if (showMenuItem($permissionManager, 'dashboard')): ?>
                    <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Dashboard
                    </a>
                <?php endif; ?>
                
                <!-- Child Management -->
                <?php if (showMenuItem($permissionManager, 'child_management')): ?>
                    <a href="child-management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'child-management.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Child management
                    </a>
                <?php endif; ?>
                
                <!-- Case Management -->
                <?php if (showMenuItem($permissionManager, 'case_management')): ?>
                    <a href="case-management.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'case-management.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Case management
                    </a>
                <?php endif; ?>
                
                <!-- Donation -->
                <?php if (showMenuItem($permissionManager, 'donation')): ?>
                    <a href="donation.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'donation.php' ? 'active' : ''; ?>">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M8 7.982C9.664 6.309 13.825 9.236 8 13 2.175 9.236 6.336 6.31 8 7.982"/>
                            <path d="M3.75 0a1 1 0 0 0-.8.4L.1 4.2a.5.5 0 0 0-.1.3V15a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V4.5a.5.5 0 0 0-.1-.3L13.05.4a1 1 0 0 0-.8-.4zm0 1H7.5v3h-6zM8.5 4V1h3.75l2.25 3zM15 5v10H1V5z"/>
                        </svg>
                        Donation
                    </a>
                <?php endif; ?>
                
                <!-- Foster Information -->
                <?php if (showMenuItem($permissionManager, 'foster_info')): ?>
                    <a href="foster-info.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'foster-info.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Foster Information
                    </a>
                <?php endif; ?>
                
                <!-- Schedule & Events -->
                <?php if (showMenuItem($permissionManager, 'schedule')): ?>
                    <a href="schedule.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Schedule & Events
                    </a>
                <?php endif; ?>
                
                <!-- Reports -->
                <?php if (showMenuItem($permissionManager, 'reports')): ?>
                    <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Reports
                    </a>
                <?php endif; ?>
                
                <!-- Settings -->
                <?php if (showMenuItem($permissionManager, 'settings')): ?>
                    <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        Settings
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chart.js for Dashboard Charts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../logout.php';
            }
        }

        // Theme management
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
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
            const currentTheme = htmlElement.getAttribute('data-theme') || 'dark';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            // Update theme
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeToggle(newTheme);
        }

        function updateThemeToggle(theme) {
            const themeToggle = document.getElementById('themeToggle');
            if (!themeToggle) return;
            
            const moonIcon = themeToggle.querySelector('.moon-icon');
            const sunIcon = themeToggle.querySelector('.sun-icon');
            const text = themeToggle.querySelector('.text');
            
            if (theme === 'dark') {
                moonIcon.style.display = 'block';
                sunIcon.style.display = 'none';
                text.textContent = 'Dark Mode';
            } else {
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'block';
                text.textContent = 'Light Mode';
            }
        }

        // Initialize theme when page loads
        document.addEventListener('DOMContentLoaded', initializeTheme);
        </script>