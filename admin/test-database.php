<?php
session_start();
require_once '../config/database.php';

// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database Structure Test</h2>";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test 1: Check if custom field column exists
    echo "<h3>1. Checking custom field columns:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM children LIKE 'cf_%'");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($columns)) {
        echo "<p style='color: red;'>No custom field columns found in children table!</p>";
    } else {
        foreach ($columns as $column) {
            echo "<p>✅ " . $column['Field'] . " - " . $column['Type'] . "</p>";
        }
    }
    
    // Test 2: Check custom_fields table
    echo "<h3>2. Checking custom_fields table:</h3>";
    $stmt = $pdo->query("SELECT * FROM custom_fields");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($fields)) {
        echo "<p style='color: red;'>No custom fields defined!</p>";
    } else {
        foreach ($fields as $field) {
            echo "<p>✅ " . $field['field_name'] . " (" . $field['module'] . ") - " . $field['field_type'] . "</p>";
        }
    }
    
    // Test 3: Manual update test
    echo "<h3>3. Manual update test:</h3>";
    $testRecordId = 'UC-2025-003'; // Use an existing record
    $testValue = 'MANUAL_TEST_' . date('Y-m-d_H-i-s');
    
    // Direct SQL update
    $sql = "UPDATE children SET cf_favorite_color = ? WHERE child_id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$testValue, $testRecordId]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Manual update successful!</p>";
        
        // Verify
        $verifyStmt = $pdo->prepare("SELECT cf_favorite_color FROM children WHERE child_id = ?");
        $verifyStmt->execute([$testRecordId]);
        $savedValue = $verifyStmt->fetchColumn();
        
        echo "<p>Saved value: " . htmlspecialchars($savedValue) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Manual update failed!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>