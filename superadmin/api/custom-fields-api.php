<?php
/**
 * Custom Fields API Endpoints
 * Handles AJAX requests for field management
 */

header('Content-Type: application/json');
session_start();

// Require database connection
require_once '../includes/superheader.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$response = ['success' => false, 'message' => 'Invalid request'];

try {
    switch ($action) {
        case 'get_fields':
            $module = $_POST['module'] ?? $_GET['module'] ?? null;
            $fields = $fieldManager->getFieldsByModule($module);
            $response = ['success' => true, 'fields' => $fields];
            break;
        
        case 'create_field':
            $data = [
                'field_name' => $_POST['field_name'] ?? '',
                'field_label' => $_POST['field_label'] ?? '',
                'field_type' => $_POST['field_type'] ?? 'text',
                'module' => $_POST['module'] ?? '',
                'placeholder_text' => $_POST['placeholder_text'] ?? '',
                'default_value' => $_POST['default_value'] ?? '',
                'help_text' => $_POST['help_text'] ?? '',
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'field_order' => $_POST['field_order'] ?? 0,
                'created_by' => $_SESSION['user_id']
            ];
            
            if (in_array($_POST['field_type'], ['dropdown', 'radio', 'checkbox'])) {
                $options = [];
                $optionValues = $_POST['option_values'] ?? [];
                $optionLabels = $_POST['option_labels'] ?? [];
                
                foreach ($optionValues as $i => $val) {
                    if (!empty($val)) {
                        $options[$val] = $optionLabels[$i] ?? $val;
                    }
                }
                $data['field_options'] = $options;
            }
            
            $result = $fieldManager->createField($data);
            $response = $result;
            break;
        
        case 'update_field':
            $fieldId = $_POST['field_id'] ?? null;
            $data = [
                'field_name' => $_POST['field_name'] ?? '',
                'field_label' => $_POST['field_label'] ?? '',
                'field_type' => $_POST['field_type'] ?? 'text',
                'placeholder_text' => $_POST['placeholder_text'] ?? '',
                'default_value' => $_POST['default_value'] ?? '',
                'help_text' => $_POST['help_text'] ?? '',
                'is_required' => isset($_POST['is_required']) ? 1 : 0,
                'field_order' => $_POST['field_order'] ?? 0
            ];
            
            if ($fieldManager->updateField($fieldId, $data)) {
                $response = ['success' => true, 'message' => 'Field updated successfully'];
            }
            break;
        
        case 'delete_field':
            $fieldId = $_POST['field_id'] ?? null;
            if ($fieldManager->deleteField($fieldId)) {
                $response = ['success' => true, 'message' => 'Field deleted successfully'];
            }
            break;
        
        case 'get_field':
            $fieldId = $_POST['field_id'] ?? $_GET['field_id'] ?? null;
            $field = $fieldManager->getField($fieldId);
            
            if ($field) {
                $response = ['success' => true, 'field' => $field];
            } else {
                $response = ['success' => false, 'message' => 'Field not found'];
            }
            break;
        
        case 'save_field_value':
            $fieldId = $_POST['field_id'] ?? null;
            $recordId = $_POST['record_id'] ?? null;
            $recordType = $_POST['record_type'] ?? null;
            $value = $_POST['field_value'] ?? '';
            
            if ($fieldManager->saveFieldValue($fieldId, $recordId, $recordType, $value)) {
                $response = ['success' => true, 'message' => 'Value saved successfully'];
            }
            break;
        
        case 'get_record_values':
            $recordId = $_POST['record_id'] ?? $_GET['record_id'] ?? null;
            $recordType = $_POST['record_type'] ?? $_GET['record_type'] ?? null;
            
            $values = $fieldManager->getRecordValues($recordId, $recordType);
            $response = ['success' => true, 'values' => $values];
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

echo json_encode($response);
?>
