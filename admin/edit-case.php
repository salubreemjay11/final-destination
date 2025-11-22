<?php
require_once 'includes/header.php';

// If no edit permission, redirect to view page or show read-only view
if (!$permissionEnforcer->isActionAllowed('edit')) {
    // Redirect to view page or show read-only message
    header('Location: case-management.php?message=Read-only access - Editing not allowed');
    exit();
}

$case_id = $_GET['id'] ?? 0;
$case = [];

if ($case_id) {
    $sql = "SELECT * FROM cases WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$case_id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$case) {
    header('Location: case-management.php');
    exit();
}

$formTemplate = new FormTemplate($permissionEnforcer);
?>

<?php echo $formTemplate->formHeader("Edit Case: {$case['case_id']}", 'case-management.php'); ?>

<!-- Use the renderForm method which will disable the entire form if no edit permission -->
<?php 
$formContent = "
    <input type='hidden' name='case_id' value='{$case['id']}'>
    
    <div class='form-group'>
        <label>Case ID:</label>
        " . $permissionEnforcer->formField('edit', 'case_id_display', $case['case_id'], 'text') . "
    </div>
    
    <div class='form-group'>
        <label>Case Type:</label>
        " . $permissionEnforcer->formField('edit', 'case_type', $case['case_type'], 'text') . "
    </div>
    
    <div class='form-group'>
        <label>Child Name:</label>
        " . $permissionEnforcer->formField('edit', 'child_name', $case['child_name'], 'text') . "
    </div>
    
    <div class='form-group'>
        <label>Description:</label>
        " . $permissionEnforcer->formField('edit', 'description', $case['description'], 'textarea') . "
    </div>
    
    <div class='form-group'>
        <label>Status:</label>
        " . $permissionEnforcer->formField('edit', 'status', $case['status'], 'select', [
            'Open' => 'Open',
            'Under Investigation' => 'Under Investigation', 
            'Closed' => 'Closed'
        ]) . "
    </div>
    
    " . $formTemplate->formActions('case-management');

// This will either render a normal form or a disabled form based on permissions
echo $permissionEnforcer->renderForm('edit', $formContent, 'update-case.php');
?>

</div> <!-- Close page-active -->

<?php require_once 'includes/footer.php'; ?>