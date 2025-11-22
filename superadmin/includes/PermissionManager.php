<?php
/**
 * PermissionManager.php
 * Manages user permissions and role-based access control
 */

class PermissionManager {
    private $conn;
    private $role;
    private $user_id;
    private $is_pdo = false;
    
    public function __construct($conn, $role, $user_id) {
        $this->conn = $conn;
        $this->role = $role;
        $this->user_id = $user_id;
        $this->is_pdo = $conn instanceof PDO;
    }
    
    public function hasPermission($module, $action) {
        // Super admin has all permissions
        if ($this->role === 'super_admin') {
            return true;
        }
        
        // Check permissions from database
        if ($this->is_pdo) {
            // PDO version
            $sql = "SELECT can_view, can_edit, can_delete, can_create 
                    FROM permissions 
                    WHERE role = ? AND module = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$this->role, $module]);
            $permission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$permission) {
                return false;
            }
        } else {
            // MySQLi version
            $sql = "SELECT can_view, can_edit, can_delete, can_create 
                    FROM permissions 
                    WHERE role = ? AND module = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $this->role, $module);
            $stmt->execute();
            $result = $stmt->get_result();
            $permission = $result->fetch_assoc();
            
            if (!$permission) {
                return false;
            }
        }
        
        if ($permission) {
            switch ($action) {
                case 'view': return (bool)$permission['can_view'];
                case 'edit': return (bool)$permission['can_edit'];
                case 'delete': return (bool)$permission['can_delete'];
                case 'create': return (bool)$permission['can_create'];
                default: return false;
            }
        }
        
        return false;
    }
    
    public function canPerformAction($action) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        $module = $this->getModuleFromPage($currentPage);
        return $this->hasPermission($module, $action);
    }
    
    public function showActionButton($action, $buttonText, $url = '#', $confirmMessage = '') {
        if ($this->canPerformAction($action)) {
            if ($confirmMessage) {
                return "<a href='$url' class='btn btn-primary' onclick='return confirm(\"$confirmMessage\")'>$buttonText</a>";
            } else {
                return "<a href='$url' class='btn btn-primary'>$buttonText</a>";
            }
        } else {
            return "<button class='btn btn-secondary' disabled title='No permission'>$buttonText</button>";
        }
    }
    
    public function showFormField($action, $fieldHtml) {
        if ($this->canPerformAction($action)) {
            return $fieldHtml;
        } else {
            return "<div style='opacity: 0.6;' title='No permission to edit'>$fieldHtml</div>";
        }
    }
    
    public function getAccessibleModules() {
        // Super admin can access all modules
        if ($this->role === 'super_admin') {
            return ['dashboard', 'child_management', 'case_management', 'donation', 'inventory', 
                   'foster_info', 'schedule', 'reports', 'settings'];
        }
        
        // Get modules this role can view
        if ($this->is_pdo) {
            // PDO version
            $sql = "SELECT module FROM permissions WHERE role = ? AND can_view = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$this->role]);
            $modules = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            // MySQLi version
            $sql = "SELECT module FROM permissions WHERE role = ? AND can_view = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $this->role);
            $stmt->execute();
            $result = $stmt->get_result();
            $modules = [];
            while ($row = $result->fetch_assoc()) {
                $modules[] = $row['module'];
            }
        }
        
        return $modules;
    }
    
    public function getModuleFromPage($pageName) {
        $pageToModuleMap = [
            'dashboard.php' => 'dashboard',
            'child-management.php' => 'child_management',
            'case-management.php' => 'case_management',
            'case-info.php' => 'case_management', 
            'case-details.php' => 'case_management', 
            'unified-registration.php' => 'child_management',
            'donation.php' => 'donation',
            'donation-reports.php' => 'donation',
            'donation-history.php' => 'donation',
            'process-donation.php' => 'donation',
            'ajax-process-donation.php' => 'donation',
            'inventory.php' => 'inventory',
            'foster-info.php' => 'foster_info',
            'new-foster.php' => 'foster_info',
            'schedule.php' => 'schedule',
            'reports.php' => 'reports',
            'settings.php' => 'settings',
            'request-role.php' => 'dashboard',
            'superadmin.php' => 'dashboard',
            'user-management.php' => 'user_management',
            'role-permissions.php' => 'role_permissions',
            'system-configuration.php' => 'system_configuration',
            'audits-logs.php' => 'audit_logs',
            'custom-fields.php' => 'custom_fields',
            'edit-permissions.php' => 'role_permissions'
        ];
        
        return $pageToModuleMap[$pageName] ?? $pageName;
    }
    
    public function canAccessPage($pageName) {
        $module = $this->getModuleFromPage($pageName);
        return $this->hasPermission($module, 'view');
    }
}

// Helper functions
function showMenuItem($permissionManager, $module) {
    return $permissionManager->hasPermission($module, 'view');
}

function checkPageAccess($permissionManager, $currentPage) {
    if (!$permissionManager->canAccessPage($currentPage)) {
        header('Location: access-denied.php');
        exit();
    }
}

function canPerformAction($permissionManager, $action) {
    return $permissionManager->canPerformAction($action);
}

function showActionButton($permissionManager, $action, $buttonText, $url = '#', $confirmMessage = '') {
    return $permissionManager->showActionButton($action, $buttonText, $url, $confirmMessage);
}

function getRoleDisplayName($role) {
    return match($role) {
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'Social Worker' => 'Social Worker',
        'Social Welfare Assistant' => 'Social Welfare Assistant',
        default => ucfirst($role)
    };
}
?>
