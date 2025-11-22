<?php
// admin/includes/permission-enforcer.php

class PermissionEnforcer {
    private $permissionManager;
    
    public function __construct($permissionManager) {
        $this->permissionManager = $permissionManager;
    }
    
    // Enforce view permission for the current page
    public function enforcePageAccess() {
        $currentPage = basename($_SERVER['PHP_SELF']);
        if (!$this->permissionManager->canAccessPage($currentPage)) {
            header('Location: access-denied.php');
            exit();
        }
    }
    
    // Enforce action permission (for form processing, actions, etc.)
    public function enforceAction($action, $redirectUrl = 'access-denied.php') {
        if (!$this->permissionManager->canPerformAction($action)) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    // Check if action is allowed (for conditional displays)
    public function isActionAllowed($action) {
        return $this->permissionManager->canPerformAction($action);
    }
    
    // Generate action button with permission check
    public function actionButton($action, $buttonText, $url = '#', $class = 'btn btn-primary', $confirmMessage = '') {
        if ($this->isActionAllowed($action)) {
            if ($confirmMessage) {
                return "<a href='$url' class='$class' onclick='return confirm(\"$confirmMessage\")'>$buttonText</a>";
            } else {
                return "<a href='$url' class='$class'>$buttonText</a>";
            }
        } else {
            return "<button class='$class' disabled style='opacity: 0.6; cursor: not-allowed;' title='No permission to $action'>$buttonText</button>";
        }
    }
    
    // Make form field editable/readonly based on permission - COMPLETELY READONLY if no edit permission
    public function formField($action, $fieldName, $fieldValue, $fieldType = 'text', $options = []) {
        $isEditable = $this->isActionAllowed($action);
        
        // If no edit permission, ALWAYS return readonly field
        if (!$isEditable) {
            switch ($fieldType) {
                case 'text':
                case 'email':
                case 'number':
                case 'date':
                    return "<input type='$fieldType' value='" . htmlspecialchars($fieldValue) . "' class='form-input' readonly style='background-color: #f8f9fa; cursor: not-allowed;'>";
                    
                case 'textarea':
                    return "<textarea class='form-input' readonly style='background-color: #f8f9fa; cursor: not-allowed; resize: none;'>" . htmlspecialchars($fieldValue) . "</textarea>";
                    
                case 'select':
                    $displayValue = $options[$fieldValue] ?? $fieldValue;
                    return "<input type='text' value='" . htmlspecialchars($displayValue) . "' class='form-input' readonly style='background-color: #f8f9fa; cursor: not-allowed;'>";
                    
                case 'checkbox':
                    $checked = $fieldValue ? 'checked' : '';
                    return "<input type='checkbox' $checked disabled style='cursor: not-allowed;'>";
                    
                case 'radio':
                    return "<input type='radio' " . ($fieldValue ? 'checked' : '') . " disabled style='cursor: not-allowed;'>";
                    
                default:
                    return "<span class='readonly-field'>" . htmlspecialchars($fieldValue) . "</span>";
            }
        }
        
        // If edit permission is granted, return editable field
        switch ($fieldType) {
            case 'text':
            case 'email':
            case 'number':
            case 'date':
                return "<input type='$fieldType' name='$fieldName' value='" . htmlspecialchars($fieldValue) . "' class='form-input'>";
                
            case 'textarea':
                return "<textarea name='$fieldName' class='form-input'>" . htmlspecialchars($fieldValue) . "</textarea>";
                
            case 'select':
                $selectHtml = "<select name='$fieldName' class='form-input'>";
                foreach ($options as $value => $label) {
                    $selected = ($value == $fieldValue) ? 'selected' : '';
                    $selectHtml .= "<option value='$value' $selected>$label</option>";
                }
                $selectHtml .= "</select>";
                return $selectHtml;
                
            case 'checkbox':
                $checked = $fieldValue ? 'checked' : '';
                return "<input type='checkbox' name='$fieldName' value='1' $checked>";
                
            case 'radio':
                $checked = $fieldValue ? 'checked' : '';
                return "<input type='radio' name='$fieldName' value='1' $checked>";
                
            default:
                return "<input type='text' name='$fieldName' value='" . htmlspecialchars($fieldValue) . "' class='form-input'>";
        }
    }
    
    // Show/hide entire sections based on permission
    public function showSection($action, $content) {
        if ($this->isActionAllowed($action)) {
            return $content;
        }
        return '';
    }
    
    // Check if current module is in read-only mode (no edit permission)
    public function isReadOnlyMode() {
        $currentPage = basename($_SERVER['PHP_SELF']);
        $module = $this->permissionManager->getModuleFromPage($currentPage);
        return !$this->permissionManager->hasPermission($module, 'edit');
    }
    
    // Get read-only banner message
    public function getReadOnlyBanner() {
        if ($this->isReadOnlyMode()) {
            return "<div class='read-only-banner' style='background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin-bottom: 15px;'>
                <strong>🔒 Read-Only Mode:</strong> You have view-only access to this module. You cannot make any changes.
            </div>";
        }
        return '';
    }
    
    // Check permission for API endpoints
    public function checkAPIPermission($action) {
        if (!$this->isActionAllowed($action)) {
            http_response_code(403);
            echo json_encode(['error' => 'Permission denied - Read only access']);
            exit();
        }
    }
    
    // Completely disable form if no edit permission
    public function renderForm($action, $formContent, $formAction = '', $method = 'POST') {
        if (!$this->isActionAllowed($action)) {
            return "
            <div style='opacity: 0.7; position: relative;'>
                <div style='position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; display: flex; align-items: center; justify-content: center;'>
                    <div style='background: #fff3cd; padding: 20px; border: 2px solid #ffeaa7; border-radius: 8px; text-align: center;'>
                        <div style='font-size: 24px; margin-bottom: 10px;'>🔒</div>
                        <strong>Read-Only Access</strong><br>
                        You do not have permission to edit this content
                    </div>
                </div>
                $formContent
            </div>";
        } else {
            return "<form action='$formAction' method='$method'>$formContent</form>";
        }
    }
}
?>