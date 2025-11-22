<?php
session_start();
require_once '../config/database.php';
require_once 'includes/CustomFieldManager.php';

if ($_SESSION['role'] !== 'super_admin') {
    die("Access denied");
}

$fieldManager = new CustomFieldManager($pdo);

echo "<h2>Debug: Automatic Field Rendering</h2>";

// Check what custom fields exist for children module
$childFields = $fieldManager->getModuleFields('children');
echo "<h3>Children Module Fields:</h3>";

if (empty($childFields)) {
    echo "No custom fields found for children module!<br>";
} else {
    foreach ($childFields as $field) {
        echo "Field: {$field['field_name']} | Label: {$field['field_label']} | Type: {$field['field_type']}<br>";
        
        // Test the renderField method
        echo "<h4>Rendered HTML for {$field['field_name']}:</h4>";
        $rendered = $fieldManager->renderField($field, '');
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo htmlspecialchars($rendered);
        echo "</div>";
        
        echo "<h4>Actual Rendered Output:</h4>";
        echo $rendered;
    }
}

// Check cases module too
$caseFields = $fieldManager->getModuleFields('cases');
echo "<h3>Cases Module Fields:</h3>";

if (empty($caseFields)) {
    echo "No custom fields found for cases module!<br>";
} else {
    foreach ($caseFields as $field) {
        echo "Field: {$field['field_name']} | Label: {$field['field_label']} | Type: {$field['field_type']}<br>";
    }
}
?>