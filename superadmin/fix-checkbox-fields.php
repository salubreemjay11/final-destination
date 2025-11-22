<?php
// fix-checkbox-fields.php
session_start();
require_once 'includes/superheader.php';
require_once 'includes/CustomFieldManager.php';

$fieldManager = new CustomFieldManager($pdo);

echo "<h2>Fixing Checkbox Fields</h2>";

// Update specific checkbox fields to use options
$checkboxUpdates = [
    // Example: Update 'favorite_color' field
    [
        'field_name' => 'favorite_color',
        'options' => [
            'red' => 'Red',
            'blue' => 'Blue',
            'green' => 'Green',
            'yellow' => 'Yellow',
            'purple' => 'Purple'
        ]
    ],
    // Add more fields here as needed
];

foreach ($checkboxUpdates as $update) {
    try {
        // Get the field
        $field = $fieldManager->getFieldByName($update['field_name'], 'children');
        
        if ($field) {
            // Update the field with options
            $updateData = [
                'field_label' => $field['field_label'],
                'field_type' => 'checkbox',
                'module' => $field['module'],
                'placeholder_text' => $field['placeholder_text'] ?? '',
                'default_value' => $field['default_value'] ?? '',
                'help_text' => $field['help_text'] ?? '',
                'field_options' => $update['options'],
                'is_required' => $field['is_required'],
                'field_order' => $field['field_order']
            ];
            
            $result = $fieldManager->updateField($field['id'], $updateData);
            
            if ($result) {
                echo "<p style='color: green;'>✓ Updated field: " . $update['field_name'] . "</p>";
                echo "<p>Options: " . print_r($update['options'], true) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to update field: " . $update['field_name'] . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>Field not found: " . $update['field_name'] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error updating " . $update['field_name'] . ": " . $e->getMessage() . "</p>";
    }
}

// Show current checkbox fields
echo "<h3>Current Checkbox Fields:</h3>";
$fields = $fieldManager->getAllFields();
foreach ($fields as $field) {
    if ($field['field_type'] === 'checkbox') {
        echo "<p><strong>" . $field['field_name'] . "</strong>: " . print_r($field['field_options'], true) . "</p>";
    }
}
?>