<?php
session_start();
require_once '../config/database.php';

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Custom Fields Debug Test</h2>";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    require_once '../superadmin/includes/CustomFieldManager.php';
    $fieldManager = new CustomFieldManager($pdo);
    
    // Test 1: Check if favorite_color field exists
    echo "<h3>Test 1: Check favorite_color field</h3>";
    $field = $fieldManager->getFieldByName('favorite_color', 'children');
    if ($field) {
        echo "✅ Field found: " . $field['field_label'] . "<br>";
        echo "Field type: " . $field['field_type'] . "<br>";
        echo "DB Column: cf_" . $field['field_name'] . "<br>";
    } else {
        echo "❌ Field not found!<br>";
    }
    
    // Test 2: Check if column exists in database
    echo "<h3>Test 2: Check database column</h3>";
    $checkColumn = $pdo->prepare("SHOW COLUMNS FROM children LIKE 'cf_favorite_color'");
    $checkColumn->execute();
    $columnExists = $checkColumn->fetch();
    
    if ($columnExists) {
        echo "✅ Column cf_favorite_color exists in children table<br>";
        echo "Column type: " . $columnExists['Type'] . "<br>";
    } else {
        echo "❌ Column cf_favorite_color does NOT exist in children table<br>";
    }
    
    // Test 3: Try to save a test value
    echo "<h3>Test 3: Save test value</h3>";
    $testRecordId = 'UC-2025-003'; // Use an existing record
    $testValue = 'TEST_BLUE_' . date('His');
    
    $result = $fieldManager->saveFieldValue($testRecordId, 'children', 'favorite_color', $testValue);
    
    if ($result) {
        echo "✅ Save successful!<br>";
        
        // Verify the save
        $verifyStmt = $pdo->prepare("SELECT cf_favorite_color FROM children WHERE child_id = ?");
        $verifyStmt->execute([$testRecordId]);
        $savedValue = $verifyStmt->fetchColumn();
        
        echo "Saved value: " . htmlspecialchars($savedValue) . "<br>";
        
        if ($savedValue === $testValue) {
            echo "✅ Value verified in database!<br>";
        } else {
            echo "❌ Value mismatch! Expected: $testValue, Got: $savedValue<br>";
        }
    } else {
        echo "❌ Save failed!<br>";
    }
    
    // Test 4: Check current values for a record
    echo "<h3>Test 4: Check current custom field values</h3>";
    $values = $fieldManager->getFieldValues($testRecordId, 'children');
    echo "<pre>Current values: " . print_r($values, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>