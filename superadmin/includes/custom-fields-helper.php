<?php
// Helper functions for working with custom fields

function getCustomField($field_key, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM custom_fields WHERE field_key = ? AND status = 'active'");
    $stmt->execute([$field_key]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCustomFields($module, $pdo) {
    $stmt = $pdo->prepare("
        SELECT * FROM custom_fields 
        WHERE JSON_CONTAINS(module_associations, JSON_QUOTE(?)) 
        AND status = 'active'
        ORDER BY display_order ASC
    ");
    $stmt->execute([$module]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function saveCustomFieldValue($field_id, $record_id, $record_type, $value, $pdo) {
    try {
        // Check if value already exists
        $stmt = $pdo->prepare("
            SELECT id FROM custom_field_values 
            WHERE field_id = ? AND record_id = ? AND record_type = ?
        ");
        $stmt->execute([$field_id, $record_id, $record_type]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE custom_field_values 
                SET field_value = ?, updated_at = NOW()
                WHERE field_id = ? AND record_id = ? AND record_type = ?
            ");
            return $stmt->execute([$value, $field_id, $record_id, $record_type]);
        } else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO custom_field_values (field_id, record_id, record_type, field_value)
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$field_id, $record_id, $record_type, $value]);
        }
    } catch (Exception $e) {
        error_log("Error saving custom field value: " . $e->getMessage());
        return false;
    }
}

function getCustomFieldValue($field_id, $record_id, $record_type, $pdo) {
    $stmt = $pdo->prepare("
        SELECT field_value FROM custom_field_values
        WHERE field_id = ? AND record_id = ? AND record_type = ?
    ");
    $stmt->execute([$field_id, $record_id, $record_type]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['field_value'] : null;
}

function renderCustomField($field, $record_id, $record_type, $current_value = null, $pdo) {
    $value = $current_value !== null ? $current_value : getCustomFieldValue($field['id'], $record_id, $record_type, $pdo);
    $fieldId = 'cf_' . $field['field_id'];
    $fieldName = 'custom_field_' . $field['id'];
    
    $html = '<div class="form-group">';
    $html .= '<label for="' . $fieldId . '">' . htmlspecialchars($field['field_label']);
    if ($field['required']) {
        $html .= ' <span style="color: red;">*</span>';
    }
    $html .= '</label>';
    
    switch ($field['field_type']) {
        case 'text':
            $html .= '<input type="text" id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . ' value="' . htmlspecialchars($value) . '">';
            break;
        case 'textarea':
            $html .= '<textarea id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . '>' . htmlspecialchars($value) . '</textarea>';
            break;
        case 'email':
            $html .= '<input type="email" id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . ' value="' . htmlspecialchars($value) . '">';
            break;
        case 'number':
            $html .= '<input type="number" id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . ' value="' . htmlspecialchars($value) . '">';
            break;
        case 'date':
            $html .= '<input type="date" id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . ' value="' . htmlspecialchars($value) . '">';
            break;
        case 'select':
            $options = json_decode($field['field_options'], true) ?? [];
            $html .= '<select id="' . $fieldId . '" name="' . $fieldName . '" ' . ($field['required'] ? 'required' : '') . '>';
            $html .= '<option value="">Select...</option>';
            foreach ($options as $option) {
                $html .= '<option value="' . htmlspecialchars($option) . '" ' . ($value === $option ? 'selected' : '') . '>' . htmlspecialchars($option) . '</option>';
            }
            $html .= '</select>';
            break;
        case 'checkbox':
            $options = json_decode($field['field_options'], true) ?? [];
            $selectedValues = $value ? json_decode($value, true) : [];
            foreach ($options as $option) {
                $html .= '<label><input type="checkbox" name="' . $fieldName . '[]" value="' . htmlspecialchars($option) . '" ' . (in_array($option, $selectedValues) ? 'checked' : '') . '> ' . htmlspecialchars($option) . '</label>';
            }
            break;
    }
    
    if (!empty($field['description'])) {
        $html .= '<small style="display: block; margin-top: 4px; color: #666;">' . htmlspecialchars($field['description']) . '</small>';
    }
    
    $html .= '</div>';
    return $html;
}
?>
