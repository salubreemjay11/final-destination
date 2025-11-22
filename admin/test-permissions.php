<?php
// test-permissions.php
require_once 'includes/header.php';

echo "<h1>Permission System Test</h1>";

// Test all modules and actions
$modules = [
    'dashboard', 'child_management', 'case_management', 'donation', 
    'inventory', 'foster_info', 'schedule', 'reports', 'settings'
];
$actions = ['view', 'create', 'edit', 'delete'];

echo "<h2>Current User: {$currentUser['username']} ({$currentUser['role']})</h2>";

echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background-color: #2a2a2a; color: white;'>";
echo "<th style='padding: 10px;'>Module</th>";
foreach ($actions as $action) {
    echo "<th style='padding: 10px; text-align: center;'>$action</th>";
}
echo "</tr>";

foreach ($modules as $module) {
    echo "<tr>";
    echo "<td style='padding: 10px; background-color: #3a3a3a; color: white;'><strong>$module</strong></td>";
    foreach ($actions as $action) {
        $hasPerm = $permissionManager->hasPermission($module, $action);
        $color = $hasPerm ? '#28a745' : '#dc3545';
        echo "<td style='padding: 10px; text-align: center; background-color: $color; color: white; font-weight: bold;'>" . ($hasPerm ? '✓ YES' : '✗ NO') . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Test page access
echo "<h2>Page Access Test</h2>";
$pages = [
    'dashboard.php' => 'Dashboard',
    'child-management.php' => 'Child Management', 
    'case-management.php' => 'Case Management',
    'donation.php' => 'Donation',
    'inventory.php' => 'Inventory',
    'foster-info.php' => 'Foster Info',
    'schedule.php' => 'Schedule',
    'reports.php' => 'Reports',
    'settings.php' => 'Settings'
];

echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
echo "<tr style='background-color: #2a2a2a; color: white;'>";
echo "<th style='padding: 10px;'>Page</th>";
echo "<th style='padding: 10px;'>Access</th>";
echo "</tr>";

foreach ($pages as $page => $name) {
    $canAccess = $permissionManager->canAccessPage($page);
    $color = $canAccess ? '#28a745' : '#dc3545';
    $text = $canAccess ? '✓ ACCESS GRANTED' : '✗ ACCESS DENIED';
    echo "<tr>";
    echo "<td style='padding: 10px; background-color: #3a3a3a; color: white;'><strong>$name</strong> ($page)</td>";
    echo "<td style='padding: 10px; text-align: center; background-color: $color; color: white; font-weight: bold;'>$text</td>";
    echo "</tr>";
}
echo "</table>";
?>