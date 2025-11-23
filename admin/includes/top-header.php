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
</div>

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