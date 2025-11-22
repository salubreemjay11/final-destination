<?php
$pageTitle = 'Edit Permissions - Orphanfare';
require_once 'includes/superheader.php';

// Check if user is super_admin
if ($_SESSION['role'] !== 'super_admin') {
    header('Location: access-denied.php');
    exit();
}

$role = $_GET['role'] ?? '';
$availableRoles = ['admin', 'Social Worker', 'Social Welfare Assistant', 'user'];

// Validate role
if (!in_array($role, $availableRoles)) {
    header("Location: role-permissions.php?error=Invalid role");
    exit();
}

// Debug: Check current permissions
echo "<!-- DEBUG: Editing permissions for role: $role -->";

// Get current permissions for this role
$permissions = [];
try {
    $sql = "SELECT * FROM permissions WHERE role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $permissions[$row['module']] = $row;
        echo "<!-- DEBUG: Found permission for module: {$row['module']} -->";
    }
    $stmt->close();
} catch (Exception $e) {
    echo "<!-- DEBUG: Error fetching permissions: " . $e->getMessage() . " -->";
}

// Handle permission updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modules = $_POST['modules'] ?? [];
    $updateCount = 0;
    
    echo "<!-- DEBUG: Processing POST data for " . count($modules) . " modules -->";
    
    foreach ($modules as $module => $perms) {
        $can_view = isset($perms['view']) ? 1 : 0;
        $can_edit = isset($perms['edit']) ? 1 : 0;
        $can_delete = isset($perms['delete']) ? 1 : 0;
        $can_create = isset($perms['create']) ? 1 : 0;
        
        echo "<!-- DEBUG: Module: $module, View: $can_view, Edit: $can_edit, Delete: $can_delete, Create: $can_create -->";
        
        // Check if permission record exists
        if (isset($permissions[$module])) {
            // Update existing
            $sql = "UPDATE permissions SET can_view = ?, can_edit = ?, can_delete = ?, can_create = ? 
                    WHERE role = ? AND module = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiiss", $can_view, $can_edit, $can_delete, $can_create, $role, $module);
        } else {
            // Insert new
            $sql = "INSERT INTO permissions (role, module, can_view, can_edit, can_delete, can_create) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiiii", $role, $module, $can_view, $can_edit, $can_delete, $can_create);
        }
        
        if ($stmt->execute()) {
            $updateCount++;
        }
        $stmt->close();
    }
    
    if ($updateCount > 0) {
        $message = "Permissions updated successfully for " . htmlspecialchars($role) . " ($updateCount modules updated)";
        
        // Refresh permissions data
        $permissions = [];
        $sql = "SELECT * FROM permissions WHERE role = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $permissions[$row['module']] = $row;
        }
        $stmt->close();
    } else {
        $error = "No changes were made to permissions.";
    }
}

// Define all available modules
$allModules = [
    'dashboard' => 'Dashboard',
    'child_management' => 'Child Management',
    'case_management' => 'Case Management',
    'donation' => 'Donation',
    'inventory' => 'Inventory Management',
    'foster_info' => 'Foster Information',
    'schedule' => 'Schedule & Events',
    'reports' => 'Reports',
    'settings' => 'Settings',
    'user_management' => 'User Management',
    'role_permissions' => 'Role & Permissions',
    'system_configuration' => 'System Configuration',
    'audit_logs' => 'Audit Logs',
    'custom_fields' => 'Custom Fields'
];
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">Edit Permissions: <?php echo htmlspecialchars($role); ?></h1>
        <a href="role-permissions.php" class="btn btn-secondary">← Back to Roles</a>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="permissions-form">
        <div class="permissions-grid">
            <?php foreach ($allModules as $moduleKey => $moduleName): 
                $modulePerms = $permissions[$moduleKey] ?? [
                    'can_view' => 0, 'can_edit' => 0, 'can_delete' => 0, 'can_create' => 0
                ];
                
                echo "<!-- DEBUG: Module $moduleKey - View: {$modulePerms['can_view']}, Edit: {$modulePerms['can_edit']}, Delete: {$modulePerms['can_delete']}, Create: {$modulePerms['can_create']} -->";
            ?>
            <div class="permission-card">
                <div class="permission-header">
                    <h3><?php echo htmlspecialchars($moduleName); ?></h3>
                </div>
                <div class="permission-options">
                    <label class="permission-checkbox">
                        <input type="checkbox" name="modules[<?php echo $moduleKey; ?>][view]" value="1"
                               <?php echo $modulePerms['can_view'] ? 'checked' : ''; ?>>
                        <span>View</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="modules[<?php echo $moduleKey; ?>][create]" value="1"
                               <?php echo $modulePerms['can_create'] ? 'checked' : ''; ?>>
                        <span>Create</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="modules[<?php echo $moduleKey; ?>][edit]" value="1"
                               <?php echo $modulePerms['can_edit'] ? 'checked' : ''; ?>>
                        <span>Edit</span>
                    </label>
                    <label class="permission-checkbox">
                        <input type="checkbox" name="modules[<?php echo $moduleKey; ?>][delete]" value="1"
                               <?php echo $modulePerms['can_delete'] ? 'checked' : ''; ?>>
                        <span>Delete</span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Save Permissions</button>
            <a href="role-permissions.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<!-- Include the same CSS and JavaScript as before -->
<style>
.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.permission-card {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    padding: 20px;
}

.permission-header h3 {
    color: #ffffff;
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
}

.permission-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.permission-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #cccccc;
    cursor: pointer;
}

.permission-checkbox input[type="checkbox"] {
    width: 16px;
    height: 16px;
}

.permission-checkbox span {
    font-size: 14px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-start;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}
</style>

<?php require_once 'includes/superfooter.php'; ?>