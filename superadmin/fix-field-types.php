<?php
// fix-field-types.php
session_start();
require_once 'includes/superheader.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);

echo "<h2>Fixing Field Types</h2>";

// Fix specific field types
$fixes = [
    // Update email fields to be 'email' type
    "UPDATE custom_fields SET field_type = 'email' WHERE field_type IN ('select', 'dropdown') AND field_name LIKE '%email%'",
    
    // Update phone fields to be 'tel' type  
    "UPDATE custom_fields SET field_type = 'tel' WHERE field_type IN ('select', 'dropdown') AND field_name LIKE '%phone%'",
    
    // Update url fields to be 'url' type
    "UPDATE custom_fields SET field_type = 'url' WHERE field_type IN ('select', 'dropdown') AND field_name LIKE '%url%'",
    
    // Make sure medical_history stays as select
    "UPDATE custom_fields SET field_type = 'select' WHERE field_name = 'medical_history'"
];

foreach ($fixes as $sql) {
    try {
        $result = $pdo->exec($sql);
        echo "<p>Executed: " . htmlspecialchars($sql) . " - Affected: $result rows</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Show current field types
$fields = $fieldManager->getAllFields();
echo "<h3>Current Field Types:</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Field Name</th><th>Label</th><th>Type</th><th>Options</th></tr>";
foreach ($fields as $field) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($field['field_name']) . "</td>";
    echo "<td>" . htmlspecialchars($field['field_label']) . "</td>";
    echo "<td>" . htmlspecialchars($field['field_type']) . "</td>";
    echo "<td>" . (!empty($field['field_options']) ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>