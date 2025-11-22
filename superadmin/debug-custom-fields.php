<?php
session_start();
require_once '../config/database.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);
$childFields = $fieldManager->getModuleFields('children');

echo "<h1>Custom Fields Database Check</h1>";
echo "<p>Found " . count($childFields) . " child custom fields in database</p>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Field Name</th><th>Field Label</th><th>Type</th><th>Module</th></tr>";
foreach ($childFields as $field) {
    echo "<tr>";
    echo "<td>{$field['id']}</td>";
    echo "<td>{$field['field_name']}</td>";
    echo "<td>{$field['field_label']}</td>";
    echo "<td>{$field['field_type']}</td>";
    echo "<td>{$field['module']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test rendering a field
echo "<h2>Test Field Rendering</h2>";
if (!empty($childFields)) {
    $testField = $childFields[0];
    echo "<h3>Testing field: {$testField['field_name']}</h3>";
    $rendered = $fieldManager->renderField($testField, 'test value');
    echo $rendered;
    
    // Show the generated HTML
    echo "<h4>Generated HTML:</h4>";
    echo "<pre>" . htmlspecialchars($rendered) . "</pre>";
}
?>