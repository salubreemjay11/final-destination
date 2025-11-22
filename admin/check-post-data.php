<?php
session_start();
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Custom Fields POST Data Check</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Looking for custom_field_* in POST data:</h3>";
    
    $foundCustomFields = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'custom_field_') === 0) {
            $foundCustomFields[$key] = $value;
        }
    }
    
    if (empty($foundCustomFields)) {
        echo "<p style='color: red; font-size: 18px;'><strong>❌ NO CUSTOM FIELDS FOUND IN POST DATA!</strong></p>";
        echo "<p>This means the custom field inputs are not being submitted with the form.</p>";
        
        echo "<h3>All POST keys (for debugging):</h3>";
        echo "<pre>";
        foreach ($_POST as $key => $value) {
            echo "['$key'] = '$value'\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: green; font-size: 18px;'><strong>✅ Custom fields found in POST data:</strong></p>";
        echo "<pre>" . print_r($foundCustomFields, true) . "</pre>";
    }
} else {
    echo "<p>No POST data. Please submit the unified registration form first.</p>";
    echo "<p><a href='unified-registration.php'>Go to Unified Registration</a></p>";
}
?>