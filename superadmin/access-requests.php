<?php
$pageTitle = 'Request Access - Orphanfare';
require_once 'includes/superheader.php';

// Check if user is Social Welfare Assistant
if ($_SESSION['role'] !== 'Social Welfare Assistant') {
    header('Location: dashboard.php');
    exit();
}

$permissionManager = new PermissionManager($conn, $_SESSION['role'], $_SESSION['user_id']);

// Handle access request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_access'])) {
    $module = $_POST['module'];
    $reason = $_POST['reason'];
    
    if ($permissionManager->requestAccess($module, $reason)) {
        $success = "Access request submitted successfully! Super Admin will review your request.";
    } else {
        $error = "Failed to submit access request. Please try again.";
    }
}

// Get available modules that user doesn't have access to
$allModules = ['user_management', 'role_permissions', 'system_configuration', 'audit_logs', 'custom_fields'];
$requestableModules = [];

foreach ($allModules as $module) {
    if (!$permissionManager->hasPermission($module) && !$permissionManager->hasPendingRequest($module)) {
        $requestableModules[] = $module;
    }
}
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">Request Additional Access</h1>
        <p class="page-subtitle">Request access to additional system features</p>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">Available Access Requests</div>
        </div>
        
        <?php if (empty($requestableModules)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                You don't have any pending access requests or you've already requested all available modules.
            </p>
        <?php else: ?>
            <div class="access-requests-grid">
                <?php foreach ($requestableModules as $module): ?>
                <div class="access-request-card">
                    <h4><?php echo ucwords(str_replace('_', ' ', $module)); ?></h4>
                    <p>Request access to manage <?php echo str_replace('_', ' ', $module); ?> features</p>
                    
                    <form method="POST" class="request-form">
                        <input type="hidden" name="module" value="<?php echo $module; ?>">
                        <textarea name="reason" placeholder="Explain why you need access to this feature..." 
                                  required class="form-input" style="width: 100%; height: 80px; margin-bottom: 10px;"></textarea>
                        <button type="submit" name="request_access" class="btn btn-primary">Request Access</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pending Requests -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">Pending Requests</div>
        </div>
        
        <?php
        $sql = "SELECT ar.*, u.username as reviewed_by_name 
                FROM access_requests ar 
                LEFT JOIN users u ON ar.reviewed_by = u.id 
                WHERE ar.user_id = ? AND ar.status = 'pending' 
                ORDER BY ar.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Reason</th>
                        <th>Requested Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo ucwords(str_replace('_', ' ', $row['requested_module'])); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-pending">Pending Review</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #666;">No pending access requests.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/superfooter.php'; ?>