<?php
require_once 'includes/header.php';

echo "<h1>System-Wide Permission Test</h1>";

// Test all modules and actions
$testModules = [
    'case_management' => ['Case Management', 'case-management.php'],
    'child_management' => ['Child Management', 'child-management.php'],
    'donation' => ['Donation', 'donation.php'],
    'inventory' => ['Inventory', 'inventory.php'],
    'foster_info' => ['Foster Info', 'foster-info.php'],
    'schedule' => ['Schedule', 'schedule.php'],
    'reports' => ['Reports', 'reports.php'],
    'settings' => ['Settings', 'settings.php']
];

$actions = ['view', 'create', 'edit', 'delete'];

echo "<h2>Complete System Permission Map for: {$currentUser['username']} ({$currentUser['role']})</h2>";

foreach ($testModules as $module => $moduleInfo) {
    echo "<div class='dashboard-card' style='margin-bottom: 20px;'>";
    echo "<div class='card-header'><h3>{$moduleInfo[0]} ($module)</h3></div>";
    echo "<div style='padding: 15px;'>";
    echo "<p><strong>Test Page:</strong> {$moduleInfo[1]}</p>";
    
    echo "<div style='display: flex; gap: 10px; margin-bottom: 15px;'>";
    foreach ($actions as $action) {
        $hasPerm = $permissionManager->hasPermission($module, $action);
        $color = $hasPerm ? '#28a745' : '#dc3545';
        echo "<span style='padding: 8px 15px; background-color: $color; color: white; border-radius: 4px;'>";
        echo strtoupper($action) . ": " . ($hasPerm ? '✓' : '✗');
        echo "</span>";
    }
    echo "</div>";
    
    // Test buttons
    echo "<div style='display: flex; gap: 10px;'>";
    echo $permissionEnforcer->actionButton('view', 'View Page', $moduleInfo[1], 'btn btn-primary');
    echo $permissionEnforcer->actionButton('create', 'Create New', "#", 'btn btn-success');
    echo $permissionEnforcer->actionButton('edit', 'Edit Item', "#", 'btn btn-warning');
    echo $permissionEnforcer->actionButton('delete', 'Delete Item', "#", 'btn btn-danger', 'Confirm delete?');
    echo "</div>";
    
    echo "</div></div>";
}

// Test form fields
echo "<div class='dashboard-card'>";
echo "<div class='card-header'><h3>Form Field Test</h3></div>";
echo "<div style='padding: 15px;'>";

echo "<div class='form-group'>";
echo "<label>Editable Field (if edit permission):</label>";
echo $permissionEnforcer->formField('edit', 'test_field', 'Sample Value', 'text');
echo "</div>";

echo "<div class='form-group'>";
echo "<label>Readonly Field (if no edit permission):</label>"; 
echo $permissionEnforcer->formField('delete', 'test_field2', 'Sample Value', 'text');
echo "</div>";

echo "</div></div>";
?>

<?php require_once 'includes/footer.php'; ?>