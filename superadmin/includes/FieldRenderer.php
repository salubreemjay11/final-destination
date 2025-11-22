<?php
/**
 * FieldRenderer.php
 * Renders custom fields in forms with proper HTML markup
 */

class FieldRenderer {
    private $fieldManager;
    private $records;
    
    public function __construct($fieldManager) {
        $this->fieldManager = $fieldManager;
    }
    
    /**
     * Render custom fields for a module and record
     */
    public function renderFields($module, $record_id = null, $record_type = null, $values = []) {
        $fields = $this->fieldManager->getFieldsByModule($module);
        $html = '<div class="custom-fields-container">';
        
        foreach ($fields as $field) {
            $value = $values[$field['id']] ?? $field['default_value'] ?? '';
            $html .= $this->renderField($field, $value, $record_id, $record_type);
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Render individual field based on type
     */
    public function renderField($field, $value = '', $record_id = null, $record_type = null) {
        $required = $field['is_required'] ? 'required' : '';
        $requiredLabel = $field['is_required'] ? '<span class="required">*</span>' : '';
        
        $html = '<div class="field-group form-group">';
        $html .= '<label for="field_' . $field['id'] . '">' . htmlspecialchars($field['field_label']) . ' ' . $requiredLabel . '</label>';
        
        switch ($field['field_type']) {
            case 'text':
            case 'email':
            case 'phone':
                $html .= $this->renderTextInput($field, $value, $required);
                break;
            
            case 'textarea':
                $html .= $this->renderTextarea($field, $value, $required);
                break;
            
            case 'number':
                $html .= $this->renderNumberInput($field, $value, $required);
                break;
            
            case 'date':
                $html .= $this->renderDateInput($field, $value, $required);
                break;
            
            case 'dropdown':
                $html .= $this->renderDropdown($field, $value, $required);
                break;
            
            case 'radio':
                $html .= $this->renderRadio($field, $value, $required);
                break;
            
            case 'checkbox':
                $html .= $this->renderCheckbox($field, $value, $required);
                break;
            
            case 'file':
                $html .= $this->renderFileInput($field, $value, $required, $record_id, $record_type);
                break;
            
            default:
                $html .= $this->renderTextInput($field, $value, $required);
        }
        
        if (!empty($field['help_text'])) {
            $html .= '<small class="form-text text-muted">' . htmlspecialchars($field['help_text']) . '</small>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function renderTextInput($field, $value, $required) {
        $type = $field['field_type'] === 'phone' ? 'tel' : 
                ($field['field_type'] === 'email' ? 'email' : 'text');
        
        return '<input type="' . $type . '" 
                       class="form-control" 
                       id="field_' . $field['id'] . '" 
                       name="custom_field_' . $field['id'] . '" 
                       value="' . htmlspecialchars($value) . '" 
                       placeholder="' . htmlspecialchars($field['placeholder_text'] ?? '') . '" 
                       ' . $required . '>';
    }
    
    private function renderTextarea($field, $value, $required) {
        return '<textarea class="form-control" 
                          id="field_' . $field['id'] . '" 
                          name="custom_field_' . $field['id'] . '" 
                          placeholder="' . htmlspecialchars($field['placeholder_text'] ?? '') . '" 
                          rows="4" 
                          ' . $required . '>' . htmlspecialchars($value) . '</textarea>';
    }
    
    private function renderNumberInput($field, $value, $required) {
        return '<input type="number" 
                       class="form-control" 
                       id="field_' . $field['id'] . '" 
                       name="custom_field_' . $field['id'] . '" 
                       value="' . htmlspecialchars($value) . '" 
                       ' . $required . '>';
    }
    
    private function renderDateInput($field, $value, $required) {
        return '<input type="date" 
                       class="form-control" 
                       id="field_' . $field['id'] . '" 
                       name="custom_field_' . $field['id'] . '" 
                       value="' . htmlspecialchars($value) . '" 
                       ' . $required . '>';
    }
    
    private function renderDropdown($field, $value, $required) {
        $options = json_decode($field['field_options'], true) ?? [];
        $html = '<select class="form-control" 
                        id="field_' . $field['id'] . '" 
                        name="custom_field_' . $field['id'] . '" 
                        ' . $required . '>';
        
        $html .= '<option value="">-- Select --</option>';
        
        foreach ($options as $optValue => $optLabel) {
            $selected = $value == $optValue ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($optValue) . '" ' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
        }
        
        $html .= '</select>';
        return $html;
    }
    
    private function renderRadio($field, $value, $required) {
        $options = json_decode($field['field_options'], true) ?? [];
        $html = '<div class="field-options">';
        
        foreach ($options as $optValue => $optLabel) {
            $checked = $value == $optValue ? 'checked' : '';
            $html .= '<label class="radio-label">';
            $html .= '<input type="radio" 
                            name="custom_field_' . $field['id'] . '" 
                            value="' . htmlspecialchars($optValue) . '" 
                            ' . $checked . ' 
                            ' . $required . '> ';
            $html .= htmlspecialchars($optLabel);
            $html .= '</label>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function renderCheckbox($field, $value, $required) {
        $checked = !empty($value) ? 'checked' : '';
        return '<input type="checkbox" 
                       class="form-check-input" 
                       id="field_' . $field['id'] . '" 
                       name="custom_field_' . $field['id'] . '" 
                       value="1" 
                       ' . $checked . ' 
                       ' . $required . '>';
    }
    
    private function renderFileInput($field, $value, $required, $record_id, $record_type) {
        $html = '<input type="file" 
                       class="form-control" 
                       id="field_' . $field['id'] . '" 
                       name="custom_field_' . $field['id'] . '" 
                       ' . $required . ' 
                       data-record-id="' . htmlspecialchars($record_id ?? '') . '" 
                       data-record-type="' . htmlspecialchars($record_type ?? '') . '">';
        
        if (!empty($value)) {
            $html .= '<div class="file-preview"><a href="' . htmlspecialchars($value) . '" target="_blank">View File</a></div>';
        }
        
        return $html;
    }
}
?>
