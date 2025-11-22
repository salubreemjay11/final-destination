<?php
session_start();
require_once '../config/database.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);
$childFields = $fieldManager->getModuleFields('children');

echo "<h1>Custom Fields Test</h1>";
echo "<p>Found " . count($childFields) . " child custom fields</p>";

foreach ($childFields as $field) {
    echo "<h3>Field: {$field['field_name']}</h3>";
    echo "<p>Type: {$field['field_type']}</p>";
    echo "<p>Label: {$field['field_label']}</p>";
    
    // Test rendering
    $rendered = $fieldManager->renderField($field, 'test value');
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
    echo "<h4>Rendered Field:</h4>";
    echo $rendered;
    echo "</div>";
    
    echo "<hr>";
}
?>