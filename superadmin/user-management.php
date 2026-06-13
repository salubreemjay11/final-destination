
Gio
<?php
ob_start(); 
$pageTitle = 'User Management - Orphanfare';
require_once 'includes/superheader.php';

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Prevent deletion of super admin
    if ($delete_id != 1) {
        // Start transaction to ensure data consistency
        $conn->begin_transaction();
        
        try {
            // First, check for and delete related meeting_requests
            $checkMeetingRequests = "SHOW TABLES LIKE 'meeting_requests'";
            $meetingRequestsResult = $conn->query($checkMeetingRequests);
            if ($meetingRequestsResult && $meetingRequestsResult->num_rows > 0) {
                // Check if the requested_by column exists in meeting_requests table
                $checkColumn = "SHOW COLUMNS FROM meeting_requests LIKE 'requested_by'";
                $columnResult = $conn->query($checkColumn);
                if ($columnResult && $columnResult->num_rows > 0) {
                    $deleteMeetingRequestsSql = "DELETE FROM meeting_requests WHERE requested_by = ?";
                    $meetingRequestsStmt = $conn->prepare($deleteMeetingRequestsSql);
                    $meetingRequestsStmt->bind_param("i", $delete_id);
                    $meetingRequestsStmt->execute();
                    $meetingRequestsStmt->close();
                }
                
                // Also check for meeting_requests where user might be the assigned person
                $checkAssignedColumn = "SHOW COLUMNS FROM meeting_requests LIKE 'assigned_to'";
                $assignedColumnResult = $conn->query($checkAssignedColumn);
                if ($assignedColumnResult && $assignedColumnResult->num_rows > 0) {
                    $deleteAssignedRequestsSql = "DELETE FROM meeting_requests WHERE assigned_to = ?";
                    $assignedRequestsStmt = $conn->prepare($deleteAssignedRequestsSql);
                    $assignedRequestsStmt->bind_param("i", $delete_id);
                    $assignedRequestsStmt->execute();
                    $assignedRequestsStmt->close();
                }
            }
            
            // Delete notifications where user is referenced as admin_id (from admin_notifications table)
            $checkAdminNotifications = "SHOW TABLES LIKE 'admin_notifications'";
            $adminNotificationsResult = $conn->query($checkAdminNotifications);
            if ($adminNotificationsResult && $adminNotificationsResult->num_rows > 0) {
                // Check if admin_id column exists
                $checkAdminIdColumn = "SHOW COLUMNS FROM admin_notifications LIKE 'admin_id'";
                $adminIdColumnResult = $conn->query($checkAdminIdColumn);
                if ($adminIdColumnResult && $adminIdColumnResult->num_rows > 0) {
                    $deleteAdminNotificationsSql = "DELETE FROM admin_notifications WHERE admin_id = ?";
                    $adminNotificationsStmt = $conn->prepare($deleteAdminNotificationsSql);
                    $adminNotificationsStmt->bind_param("i", $delete_id);
                    $adminNotificationsStmt->execute();
                    $adminNotificationsStmt->close();
                }
            }
            
            // Delete related notifications where user is the recipient (user_id)
            $deleteNotificationsSql = "DELETE FROM admin_notifications WHERE user_id = ?";
            $notificationsStmt = $conn->prepare($deleteNotificationsSql);
            $notificationsStmt->bind_param("i", $delete_id);
            $notificationsStmt->execute();
            $notificationsStmt->close();
            
            // Delete related audit logs
            $deleteAuditLogsSql = "DELETE FROM audit_logs WHERE user_id = ?";
            $auditLogsStmt = $conn->prepare($deleteAuditLogsSql);
            $auditLogsStmt->bind_param("i", $delete_id);
            $auditLogsStmt->execute();
            $auditLogsStmt->close();
            
            // Also delete related role change requests if they exist
            $checkRoleRequests = "SHOW TABLES LIKE 'role_change_requests'";
            $tablesResult = $conn->query($checkRoleRequests);
            if ($tablesResult && $tablesResult->num_rows > 0) {
                $deleteRoleRequestsSql = "DELETE FROM role_change_requests WHERE user_id = ?";
                $roleRequestsStmt = $conn->prepare($deleteRoleRequestsSql);
                $roleRequestsStmt->bind_param("i", $delete_id);
                $roleRequestsStmt->execute();
                $roleRequestsStmt->close();
            }
            
            // Check for other common tables with user_id foreign keys
            $tablesToCheck = ['user_sessions', 'login_attempts', 'password_resets', 'user_activities'];
            foreach ($tablesToCheck as $table) {
                $checkTable = "SHOW TABLES LIKE '$table'";
                $tableResult = $conn->query($checkTable);
                if ($tableResult && $tableResult->num_rows > 0) {
                    // Check if table has user_id column
                    $checkColumn = "SHOW COLUMNS FROM $table LIKE 'user_id'";
                    $columnResult = $conn->query($checkColumn);
                    if ($columnResult && $columnResult->num_rows > 0) {
                        $deleteSql = "DELETE FROM $table WHERE user_id = ?";
                        $deleteStmt = $conn->prepare($deleteSql);
                        $deleteStmt->bind_param("i", $delete_id);
                        $deleteStmt->execute();
                        $deleteStmt->close();
                    }
                }
            }
            
            // Now delete the user
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $delete_id);
            
            if ($stmt->execute()) {
                $conn->commit(); // Commit transaction if all queries succeed
                $message = "User deleted successfully";
            } else {
                throw new Exception("Error deleting user: " . $conn->error);
            }
            $stmt->close();
            
        } catch (Exception $e) {
            $conn->rollback(); // Rollback if any query fails
            $error = "Error deleting user. Please check all related records. Error: " . $e->getMessage();
        }
    } else {
        $error = "Cannot delete super admin user";
    }
    
    // Redirect to avoid resubmission
    header("Location: user-management.php?message=" . urlencode($message ?? '') . "&error=" . urlencode($error ?? ''));
    exit();
}

// Fetch all users from database
$sql = "SELECT id, username, email, role, status, created_at, updated_at FROM users";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>


<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">User Management</h1>
        <a href="add-user.php" class="btn btn-primary">Add New User</a>
    </div>

    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="data-table">
        <div class="table-header">
            <input type="text" class="search-input" placeholder="Search users by name, username, or email.">
            <select class="filter-dropdown">
                <option value="">All Roles</option>
                <option value="super_admin">Super Admin</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
                <option value="Social Worker">Social Worker</option>
                <option value="Social Welfare Assistant">Social Welfare Assistant</option>
            </select>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="id-management"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td class="user-name"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td class="user-email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="status-badge <?php 
                                    echo $user['role'] === 'super_admin' ? 'status-urgent' : 
                                         ($user['role'] === 'admin' ? 'status-approved' : 'status-mild'); 
                                ?>">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php 
                                    echo $user['status'] === 'active' ? 'status-active' : 
                                         ($user['status'] === 'pending' ? 'status-pending' : 'status-inactive'); 
                                ?>">
                                    <?php echo htmlspecialchars($user['status']); ?>
                                </span>
                            </td>
                            <td class="user-created"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                    </svg>
                                </a>
                                <?php if ($user['id'] != 1): ?>
                                    <a href="user-management.php?delete_id=<?php echo $user['id']; ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this user? This will also delete all related records (notifications, audit logs, role requests, etc.).')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                        </svg>
                                    </a>
                                <?php else: ?>
                                    <span class="action-btn delete-btn disabled" style="opacity: 0.5; cursor: not-allowed;" title="Super Admin cannot be deleted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                        </svg>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No users found in the database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style> 
    .light-theme .id-management {
        color: #333333;
    }
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #3a3a3a;
        gap: 10px;
    }
    
    .data-table {
        background-color: #2a2a2a;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #3a3a3a;
    }
    
    .filter-dropdown {
        background-color: #2a2a2a;
        border: 1px solid #3a3a3a;
        border-radius: 6px;
        padding: 8px 12px;
        color: #ffffff;
        font-size: 14px;
    }
</style>

<?php 
ob_end_flush(); 
require_once 'includes/superfooter.php'; 
?>
