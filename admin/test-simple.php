<?php
require_once '../config/database.php';

try {
    echo "Testing database connection...<br>";
    
    // Test basic connection
    $test = $pdo->query("SELECT 1");
    echo "Database connection: OK<br>";
    
    // Test simple insert
    $fosterId = 'FT-TEST-' . time();
    $stmt = $pdo->prepare("
        INSERT INTO foster_parents (foster_id, name, age, gender, civil_status, contact_number, address, status) 
        VALUES (?, 'Test Name', 30, 'Male', 'Single', '123456789', 'Test Address', 'Pending')
    ");
    
    $result = $stmt->execute([$fosterId]);
    
    if ($result) {
        echo "SUCCESS: Basic insert worked!<br>";
        
        // Test if record exists
        $check = $pdo->prepare("SELECT COUNT(*) FROM foster_parents WHERE foster_id = ?");
        $check->execute([$fosterId]);
        $count = $check->fetchColumn();
        
        echo "Record found in database: " . ($count > 0 ? 'YES' : 'NO') . "<br>";
        
        // Clean up
        $pdo->prepare("DELETE FROM foster_parents WHERE foster_id = ?")->execute([$fosterId]);
        echo "Test record cleaned up.<br>";
    } else {
        echo "FAILED: " . print_r($stmt->errorInfo(), true) . "<br>";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}
?>