<?php
// debug-checkbox.php
session_start();
require_once 'includes/header.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);

echo "<h2>Debug Checkbox Fields</h2>";

// Get all custom fields
$fields = $fieldManager->getAllFields();

foreach ($fields as $field) {
    if ($field['field_type'] === 'checkbox') {
        echo "<h3>Field: " . $field['field_name'] . " (" . $field['field_label'] . ")</h3>";
        echo "<p>Type: " . $field['field_type'] . "</p>";
        echo "<p>Options: " . print_r($field['field_options'], true) . "</p>";
        echo "<p>Module: " . $field['module'] . "</p>";
        echo "<hr>";
    }
}

// Test rendering a checkbox field
echo "<h2>Test Rendering</h2>";
$testField = [
    'field_name' => 'test_checkbox',
    'field_label' => 'Test Checkbox with Options',
    'field_type' => 'checkbox',
    'field_options' => [
        'red' => 'Red Color',
        'blue' => 'Blue Color', 
        'green' => 'Green Color'
    ],
    'help_text' => 'Choose your favorite colors',
    'is_required' => 0
];

echo $fieldManager->renderField($testField, 'red,blue');
?>