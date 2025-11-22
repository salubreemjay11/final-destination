<?php
// admin/includes/form-template.php

class FormTemplate {
    private $enforcer;
    
    public function __construct($permissionEnforcer) {
        $this->enforcer = $permissionEnforcer;
    }
    
    // Standard form header with permission checks and read-only banner
    public function formHeader($title, $backUrl = '') {
        $html = "<div class='page-active'>";
        
        // Show read-only banner if in read-only mode
        $html .= $this->enforcer->getReadOnlyBanner();
        
        $html .= "<div class='page-header'>";
        $html .= "<h1 class='page-title'>$title";
        
        // Add read-only badge if in read-only mode
        if ($this->enforcer->isReadOnlyMode()) {
            $html .= " <span class='status-badge status-mild' style='font-size: 14px;'>Read-Only</span>";
        }
        
        $html .= "</h1>";
        
        if ($backUrl) {
            $html .= "<a href='$backUrl' class='btn btn-secondary'>← Back</a>";
        }
        
        $html .= "</div>";
        return $html;
    }
    
    // Standard form actions (save/cancel buttons) - completely hidden in read-only mode
    public function formActions($module) {
        // If no edit permission, don't show any form actions
        if (!$this->enforcer->isActionAllowed('edit')) {
            return "<div class='form-actions'>
                <button type='button' class='btn btn-secondary' disabled>📋 Read-Only Mode - No Editing Allowed</button>
                <a href='{$module}.php' class='btn btn-primary'>Back to List</a>
            </div>";
        }
        
        // If edit permission exists, show normal actions
        $html = "<div class='form-actions'>";
        $html .= "<button type='submit' class='btn btn-primary'>💾 Save Changes</button>";
        $html .= "<a href='{$module}.php' class='btn btn-secondary'>Cancel</a>";
        $html .= "</div>";
        return $html;
    }
    
    // Standard data table with action buttons - hide edit/delete in read-only mode
    public function dataTable($data, $columns, $module) {
        $html = "<table class='data-table'>";
        $html .= "<thead><tr>";
        
        // Headers
        foreach ($columns as $column) {
            $html .= "<th>$column</th>";
        }
        $html .= "<th>Actions</th>";
        $html .= "</tr></thead>";
        
        $html .= "<tbody>";
        foreach ($data as $row) {
            $html .= "<tr>";
            foreach ($columns as $columnKey => $columnName) {
                $html .= "<td>" . htmlspecialchars($row[$columnKey] ?? '') . "</td>";
            }
            
            // Action buttons - only show view in read-only mode
            $html .= "<td class='action-buttons' style='display: flex; gap: 5px;'>";
            
            // Always show view button
            $html .= $this->enforcer->actionButton('view', '👁️ View', "view-$module.php?id={$row['id']}", 'btn btn-primary btn-sm');
            
            // Only show edit/delete if edit permission exists
            if ($this->enforcer->isActionAllowed('edit')) {
                $html .= $this->enforcer->actionButton('edit', '✏️ Edit', "edit-$module.php?id={$row['id']}", 'btn btn-secondary btn-sm');
                $html .= $this->enforcer->actionButton('delete', '🗑️ Delete', "delete-$module.php?id={$row['id']}", 'btn btn-danger btn-sm', 'Are you sure?');
            } else {
                // Show disabled edit/delete buttons in read-only mode
                $html .= "<button class='btn btn-secondary btn-sm' disabled title='Read-only mode'>✏️ Edit</button>";
                $html .= "<button class='btn btn-danger btn-sm' disabled title='Read-only mode'>🗑️ Delete</button>";
            }
            
            $html .= "</td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        
        return $html;
    }
    
    // Completely disable add new button in read-only mode
    public function addButton($module) {
        return $this->enforcer->actionButton('create', '➕ Add New', "add-$module.php", 'btn btn-primary');
    }
}
?>