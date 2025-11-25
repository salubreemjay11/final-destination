<?php
session_start();
require_once 'config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Quick Custom Fields Test</h2>";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Test direct database update
    $testId = 'UC-2025-001';
    $testValue = 'QUICK_TEST_' . date('His');
    
    $stmt = $pdo->prepare("UPDATE children SET cf_favorite_color = ? WHERE child_id = ?");
    $result = $stmt->execute([$testValue, $testId]);
    
    if ($result) {
        echo "✅ Direct update successful!<br>";
        
        // Verify
        $check = $pdo->prepare("SELECT cf_favorite_color FROM children WHERE child_id = ?");
        $check->execute([$testId]);
        $value = $check->fetchColumn();
        
        echo "Value in database: " . htmlspecialchars($value) . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>