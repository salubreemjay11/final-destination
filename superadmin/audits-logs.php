<?php
$pageTitle = 'Audit Logs - Orphanfare';
require_once 'includes/superheader.php';

// Build the query for audit logs with filters
$whereConditions = [];
$params = [];
$types = '';

// Handle filters
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$user_filter = $_GET['user_filter'] ?? '';
$action_filter = $_GET['action_filter'] ?? '';
$search = $_GET['search'] ?? '';

// Add date filter
if ($start_date && $end_date) {
    $whereConditions[] = "DATE(a.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
}

// Add user filter
if (!empty($user_filter)) {
    $whereConditions[] = "u.username = ?";
    $params[] = $user_filter;
    $types .= 's';
}

// Add action filter
if (!empty($action_filter)) {
    $whereConditions[] = "a.action = ?";
    $params[] = $action_filter;
    $types .= 's';
}

// Add search filter
if (!empty($search)) {
    $whereConditions[] = "(u.username LIKE ? OR u.email LIKE ? OR a.description LIKE ? OR a.ip_address LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

// Build the final query
$sql = "SELECT a.*, u.username, u.email 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id";
        
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY a.created_at DESC LIMIT 100";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$audit_logs = [];
if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $audit_logs[] = $row;
        }
    }
}
$stmt->close();

// Get unique users for filter dropdown
$users = [];
$user_sql = "SELECT DISTINCT username FROM users ORDER BY username";
$user_result = $conn->query($user_sql);
if ($user_result && $user_result->num_rows > 0) {
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row['username'];
    }
}

// Get unique actions for filter dropdown
$actions = [];
$action_sql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
$action_result = $conn->query($action_sql);
if ($action_result && $action_result->num_rows > 0) {
    while ($row = $action_result->fetch_assoc()) {
        $actions[] = $row['action'];
    }
}
?>

<!-- Print Styles -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .print-section, .print-section * {
        visibility: visible;
    }
    .print-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f0f0f0;
    }
}
</style>

<div class="page-active">
    <div class="page-header">
        <div>
            <h1 class="page-title">System Audit Logs</h1>
            <p class="page-subtitle">Track all system activities and user actions</p>
        </div>
        <div>
            <button type="button" onclick="printAuditLogs()" class="btn btn-secondary no-print" style="margin-right: 10px;">
                Print Report
            </button>
            <a href="user-management.php?action=add" class="btn btn-primary no-print">Add New User</a>
        </div>
    </div>
    
    <form method="GET" action="audits-logs.php" class="data-table no-print">
        <div class="table-header">
            <div class="filter-row">
                <label class="date-range">Date Range:</label>
                <input type="date" class="date-input" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <span class="to">to</span>
                <input type="date" class="date-input" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

                <select class="filter-dropdown" name="user_filter">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $user_filter === $user ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="filter-dropdown" name="action_filter">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action); ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($action); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="audits-logs.php" class="btn btn-secondary">Clear Filters</a>
            </div>

            <input type="text" class="search-input" name="search" placeholder="Search users by name, username, or email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
    </form>

    <div class="data-table print-section" style="margin-top: 20px;">
        <!-- Print Header -->
        <div style="display: none;" class="print-header">
            <h2>Audit Logs Report - Orphanfare</h2>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
            <?php if ($start_date || $end_date || $user_filter || $action_filter || $search): ?>
                <p>Filters Applied: 
                    <?php 
                    $filters = [];
                    if ($start_date && $end_date) $filters[] = "Date: $start_date to $end_date";
                    if ($user_filter) $filters[] = "User: $user_filter";
                    if ($action_filter) $filters[] = "Action: $action_filter";
                    if ($search) $filters[] = "Search: $search";
                    echo implode(', ', $filters);
                    ?>
                </p>
            <?php endif; ?>
            <hr>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($audit_logs) > 0): ?>
                    <?php foreach ($audit_logs as $log): ?>
                        <tr>
                            <td class="timestamp"><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                            <td>
                                <?php if ($log['username']): ?>
                                    <strong class="username"><?php echo htmlspecialchars($log['username']); ?></strong><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($log['email'] ?? ''); ?></small>
                                <?php else: ?>
                                    <span style="color: #666;">System</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php 
                                    echo getActionBadgeClass($log['action']);
                                ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="description"><?php echo htmlspecialchars($log['description'] ?? 'No description'); ?></td>
                            <td>
                                <code style="color: #888; font-size: 12px;">
                                    <?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?>
                                </code>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                            <?php if (!empty($start_date) || !empty($end_date) || !empty($user_filter) || !empty($action_filter) || !empty($search)): ?>
                                No audit logs found matching your filters.
                            <?php else: ?>
                                No audit logs found in the system.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (count($audit_logs) > 0): ?>
            <div style="padding: 15px; text-align: center; color: #666; border-top: 1px solid #3a3a3a;">
                Showing <?php echo count($audit_logs); ?> most recent audit logs
                <?php if (count($audit_logs) >= 100): ?>
                    <br><small>(Limited to 100 most recent records)</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.light-theme .timestamp {
    color: #000;
}
.light-theme .description {
    color: #000;
}
    .date-range {
        margin-right: 10px;
        font-weight: 500;
        color: #ffffff;
    }

    .to {
        margin: 0 10px;
        color: #ffffff;
    }

    .page-subtitle {
    font-size: 16px;
    color: #cccccc;
}

.filter-dropdown {
    background-color: white;
    border-radius: 6px;
    padding: 8px 12px;
    color: white;
    font-size: 14px;
    background-color: #1a1a1a;
    border: 1px solid #3a3a3a;
}

.date-input {
    background-color: #1a1a1a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    padding: 8px 12px;
    color: #ffffff;
    font-size: 14px;
}

.light-theme .username {
    color: #000;
}
</style>

<script>
function printAuditLogs() {
    // Show print header
    const printHeader = document.querySelector('.print-header');
    if (printHeader) {
        printHeader.style.display = 'block';
    }
    
    // Trigger print
    window.print();
    
    // Hide print header again after printing
    if (printHeader) {
        printHeader.style.display = 'none';
    }
}

// Add event listener for print dialog close
window.addEventListener('afterprint', function() {
    const printHeader = document.querySelector('.print-header');
    if (printHeader) {
        printHeader.style.display = 'none';
    }
});
</script>

<?php require_once 'includes/superfooter.php'; ?>