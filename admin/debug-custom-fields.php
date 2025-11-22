<?php
session_start();
require_once 'includes/header.php';

// Load Custom Field Manager
$fieldManager = null;
try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
    } else {
        require_once 'includes/CustomFieldManager.php';
    }
    $fieldManager = new CustomFieldManager($pdo);
} catch (Exception $e) {
    die("Custom Field Manager Error: " . $e->getMessage());
}

echo "<h1>Custom Fields Debug</h1>";

// Check if we have a child ID to test
$childId = $_GET['child_id'] ?? 'UC-2024-001'; // Replace with actual ID

echo "<h2>Testing Child ID: $childId</h2>";

// 1. Check what custom fields are defined for children
$childCustomFields = $fieldManager->getModuleFields('children');
echo "<h3>Defined Child Custom Fields:</h3>";
echo "<pre>" . print_r($childCustomFields, true) . "</pre>";

// 2. Check what values are stored
$childFieldValues = $fieldManager->getFieldValues($childId, 'children');
echo "<h3>Stored Child Field Values:</h3>";
echo "<pre>" . print_r($childFieldValues, true) . "</pre>";

// 3. Check database structure
echo "<h3>Database Check:</h3>";
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM children LIKE 'cf_%'");
    $stmt->execute();
    $customColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>Custom columns in children table: " . print_r($customColumns, true) . "</pre>";
} catch (Exception $e) {
    echo "Error checking database: " . $e->getMessage();
}

// 4. Check custom_field_values table
try {
    $stmt = $pdo->prepare("SELECT * FROM custom_field_values WHERE record_id = ? AND module = 'children'");
    $stmt->execute([$childId]);
    $fallbackValues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>Fallback values from custom_field_values: " . print_r($fallbackValues, true) . "</pre>";
} catch (Exception $e) {
    echo "Error checking fallback table: " . $e->getMessage();
}
?>