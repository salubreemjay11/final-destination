<?php
$pageTitle = 'Role Change Requests - Orphanfare';
require_once 'includes/superheader.php';

// Debug: Check table structure
echo "<!-- Debug: Checking role_change_requests table structure -->";
$debug_sql = "DESCRIBE role_change_requests";
$debug_result = $conn->query($debug_sql);
if ($debug_result) {
    echo "<!-- Table columns: ";
    while ($debug_row = $debug_result->fetch_assoc()) {
        echo $debug_row['Field'] . ", ";
    }
    echo " -->";
}

// Handle request actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $requestId = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        // Update the request status
        $sql = "UPDATE role_change_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $_SESSION['user_id'], $requestId);
        
        if ($stmt->execute()) {
            // If approved, update user's role
            if ($action === 'approve') {
                // Try both possible column names
                $getRequestSql = "SELECT user_id, requested_role, new_role FROM role_change_requests WHERE id = ?";
                $getStmt = $conn->prepare($getRequestSql);
                $getStmt->bind_param("i", $requestId);
                $getStmt->execute();
                $result = $getStmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    // Use whichever column exists
                    $newRole = $row['requested_role'] ?? $row['new_role'] ?? '';
                    
                    if (!empty($newRole)) {
                        $updateUserSql = "UPDATE users SET role = ? WHERE id = ?";
                        $updateStmt = $conn->prepare($updateUserSql);
                        $updateStmt->bind_param("si", $newRole, $row['user_id']);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                }
                $getStmt->close();
            }
            
            $message = "Request " . $action . "d successfully";
        } else {
            $error = "Error processing request: " . $conn->error;
        }
        $stmt->close();
        
        header("Location: role-requests.php?message=" . urlencode($message ?? '') . "&error=" . urlencode($error ?? ''));
        exit();
    }
}

// Fetch all role change requests - Try both possible column names
$sql = "SELECT r.*, u.username, u.role as user_current_role, ur.username as reviewer_name 
        FROM role_change_requests r 
        JOIN users u ON r.user_id = u.id 
        LEFT JOIN users ur ON r.reviewed_by = ur.id 
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
$requests = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">Role Change Requests</h1>
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
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Current Role</th>
                    <th>Requested Role</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested Date</th>
                    <th>Reviewed By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($requests) > 0): ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td style="color: #047857"><?php echo htmlspecialchars($request['id']); ?></td>
                            <td class="username"><?php echo htmlspecialchars($request['username']); ?></td>
                            <td>
                                <span class="status-badge status-mild">
                                    <?php echo htmlspecialchars($request['user_current_role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-progress">
                                    <?php 
                                    // Try both possible column names
                                    $requestedRole = $request['requested_role'] ?? $request['new_role'] ?? 'Unknown';
                                    echo htmlspecialchars($requestedRole); 
                                    ?>
                                </span>
                            </td>
                            <td class="reason"><?php echo htmlspecialchars($request['reason'] ?: 'No reason provided'); ?></td>
                            <td>
                                <span class="status-badge <?php 
                                    echo $request['status'] === 'approved' ? 'status-approved' : 
                                         ($request['status'] === 'rejected' ? 'status-urgent' : 'status-pending'); 
                                ?>">
                                    <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                                </span>
                            </td>
                            <td class="requested_date"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                            <td class="reviewer_name"><?php echo $request['reviewer_name'] ? htmlspecialchars($request['reviewer_name']) : 'Not reviewed'; ?></td>
                            <td class="buttons-actions">
                                <?php if ($request['status'] === 'pending'): ?>
                                    <a href="role-requests.php?action=approve&id=<?php echo $request['id']; ?>" 
                                       class="action-btn edit-btn" 
                                       onclick="return confirm('Approve this role change request?')">Approve</a>
                                    <a href="role-requests.php?action=reject&id=<?php echo $request['id']; ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('Reject this role change request?')">Reject</a>
                                <?php else: ?>
                                    <span style="color: #666; font-size: 12px;">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 20px;">No role change requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .buttons-actions .action-btn {
        margin-right: 5px;
        display: flex;
        margin-bottom: 5px;
    }

    .light-theme .username {
        color: #1e293b;
    }

    .light-theme .reason {
        color: #1e293b;
    }

    .light-theme .requested_date {
        color: #1e293b;
    }

    .light-theme .reviewer_name {
        color: #1e293b;
    }
</style>

<?php require_once 'includes/superfooter.php'; ?>