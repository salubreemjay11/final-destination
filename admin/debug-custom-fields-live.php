<?php
session_start();
require_once 'config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Live Custom Fields Debug</h2>";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    // Get the latest child record
    $stmt = $pdo->query("SELECT child_id FROM children ORDER BY created_at DESC LIMIT 1");
    $latestChild = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($latestChild) {
        $childId = $latestChild['child_id'];
        echo "<h3>Checking latest child: $childId</h3>";
        
        // Check ALL custom field columns
        $checkStmt = $pdo->prepare("SELECT 
            cf_favorite_color, cf_educational_level, cf_religion, cf_special_needs, 
            cf_hobbies, cf_school_name, cf_grade_level, cf_medical_history,
            cf_time_gold, cf_radio_buttons, cf_favorite_anime, cf_favorite_hobbies,
            cf_mega_box
            FROM children WHERE child_id = ?");
        $checkStmt->execute([$childId]);
        $currentValues = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h4>Current Custom Field Values:</h4>";
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Field Name</th><th>Value in Database</th></tr>";
        foreach ($currentValues as $field => $value) {
            $displayValue = $value === null ? 'NULL' : htmlspecialchars($value);
            echo "<tr><td>$field</td><td><strong>$displayValue</strong></td></tr>";
        }
        echo "</table>";
        
        // Test updating a field
        echo "<h4>Test Update:</h4>";
        $testValue = 'DEBUG_' . date('Y-m-d H:i:s');
        $updateStmt = $pdo->prepare("UPDATE children SET cf_favorite_color = ? WHERE child_id = ?");
        $result = $updateStmt->execute([$testValue, $childId]);
        
        echo "Test update result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
        echo "Rows affected: " . $updateStmt->rowCount() . "<br>";
        
        // Verify the update
        $checkStmt->execute([$childId]);
        $updatedValues = $checkStmt->fetch(PDO::FETCH_ASSOC);
        echo "After update - cf_favorite_color: " . htmlspecialchars($updatedValues['cf_favorite_color']) . "<br>";
        
    } else {
        echo "No children found in database!";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Also check the error logs
echo "<h3>Recent Error Logs (last 10 lines):</h3>";
$logFile = 'error_log';
if (file_exists($logFile)) {
    $logs = `tail -20 $logFile`;
    echo "<pre>" . htmlspecialchars($logs) . "</pre>";
} else {
    echo "Error log file not found at: $logFile<br>";
}
?>