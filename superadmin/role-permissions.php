<?php
$pageTitle = 'Role & Permissions - Orphanfare';
require_once 'includes/superheader.php';

// Get role statistics
$roles = [
    'Social Worker' => 0,
    'Social Welfare Assistant' => 0,
    'admin' => 0,
    'user' => 0,
    'super_admin' => 0
];

// Count users per role
$sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roles[$row['role']] = $row['count'];
    }
}

// Handle role change requests
$pendingRequests = 0;
$sql = "SELECT COUNT(*) as count FROM role_change_requests WHERE status = 'pending'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pendingRequests = $row['count'];
}
?>

<div class="page-active">

    <?php if ($pendingRequests > 0): ?>
        <div class="dashboard-card" style="background-color: #fff3cd; border-color: #ffeaa7; margin-bottom: 20px;">
            <div class="card-header">
                <div class="card-title" style="color: #856404;">Pending Role Change Requests</div>
                <a href="role-requests.php" class="btn btn-warning">Review Requests</a>
            </div>
            <p style="color: #856404; margin-bottom: 12px;">
                There are <?php echo $pendingRequests; ?> pending role change requests waiting for approval.
            </p>
        </div>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">Super Admin</div>
                <span class="status-badge status-urgent">System Role</span>
            </div>
            <p style="color:rgb(134, 132, 132); margin-bottom: 12px;">Full system access with all permissions</p>
            <p style="color: #666666; font-size: 14px;"><?php echo $roles['super_admin']; ?> users assigned to this role</p>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">Admin</div>
                <a href="edit-permissions.php?role=admin" class="btn btn-primary">Edit Permissions</a>
            </div>
            <p style="color: rgb(134, 132, 132); margin-bottom: 12px;">Administrative access to user management and system configuration</p>
            <p style="color: #666666; font-size: 14px;"><?php echo $roles['admin']; ?> users assigned to this role</p>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">Social Worker</div>
                <a href="edit-permissions.php?role=Social Worker" class="btn btn-primary">Edit Permissions</a>
            </div>
            <p style="color: rgb(134, 132, 132); margin-bottom: 12px;">Access to child records, case management, and reporting</p>
            <p style="color: #666666; font-size: 14px;"><?php echo $roles['Social Worker']; ?> users assigned to this role</p>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">Social Welfare Assistant</div>
                <a href="edit-permissions.php?role=Social Welfare Assistant" class="btn btn-primary">Edit Permissions</a>
            </div>
            <p style="color: rgb(134, 132, 132); margin-bottom: 12px;">Basic access to view and update assigned cases</p>
            <p style="color: #666666; font-size: 14px;"><?php echo $roles['Social Welfare Assistant']; ?> users assigned to this role</p>
        </div>
        
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-title">User</div>
                <a href="edit-permissions.php?role=user" class="btn btn-primary">Edit Permissions</a>
            </div>
            <p style="color: rgb(134, 132, 132); margin-bottom: 12px;">Basic access with limited permissions</p>
            <p style="color: #666666; font-size: 14px;"><?php echo $roles['user']; ?> users assigned to this role</p>
        </div>
    </div>

    <!-- Role Change Requests Section -->
    <div class="dashboard-card" style="margin-top: 30px;">
        <div class="card-header">
            <div class="card-title">Recent Role Change Requests</div>
            <a href="role-requests.php" class="btn btn-secondary">View All</a>
        </div>
        
        <?php
        // Get recent role change requests - CORRECTED: Use requested_role instead of new_role
        $sql = "SELECT r.id, u.username, r.requested_role, r.status, r.created_at 
                FROM role_change_requests r 
                JOIN users u ON r.user_id = u.id 
                ORDER BY r.created_at DESC 
                LIMIT 5";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0): ?>
            <table style="width: 100%; margin-top: 15px;">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Requested Role</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="color: #047857"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="requested_role"><?php echo htmlspecialchars($row['requested_role']); ?></td>
                            <td>
                                <span class="status-badge <?php 
                                    echo $row['status'] === 'approved' ? 'status-approved' : 
                                         ($row['status'] === 'rejected' ? 'status-urgent' : 'status-pending'); 
                                ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td class="date"><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <a href="role-requests.php?action=approve&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">Approve</a>
                                    <a href="role-requests.php?action=reject&id=<?php echo $row['id']; ?>" class="action-btn delete-btn">Reject</a>
                                <?php else: ?>
                                    <span style="color: #666; font-size: 12px;">Completed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No role change requests found.</p>
        <?php endif; ?>
    </div>
</div>
<style>
    

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.light-theme .requested_role {
    color: #1e293b;
}

.light-theme .date {
    color: #1e293b;
}

.card-header
.card-stat {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.dashboard-card {
    background-color: #2a2a2a;
    border-radius: 8px;
    padding: 24px;
    border: 1px solid #3a3a3a;
}

</style>
<?php require_once 'includes/superfooter.php'; ?>