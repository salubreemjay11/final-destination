<?php
session_start();

// Handle redirects and form processing BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_field'])) {
    require_once '../config/database.php';
    
    // Check if user is super admin
    if ($_SESSION['role'] !== 'super_admin') {
        header("Location: ../admin/access-denied.php");
        exit();
    }

    // Ensure $pdo is available
    if (!isset($pdo)) {
        $database = new Database();
        $pdo = $database->getConnection();
    }

    require_once 'includes/CustomFieldManager.php';
    $fieldManager = new CustomFieldManager($pdo);

    $fieldId = $_GET['id'] ?? null;
    
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
    
    // Handle field options
    if (in_array($_POST['field_type'], ['select', 'radio', 'checkbox'])) {
        $options = [];
        $optionValues = $_POST['option_values'] ?? [];
        $optionLabels = $_POST['option_labels'] ?? [];
        
        foreach ($optionValues as $i => $val) {
            if (!empty($val) && !empty($optionLabels[$i])) {
                $options[trim($val)] = trim($optionLabels[$i]);
            }
        }
        $data['field_options'] = $options;
    }
    
    if (!$fieldId) {
        // Create field and redirect immediately
        $result = $fieldManager->createField($data);
        if ($result['success']) {
            $_SESSION['success_message'] = 'Field created successfully!';
            $_SESSION['message_type'] = 'success';
            header("Location: custom-field.php");
            exit();
        } else {
            // Store error in session for display
            $_SESSION['error_message'] = 'Error creating field: ' . ($result['error'] ?? 'Unknown error');
            header("Location: custom-field.php?action=add");
            exit();
        }
    }
}

// Handle GET actions that need redirects
if (isset($_GET['action']) && in_array($_GET['action'], ['delete', 'toggle'])) {
    require_once '../config/database.php';
    
    // Check if user is super admin
    if ($_SESSION['role'] !== 'super_admin') {
        header("Location: ../admin/access-denied.php");
        exit();
    }

    // Ensure $pdo is available
    if (!isset($pdo)) {
        $database = new Database();
        $pdo = $database->getConnection();
    }

    require_once 'includes/CustomFieldManager.php';
    $fieldManager = new CustomFieldManager($pdo);

    $fieldId = $_GET['id'] ?? null;

    switch ($_GET['action']) {
        case 'delete':
            if ($fieldId) {
                $result = $fieldManager->deleteField($fieldId);
                if ($result) {
                    $_SESSION['success_message'] = 'Field deleted successfully!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['error_message'] = 'Error deleting field!';
                    $_SESSION['message_type'] = 'error';
                }
                header("Location: custom-field.php");
                exit();
            }
            break;
            
        case 'toggle':
            if ($fieldId) {
                $status = $_GET['status'] ?? 0;
                $result = $fieldManager->toggleFieldStatus($fieldId, $status);
                if ($result) {
                    $_SESSION['success_message'] = 'Field status updated!';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['error_message'] = 'Error updating field status!';
                    $_SESSION['message_type'] = 'error';
                }
                header("Location: custom-field.php");
                exit();
            }
            break;
    }
}

// Handle POST for updates (not creation) - ALSO at the top
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_field']) && isset($_GET['id'])) {
    require_once '../config/database.php';
    
    // Check if user is super admin
    if ($_SESSION['role'] !== 'super_admin') {
        header("Location: ../admin/access-denied.php");
        exit();
    }

    // Ensure $pdo is available
    if (!isset($pdo)) {
        $database = new Database();
        $pdo = $database->getConnection();
    }

    require_once 'includes/CustomFieldManager.php';
    $fieldManager = new CustomFieldManager($pdo);

    $fieldId = $_GET['id'];
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
    
    if (in_array($_POST['field_type'], ['select', 'radio', 'checkbox'])) {
        $options = [];
        $optionValues = $_POST['option_values'] ?? [];
        $optionLabels = $_POST['option_labels'] ?? [];
        
        foreach ($optionValues as $i => $val) {
            if (!empty($val) && !empty($optionLabels[$i])) {
                $options[trim($val)] = trim($optionLabels[$i]);
            }
        }
        $data['field_options'] = $options;
        
        // DEBUG: Log the options being saved
        error_log("Saving field options for " . $_POST['field_type'] . ": " . print_r($options, true));
    }
    
    // Update field
    $result = $fieldManager->updateField($fieldId, $data);
    if ($result) {
        $_SESSION['success_message'] = 'Field updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['error_message'] = 'Error updating field!';
        $_SESSION['message_type'] = 'error';
    }
    header("Location: custom-field.php?action=edit&id=" . $fieldId);
    exit();
}

// NOW include headers and continue with normal display
require_once 'includes/superheader.php';
require_once '../config/database.php';

// Check if user is super admin (again for display logic)
if ($_SESSION['role'] !== 'super_admin') {
    header("Location: ../admin/access-denied.php");
    exit();
}

// Ensure $pdo is available
if (!isset($pdo)) {
    $database = new Database();
    $pdo = $database->getConnection();
}

require_once 'includes/CustomFieldManager.php';
$fieldManager = new CustomFieldManager($pdo);

$action = $_GET['action'] ?? 'list';
$fieldId = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Handle POST for updates (not creation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_field']) && $fieldId) {
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
    
    if (in_array($_POST['field_type'], ['select', 'radio', 'checkbox'])) {
        $options = [];
        $optionValues = $_POST['option_values'] ?? [];
        $optionLabels = $_POST['option_labels'] ?? [];
        
        foreach ($optionValues as $i => $val) {
            if (!empty($val) && !empty($optionLabels[$i])) {
                $options[trim($val)] = trim($optionLabels[$i]);
            }
        }
        $data['field_options'] = $options;
        
        // DEBUG: Log the options being saved
        error_log("Saving field options for " . $_POST['field_type'] . ": " . print_r($options, true));
    }
    
    // Update field
    $result = $fieldManager->updateField($fieldId, $data);
    if ($result) {
        $_SESSION['success_message'] = 'Field updated successfully!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['error_message'] = 'Error updating field!';
        $_SESSION['message_type'] = 'error';
    }
    header("Location: custom-field.php?action=edit&id=" . $fieldId);
    exit();
}

// Check for session messages first
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $messageType = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['success_message']);
    unset($_SESSION['message_type']);
} elseif (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $messageType = 'error';
    unset($_SESSION['error_message']);
} elseif (isset($_SESSION['form_error'])) {
    $message = $_SESSION['form_error'];
    $messageType = 'error';
    unset($_SESSION['form_error']);
}

// Then check for URL messages
if (isset($_GET['message']) && empty($message)) {
    $message = $_GET['message'];
    $messageType = $_GET['type'] ?? 'success';
}

// Get field data for editing
$field = null;
if ($fieldId && $action === 'edit') {
    $field = $fieldManager->getField($fieldId);
}

$modules = $fieldManager->getAvailableModules();
$fieldTypes = $fieldManager->getFieldTypes();

// Remove unwanted modules
$modulesToRemove = ['inventory', 'schedule_events', 'user'];
foreach ($modulesToRemove as $module) {
    unset($modules[$module]);
}

// Remove unwanted field types
$fieldTypesToRemove = ['email', 'tel', 'url'];
foreach ($fieldTypesToRemove as $type) {
    unset($fieldTypes[$type]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Fields Management - Orphanfare</title>
    <link rel="stylesheet" href="../css/superadmin.css">
    <link rel="stylesheet" href="../css/common.css">
    <style>
        body {
            color: black;
        }
        .content-title {
            color: #007bff;
        }
        .main-content { max-width: 1200px; margin-left: 150px; margin-top: -50px;}
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ddd; }
        .field-form { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .form-section { margin-bottom: 25px; }
        .form-section label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-control:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 2px rgba(0,123,255,0.25); }
        .field-options { margin-top: 10px; border: 1px solid #eee; padding: 15px; border-radius: 4px; background: #f9f9f9; }
        .option-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .option-row input { flex: 1; }
        .option-actions { display: flex; gap: 5px; margin-top: 10px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; padding: 6px 12px; font-size: 12px; }
        .btn-warning { background: #ffc107; color: #212529; padding: 6px 12px; font-size: 12px; }
        .btn-danger { background: #dc3545; color: white; padding: 6px 12px; font-size: 12px; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .fields-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .fields-table th, .fields-table td { padding: 12px; text-align: left; }
        .fields-table th { background: #f8f9fa; font-weight: 600; color: #333; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .badge-type { background: #e2e3e5; color: #383d41; }
        .alert { padding: 12px 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid transparent; }
        .alert-success { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .required { color: #dc3545; }
        .help-text { font-size: 12px; color: #666; margin-top: 4px; font-style: italic; }
        .content-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .action-buttons { display: flex; gap: 5px; }
        .option-preview { margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; border: 1px solid #dee2e6; }
        .option-preview h4 { margin: 0 0 10px 0; font-size: 14px; color: #495057; }
        .dark-theme .option-preview-item { display: inline-block; margin: 5px; padding: 5px 10px; background: white; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px; }
        .light-theme .option-preview-item { display: inline-block; margin: 5px; padding: 5px 10px; color: black;}
        .field-type-info { background: #e7f3ff; border-left: 4px solid #007bff; padding: 10px 15px; margin: 10px 0; border-radius: 4px; }
        .checkbox-info { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px 15px; margin: 10px 0; border-radius: 4px; }
        .custom-title {
            color: #ffffff;
        }

        .light-theme .custom-title {
            color: black;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="header">
            <h1 class="custom-title">Custom Fields Management</h1>
            <?php if ($action === 'list'): ?>
                <a href="?action=add" class="btn btn-primary">+ Add New Field</a>
            <?php else: ?>
                <a href="custom-field.php" class="btn btn-secondary">← Back to Fields</a>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="notification <?php echo $messageType === 'success' ? 'success' : 'error'; ?> show" id="pageNotification">
                <div class="notification-icon"><?php echo $messageType === 'success' ? '✓' : '⚠'; ?></div>
                <div class="notification-content">
                    <div class="notification-title"><?php echo $messageType === 'success' ? 'Success!' : 'Error!'; ?></div>
                    <div class="notification-message"><?php echo htmlspecialchars($message); ?></div>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- List View -->
            <div class="content-box">
                <h2 class="content-title">All Custom Fields</h2>
                <?php $fields = $fieldManager->getAllFields(); ?>
                <?php if (empty($fields)): ?>
                    <p style="text-align: center; padding: 40px; color: #666;">No custom fields found. <a href="?action=add">Create your first field</a></p>
                <?php else: ?>
                    <table class="fields-table">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Label</th>
                                <th>Type</th>
                                <th>Module</th>
                                <th>Required</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fields as $f): ?>
                            <tr>
                                <td><strong class="custom-name"><?php echo htmlspecialchars($f['field_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($f['field_label']); ?></td>
                                <td><span class="badge badge-type"><?php echo ucfirst($f['field_type']); ?></span></td>
                                <td><?php echo htmlspecialchars($modules[$f['module']] ?? $f['module']); ?></td>
                                <td><?php echo $f['is_required'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <span class="badge <?php echo $f['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $f['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="?action=edit&id=<?php echo $f['id']; ?>" class="btn btn-info">Edit</a>
                                    <a href="?action=toggle&id=<?php echo $f['id']; ?>&status=<?php echo $f['is_active'] ? 0 : 1; ?>" class="btn btn-warning">
                                        <?php echo $f['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $f['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this field?')">Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="field-form">
                <h2><?php echo $field ? 'Edit Field' : 'Add New Field'; ?></h2>
                
                <form method="POST" action="custom-field.php?<?php echo $field ? 'action=edit&id=' . $field['id'] : 'action=add'; ?>">
                    <input type="hidden" name="save_field" value="1">
                    
                    <div class="form-section">
                        <label>Field Name (System) *</label>
                        <input type="text" name="field_name" class="form-control" 
                               value="<?php echo htmlspecialchars($field['field_name'] ?? ''); ?>" 
                               pattern="[a-zA-Z0-9_]+" title="Only letters, numbers and underscores" required>
                        <div class="help-text">Internal field name (no spaces, use underscores like: favorite_color)</div>
                    </div>

                    <div class="form-section">
                        <label>Field Label (Display) *</label>
                        <input type="text" name="field_label" class="form-control" 
                               value="<?php echo htmlspecialchars($field['field_label'] ?? ''); ?>" required>
                        <div class="help-text">Label shown to users (like: Favorite Color)</div>
                    </div>

                    <div class="form-section">
                        <label>Field Type *</label>
                        <select name="field_type" class="form-control" id="fieldType" onchange="updateFieldOptions()" required>
                            <?php foreach ($fieldTypes as $type => $label): ?>
                                <option value="<?php echo $type; ?>" <?php echo ($field['field_type'] ?? 'text') === $type ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="help-text" id="fieldTypeHelp">
                            <!-- Dynamic help text will be inserted here -->
                        </div>
                    </div>

                    <div class="form-section">
                        <label>Module *</label>
                        <select name="module" class="form-control" required>
                            <option value="">Select Module</option>
                            <?php foreach ($modules as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($field['module'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-section" id="placeholderSection">
                        <label>Placeholder Text</label>
                        <input type="text" name="placeholder_text" class="form-control" 
                               value="<?php echo htmlspecialchars($field['placeholder_text'] ?? ''); ?>">
                        <div class="help-text">Text shown in the field before user input</div>
                    </div>

                    <div class="form-section" id="defaultValueSection">
                        <label>Default Value</label>
                        <input type="text" name="default_value" class="form-control" 
                               value="<?php echo htmlspecialchars($field['default_value'] ?? ''); ?>">
                        <div class="help-text">Pre-filled value for the field</div>
                    </div>

                    <div class="form-section">
                        <label>Help Text</label>
                        <textarea name="help_text" class="form-control" rows="2"><?php echo htmlspecialchars($field['help_text'] ?? ''); ?></textarea>
                        <div class="help-text">Optional help text displayed below the field</div>
                    </div>

                    <!-- Field Options Section -->
                    <div class="form-section" id="optionsSection" style="display: none;">
                        <label>Field Options *</label>
                        
                        <div id="selectRadioInfo" class="field-type-info">
                            <strong>Options for Dropdown/Radio Fields:</strong> Add options that users can select from. 
                            The <strong>Value</strong> is stored in the database, and the <strong>Label</strong> is displayed to users.
                        </div>
                        
                        <div id="checkboxInfo" class="checkbox-info" style="display: none;">
                            <strong>Options for Checkbox Fields:</strong> Add multiple options that users can check. 
                            Users can select multiple options. The <strong>Value</strong> is stored in the database (as comma-separated), 
                            and the <strong>Label</strong> is displayed to users.
                            <br><br>
                            <strong>Note:</strong> If you don't add any options, it will create a single checkbox that stores 1/0.
                        </div>
                        
                        <div class="field-options" id="optionsContainer">
                            <?php if ($field && in_array($field['field_type'], ['select', 'radio', 'checkbox']) && !empty($field['field_options'])): 
                                foreach ($field['field_options'] as $optValue => $optLabel): ?>
                                    <div class="option-row">
                                        <input type="text" name="option_values[]" placeholder="Option Value (stored in DB)" class="form-control option-value" value="<?php echo htmlspecialchars($optValue); ?>" required>
                                        <input type="text" name="option_labels[]" placeholder="Option Label (displayed)" class="form-control option-label" value="<?php echo htmlspecialchars($optLabel); ?>" required>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">Remove</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="option-row">
                                    <input type="text" name="option_values[]" placeholder="Option Value (stored in DB)" class="form-control option-value" value="">
                                    <input type="text" name="option_labels[]" placeholder="Option Label (displayed)" class="form-control option-label" value="">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">Remove</button>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="option-actions">
                            <button type="button" class="btn btn-success btn-sm" onclick="addOption()">+ Add Option</button>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="addMultipleOptions()">Add Multiple Options</button>
                        </div>
                        
                        <!-- Option Preview -->
                        <div class="option-preview" id="optionPreview" style="display: none;">
                            <h4>Options Preview:</h4>
                            <div id="previewContent"></div>
                        </div>
                        
                        <div class="help-text">
                            <strong>Examples:</strong><br>
                            - Value: "red", Label: "Red"<br>
                            - Value: "high", Label: "High Priority"<br>
                            - Value: "1", Label: "Yes"
                        </div>
                    </div>

                    <div class="form-section">
                        <label>Field Order</label>
                        <input type="number" name="field_order" class="form-control" 
                               value="<?php echo htmlspecialchars($field['field_order'] ?? 0); ?>" min="0">
                        <div class="help-text">Display order (lower numbers show first)</div>
                    </div>

                    <div class="form-section">
                        <label>
                            <input type="checkbox" name="is_required" value="1" <?php echo ($field['is_required'] ?? 0) ? 'checked' : ''; ?>>
                            Required Field
                        </label>
                    </div>

                    <div class="form-section">
                        <button type="submit" class="btn btn-primary"><?php echo $field ? 'Update Field' : 'Create Field'; ?></button>
                        <a href="custom-field.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <style>
        /* Notification Styles - Same as child management */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 400px;
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.3s ease;
        border-left: 4px solid;
    }

    .notification.show {
        transform: translateX(0);
        opacity: 1;
    }

    .previewContent span{
        color: black;
    }

    .notification.success {
        border-left-color: #28a745;
        background: #d4edda;
        color: #155724;
    }

    .notification.error {
        border-left-color: #dc3545;
        background: #f8d7da;
        color: #721c24;
    }

    .notification.warning {
        border-left-color: #ffc107;
        background: #fff3cd;
        color: #856404;
    }

    .notification.info {
        border-left-color: #17a2b8;
        background: #d1ecf1;
        color: #0c5460;
    }

    .notification-icon {
        font-size: 20px;
        font-weight: bold;
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 14px;
        opacity: 0.9;
    }

    .notification-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        opacity: 0.7;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .notification-close:hover {
        opacity: 1;
    }
        .content-box {
            background: #2a2a2a; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .fields-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #3a3a3a;
        }

        .fields-table td {
            font-weight: 600;
            color: white;
            
        }

        .custom-name {
            color:rgb(255, 255, 255); /* Tailwind's green-400 */
            font-weight: 600;
        }

        .fields-table {
            background: #2a2a2a; 
            color: #ffffff;
            box-sizing: none;
        }

        .fields-table th {
            background: #333333;
            font-weight: 600;
            color: #b8c5ff;
        }

        
    </style>

    <script>
        let optionCounter = 1;
        const fieldTypeHelp = {
            'text': 'Simple text input field',
            'textarea': 'Multi-line text area',
            'number': 'Numeric input field',
            'date': 'Date picker field',
            'select': 'Dropdown with predefined options (requires options below)',
            'checkbox': 'Checkbox field - add options for multiple choices, or leave empty for single checkbox',
            'radio': 'Radio button group with predefined options (requires options below)'
        };

        function updateFieldOptions() {
            const fieldType = document.getElementById('fieldType').value;
            const optionsSection = document.getElementById('optionsSection');
            const placeholderSection = document.getElementById('placeholderSection');
            const defaultValueSection = document.getElementById('defaultValueSection');
            const fieldTypeHelpElement = document.getElementById('fieldTypeHelp');
            const selectRadioInfo = document.getElementById('selectRadioInfo');
            const checkboxInfo = document.getElementById('checkboxInfo');
            
            // Update help text
            fieldTypeHelpElement.innerHTML = fieldTypeHelp[fieldType] || 'General input field';
            
            // FIXED: Show options section for select, radio, AND checkbox fields
            if (['select', 'radio', 'checkbox'].includes(fieldType)) {
                optionsSection.style.display = 'block';
                
                // Show appropriate info message
                if (fieldType === 'checkbox') {
                    selectRadioInfo.style.display = 'none';
                    checkboxInfo.style.display = 'block';
                } else {
                    selectRadioInfo.style.display = 'block';
                    checkboxInfo.style.display = 'none';
                }
                
                updateOptionPreview();
            } else {
                optionsSection.style.display = 'none';
            }
            
            // Show/hide placeholder and default value for appropriate fields
            if (['checkbox'].includes(fieldType)) {
                placeholderSection.style.display = 'none';
                defaultValueSection.style.display = 'none';
            } else {
                placeholderSection.style.display = 'block';
                defaultValueSection.style.display = 'block';
            }
            
            // DEBUG: Log what's happening
            console.log('Field type changed to:', fieldType, 'Options section visible:', optionsSection.style.display);
        }

        function addOption() {
            const container = document.getElementById('optionsContainer');
            const html = `
                <div class="option-row">
                    <input type="text" name="option_values[]" placeholder="Option Value (stored in DB)" class="form-control option-value" required>
                    <input type="text" name="option_labels[]" placeholder="Option Label (displayed)" class="form-control option-label" required>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">Remove</button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            
            // Add event listeners to new inputs
            const newInputs = container.lastElementChild.querySelectorAll('input');
            newInputs.forEach(input => {
                input.addEventListener('input', updateOptionPreview);
            });
            
            updateOptionPreview();
        }

        function addMultipleOptions() {
            const options = prompt('Enter multiple options separated by commas (e.g., Red,Green,Blue):');
            if (options) {
                const optionList = options.split(',').map(opt => opt.trim()).filter(opt => opt !== '');
                
                optionList.forEach(option => {
                    const container = document.getElementById('optionsContainer');
                    const html = `
                        <div class="option-row">
                            <input type="text" name="option_values[]" placeholder="Option Value" class="form-control option-value" value="${option.toLowerCase().replace(/\s+/g, '_')}" required>
                            <input type="text" name="option_labels[]" placeholder="Option Label" class="form-control option-label" value="${option}" required>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">Remove</button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', html);
                    
                    // Add event listeners to new inputs
                    const newInputs = container.lastElementChild.querySelectorAll('input');
                    newInputs.forEach(input => {
                        input.addEventListener('input', updateOptionPreview);
                    });
                });
                
                updateOptionPreview();
            }
        }

        function removeOption(button) {
            button.parentElement.remove();
            updateOptionPreview();
        }

        function updateOptionPreview() {
            const preview = document.getElementById('optionPreview');
            const previewContent = document.getElementById('previewContent');
            const optionRows = document.querySelectorAll('.option-row');
            
            if (optionRows.length === 0) {
                preview.style.display = 'none';
                return;
            }
            
            let previewHtml = '';
            optionRows.forEach(row => {
                const valueInput = row.querySelector('.option-value');
                const labelInput = row.querySelector('.option-label');
                
                if (valueInput && labelInput && valueInput.value && labelInput.value) {
                    previewHtml += `<span class="option-preview-item" style="black"><strong>${valueInput.value}</strong>: ${labelInput.value}</span>`;
                }
            });
            
            if (previewHtml) {
                previewContent.innerHTML = previewHtml;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        function validateOptions() {
            const fieldType = document.getElementById('fieldType').value;
            
            // FIXED: Checkbox fields can have options OR be single checkbox (no options)
            if (!['select', 'radio'].includes(fieldType)) {
                return true;
            }
            
            // Only select and radio fields require at least one option
            const optionValues = document.querySelectorAll('input[name="option_values[]"]');
            const optionLabels = document.querySelectorAll('input[name="option_labels[]"]');
            
            // Check if at least one option is filled
            let hasValidOption = false;
            for (let i = 0; i < optionValues.length; i++) {
                if (optionValues[i].value.trim() && optionLabels[i].value.trim()) {
                    hasValidOption = true;
                    break;
                }
            }
            
            if (!hasValidOption) {
                alert('Please add at least one option with both value and label for dropdown/radio fields.');
                return false;
            }
            
            return true;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateFieldOptions();
            
            // Add event listeners to existing option inputs
            document.querySelectorAll('.option-value, .option-label').forEach(input => {
                input.addEventListener('input', updateOptionPreview);
            });
            
            // Add form validation with notifications
            document.querySelector('form')?.addEventListener('submit', function(e) {
                if (!validateOptions()) {
                    e.preventDefault();
                    showNotification('Please add at least one option with both value and label for dropdown/radio fields.', 'error');
                }
            });
        });

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type} show`;
            notification.innerHTML = `
                <div class="notification-icon">${type === 'success' ? '✓' : '⚠'}</div>
                <div class="notification-content">
                    <div class="notification-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">×</button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Auto-remove page notification after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const pageNotification = document.getElementById('pageNotification');
            if (pageNotification) {
                setTimeout(() => {
                    if (pageNotification.parentElement) {
                        pageNotification.remove();
                    }
                }, 5000);
            }
        });
    </script>
</body>
</html>
<?php require_once 'includes/superfooter.php'; ?>