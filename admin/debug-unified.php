<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$pdo = $database->getConnection();

echo "<h1>🧪 CUSTOM FIELD DEBUG</h1>";
echo "<p>This file tests if custom fields can be saved directly.</p>";

if ($_POST) {
    echo "<h2>📨 POST Data Received:</h2>";
    foreach ($_POST as $key => $value) {
        echo "<p><strong>$key</strong> = '$value'</p>";
    }
    
    $testId = 'UC-2024-001'; // CHANGE THIS TO A REAL CHILD ID
    
    foreach ($_POST as $fieldName => $fieldValue) {
        if (strpos($fieldName, 'custom_field_') === 0) {
            $dbColumn = 'cf_' . str_replace('custom_field_', '', $fieldName);
            
            echo "<h3>💾 Testing: $dbColumn = '$fieldValue'</h3>";
            
            try {
                // Try to save
                $sql = "UPDATE children SET `$dbColumn` = ? WHERE child_id = ?";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$fieldValue, $testId]);
                
                if ($result) {
                    echo "<p style='color: green;'>✅ SUCCESS: Saved to database!</p>";
                    
                    // Verify
                    $verify = $pdo->prepare("SELECT `$dbColumn` FROM children WHERE child_id = ?");
                    $verify->execute([$testId]);
                    $savedValue = $verify->fetchColumn();
                    echo "<p>Verified in database: '$savedValue'</p>";
                } else {
                    echo "<p style='color: red;'>❌ FAILED: Could not save</p>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>🚨 ERROR: " . $e->getMessage() . "</p>";
            }
        }
    }
}
?>

<form method="POST" style="background: #f0f0f0; padding: 20px; margin: 20px 0;">
    <h3>Test Custom Fields:</h3>
    <input type="text" name="custom_field_educational_level" placeholder="Educational Level" value="Test High School"><br><br>
    <input type="text" name="custom_field_religion" placeholder="Religion" value="Test Religion"><br><br>
    <input type="text" name="custom_field_favorite_color" placeholder="Favorite Color" value="Test Blue"><br><br>
    <button type="submit" style="background: blue; color: white; padding: 10px 20px;">🧪 TEST SAVE</button>
</form>

<h3>Current Database Structure:</h3>
<?php
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM children");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        if (strpos($col['Field'], 'cf_') === 0) {
            echo "<tr>";
            echo "<td><strong>" . $col['Field'] . "</strong></td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>