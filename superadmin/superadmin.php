<?php
$pageTitle = 'Super Admin Dashboard - Orphanfare';
require_once 'includes/superheader.php';

// Get statistics
$totalActiveUsers = getTotalActiveUsers($conn);
$newUsersThisMonth = getNewUsersThisMonth($conn);
$pendingRoleRequests = getPendingRoleRequests($conn);
?>

<div class="page-active">
    <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Super Admin'); ?>. Here's a quick overview of your system</p>
        <?php if ($conn->connect_error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin: 10px 0;">
                <strong>Database Notice:</strong> Connected but some tables might not exist yet. Run the SQL setup script.
            </div>
        <?php endif; ?>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-title">System Users</div>
            <div class="card-stat">
                <span class="stat-label">Total Active Users</span>
                <span class="stat-value"><?php echo $totalActiveUsers; ?></span>
            </div>
            <div class="card-stat">
                <span class="stat-label">New Users This Month</span>
                <span class="stat-value"><?php echo $newUsersThisMonth; ?></span>
            </div>
            <a href="user-management.php" class="btn btn-primary">Manage Users</a>
        </div>
        
        <div class="dashboard-card">
            <div class="card-title">Pending Approvals</div>
            <div class="card-stat">
                <span class="stat-label">Pending Role Change Requests</span>
                <span class="stat-value"><?php echo $pendingRoleRequests; ?></span>
            </div>
            <a href="role-permissions.php" class="btn btn-success">Review Requests</a>
        </div>

        
    </div>

    <div class="dashboard-card">
        <div class="card-title">Quick Links</div>
        <div class="quick-links">
            <a href="user-management.php?action=add" class="quick-link-card">
                <div class="quick-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                </svg></div>
                <div class="quick-link-title">Add New User</div>
            </a>
            <a href="system-configuration.php" class="quick-link-card">
                <div class="quick-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                </svg></div>
                <div class="quick-link-title">Configure System Settings</div>
            </a>
            <a href="audits-logs.php" class="quick-link-card">
                <div class="quick-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart" viewBox="0 0 16 16">
                <path d="M4 11H2v3h2zm5-4H7v7h2zm5-5v12h-2V2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1z"/>
                </svg></div>
                <div class="quick-link-title">Review Audit Logs</div>
            </a>
            <a href="custom-field.php" class="quick-link-card">
                <div class="quick-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal" viewBox="0 0 16 16">
                <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                </svg></div>
                <div class="quick-link-title">Manage Custom Fields</div>
            </a>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="dashboard-card" style="margin-top: 30px;">
        <div class="card-title">Recent Activity</div>
        <?php
        // Get recent audit logs
        $sql = "SELECT a.*, u.username 
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id 
                ORDER BY a.created_at DESC 
                LIMIT 5";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0): ?>
            <table style="width: 100%; margin-top: 15px;">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="time">
                                <?php echo date('M j, g:i A', strtotime($row['created_at'])); ?>
                            </td>
                            <td>
                                <strong class="username-dashboard"><?php echo htmlspecialchars($row['username'] ?? 'System'); ?></strong>
                            </td>
                            <td>
                                <span class="status-badge <?php 
                                    echo getActionBadgeClass($row['action']);
                                ?>">
                                    <?php echo htmlspecialchars($row['action']); ?>
                                </span>
                            </td>
                            <td class="description">
                                <?php echo htmlspecialchars($row['description'] ?? 'No description'); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No recent activity found.</p>
        <?php endif; ?>
        <div style="text-align: center; margin-top: 15px;">
            <a href="audits-logs.php" class="btn btn-secondary">View All Activity</a>
        </div>
    </div>
</div>

<style> 
.light-theme .time {
    color: black;
}

.light-theme .description {
    color: #3a3a3a;
}

.dashboard-card {
    background-color: #2a2a2a;
    border-radius: 8px;
    padding: 24px;
    border: 1px solid #3a3a3a;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    color: #ffffff;
}

.stat-label {
    color: #cccccc;
    font-size: 14px;
}

.quick-link-card {
    
    padding: 24px;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    border: 1px solid #3a3a3a;
}

.quick-link-title {
    font-size: 16px;
    font-weight: 600;
    color: #ffffff;
    text-decoration: none;
}

.light-theme .username-dashboard{
    color: #3a3a3a;
}

</style>
<?php require_once 'includes/superfooter.php'; ?>