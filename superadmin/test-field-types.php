<?php
session_start();
require_once 'includes/superheader.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);

// Test different field types
$testFields = [
    [
        'field_name' => 'test_email',
        'field_label' => 'Test Email Field',
        'field_type' => 'email',
        'field_options' => []
    ],
    [
        'field_name' => 'test_phone',
        'field_label' => 'Test Phone Field', 
        'field_type' => 'tel',
        'field_options' => []
    ],
    [
        'field_name' => 'test_url',
        'field_label' => 'Test URL Field',
        'field_type' => 'url',
        'field_options' => []
    ],
    [
        'field_name' => 'test_select',
        'field_label' => 'Test Select Field',
        'field_type' => 'select',
        'field_options' => ['option1' => 'Option 1', 'option2' => 'Option 2']
    ]
];

echo "<h2>Field Type Rendering Test</h2>";

foreach ($testFields as $field) {
    echo "<h3>Field: {$field['field_label']} (Type: {$field['field_type']})</h3>";
    echo $fieldManager->renderField($field);
    $fieldManager->debugFieldRendering($field);
    echo "<hr>";
}
?>