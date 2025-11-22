<?php
session_start();
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>POST Data Debug</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>All POST Data:</h3>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h3>Custom Fields Found:</h3>";
    $customFields = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'custom_field_') === 0) {
            $customFields[$key] = $value;
        }
    }
    
    if (empty($customFields)) {
        echo "<p style='color: red;'><strong>NO CUSTOM FIELDS FOUND IN POST DATA!</strong></p>";
    } else {
        echo "<pre>" . print_r($customFields, true) . "</pre>";
    }
    
    echo "<h3>Files Data:</h3>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
} else {
    echo "<p>No POST data received. Submit the form first.</p>";
}
?>