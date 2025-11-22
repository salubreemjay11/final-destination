<?php
// test-unified.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

echo "<h1>Testing Unified Registration</h1>";

// Test 1: Check if header loads
echo "<h2>Test 1: Header Include</h2>";
try {
    require_once 'includes/header.php';
    echo "<p style='color: green;'>✓ Header loaded successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Header failed: " . $e->getMessage() . "</p>";
}

// Test 2: Check database connection
echo "<h2>Test 2: Database Connection</h2>";
try {
    require_once '../config/database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database failed: " . $e->getMessage() . "</p>";
}

// Test 3: Check CustomFieldManager
echo "<h2>Test 3: Custom Field Manager</h2>";
try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
        $fieldManager = new CustomFieldManager($pdo);
        echo "<p style='color: green;'>✓ CustomFieldManager loaded successfully</p>";
        
        // Test getting fields
        $childFields = $fieldManager->getModuleFields('children');
        echo "<p>Child fields found: " . count($childFields) . "</p>";
        
        $caseFields = $fieldManager->getModuleFields('cases');
        echo "<p>Case fields found: " . count($caseFields) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ CustomFieldManager.php not found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ CustomFieldManager failed: " . $e->getMessage() . "</p>";
}

// Test 4: Check session and permissions
echo "<h2>Test 4: Session & Permissions</h2>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";
echo "<p>User Role: " . ($_SESSION['role'] ?? 'NOT SET') . "</p>";

echo "<h2>Test Complete</h2>";
?>