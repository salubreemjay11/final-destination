<?php
$pageTitle = 'Dashboard - Orphanfare';
require_once 'includes/header.php';

// Get dashboard statistics (your existing code remains the same)
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cases WHERE status IN ('Open', 'Under Investigation')");
    $activeCases = $stmt->fetch()['count'];
} catch (Exception $e) {
    $activeCases = 0;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM children WHERE status = 'In Care'");
    $childrenInCare = $stmt->fetch()['count'];
} catch (Exception $e) {
    $childrenInCare = 0;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM children WHERE status = 'Adopted'");
    $successfulAdoptions = $stmt->fetch()['count'];
} catch (Exception $e) {
    $successfulAdoptions = 0;
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cases WHERE status = 'Open' AND priority = 'urgent'");
    $pendingReferrals = $stmt->fetch()['count'];
} catch (Exception $e) {
    $pendingReferrals = 0;
}

// Get recent activities from actual database tables
$recentActivities = [];
try {
    // First try to get from activity_log table if it exists
    $stmt = $pdo->prepare("
        SELECT al.activity_type as activity, al.description, al.performed_by, al.created_at as activity_date,
               u.username as performed_by_username, u.role as performed_by_role,
               al.table_name, al.record_id,
               CASE 
                   WHEN al.table_name = 'children' THEN c.child_id
                   WHEN al.table_name = 'cases' THEN cs.case_id
                   ELSE NULL
               END as record_identifier
        FROM activity_log al
        LEFT JOIN users u ON al.performed_by = u.id
        LEFT JOIN children c ON al.table_name = 'children' AND al.record_id = c.id
        LEFT JOIN cases cs ON al.table_name = 'cases' AND al.record_id = cs.id
        ORDER BY al.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentActivities = $stmt->fetchAll();
    
} catch (Exception $e) {
    // If activity_log doesn't exist, get recent activities from children and cases tables
    try {
        // Get recent child registrations
        $stmt = $pdo->prepare("
            SELECT c.child_id as record_identifier, 
                   CONCAT('Child Registered: ', c.name) as activity, 
                   u.username as performed_by_username, u.role as performed_by_role, 
                   c.created_at as activity_date
            FROM children c 
            LEFT JOIN users u ON c.created_by = u.id
            ORDER BY c.created_at DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $childActivities = $stmt->fetchAll();
        
        // Get recent case activities
        $stmt = $pdo->prepare("
            SELECT cs.case_id as record_identifier, 
                   CONCAT('Case ', cs.case_type, ': ', cs.description) as activity, 
                   u.username as performed_by_username, u.role as performed_by_role, 
                   cs.created_date as activity_date
            FROM cases cs 
            LEFT JOIN users u ON cs.created_by = u.id
            ORDER BY cs.created_date DESC 
            LIMIT 2
        ");
        $stmt->execute();
        $caseActivities = $stmt->fetchAll();
        
        // Combine and sort by date
        $recentActivities = array_merge($childActivities, $caseActivities);
        usort($recentActivities, function($a, $b) {
            return strtotime($b['activity_date']) - strtotime($a['activity_date']);
        });
        
        // Limit to 5 most recent
        $recentActivities = array_slice($recentActivities, 0, 5);
        
    } catch (Exception $e2) {
        $recentActivities = [];
    }
}

// Get schedule event type distribution for bar chart
$scheduleTypeData = [];
try {
    $stmt = $pdo->query("SELECT event_type, COUNT(*) as count FROM events WHERE status != 'Cancelled' AND is_active = 1 GROUP BY event_type");
    $typeResults = $stmt->fetchAll();
    
    foreach ($typeResults as $row) {
        $scheduleTypeData[$row['event_type']] = $row['count'];
    }
} catch (Exception $e) {
    $scheduleTypeData = [
        'home_visit' => 0,
        'meeting' => 0,
        'team_building' => 0,
        'staff_training' => 0,
        'financial' => 0,
        'orientation' => 0,
        'calamity_duty' => 0
    ];
}

// Get schedule status distribution for second chart
$scheduleStatusData = [];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM events WHERE is_active = 1 GROUP BY status");
    $statusResults = $stmt->fetchAll();
    
    foreach ($statusResults as $row) {
        $scheduleStatusData[$row['status']] = $row['count'];
    }
} catch (Exception $e) {
    $scheduleStatusData = [
        'Scheduled' => 0,
        'Completed' => 0,
        'Cancelled' => 0
    ];
}

// Get upcoming events count for stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status = 'Scheduled' AND is_active = 1");
    $upcomingEvents = $stmt->fetch()['count'];
} catch (Exception $e) {
    $upcomingEvents = 0;
}

// Get completed events count for stats
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE status = 'Completed' AND is_active = 1");
    $completedEvents = $stmt->fetch()['count'];
} catch (Exception $e) {
    $completedEvents = 0;
}

// Get foster status distribution for bar chart
$fosterStatusData = [];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM foster_parents GROUP BY status");
    $statusResults = $stmt->fetchAll();
    
    foreach ($statusResults as $row) {
        $fosterStatusData[$row['status']] = $row['count'];
    }
} catch (Exception $e) {
    $fosterStatusData = [
        'Pending' => 0,
        'Approved' => 0,
        'Active' => 0,
        'Rejected' => 0
    ];
}

// Get foster type distribution for second chart
$fosterTypeData = [];
try {
    $stmt = $pdo->query("SELECT adopter_type, COUNT(*) as count FROM foster_parents GROUP BY adopter_type");
    $typeResults = $stmt->fetchAll();
    
    foreach ($typeResults as $row) {
        $fosterTypeData[$row['adopter_type']] = $row['count'];
    }
} catch (Exception $e) {
    $fosterTypeData = [
        'Single Parents' => 0,
        'Married Couples' => 0,
        'Extended Family' => 0
    ];
}

// Get foster capacity distribution
$fosterCapacityData = [];
try {
    $capacityGroups = [
        '1-2 Children' => "capacity BETWEEN 1 AND 2",
        '3-4 Children' => "capacity BETWEEN 3 AND 4", 
        '5+ Children' => "capacity >= 5"
    ];
    
    foreach ($capacityGroups as $group => $condition) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM foster_parents WHERE $condition");
        $fosterCapacityData[$group] = $stmt->fetch()['count'];
    }
} catch (Exception $e) {
    $fosterCapacityData = [
        '1-2 Children' => 0,
        '3-4 Children' => 0,
        '5+ Children' => 0
    ];
}

// Get donation status distribution for bar chart
$donationStatusData = [];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM donations GROUP BY status");
    $statusResults = $stmt->fetchAll();
    
    foreach ($statusResults as $row) {
        $donationStatusData[$row['status']] = $row['count'];
    }
} catch (Exception $e) {
    $donationStatusData = [
        'Received' => 0,
        'Pending' => 0,
        'Processed' => 0
    ];
}

// Get donation type distribution for second chart
$donationTypeData = [];
try {
    $stmt = $pdo->query("SELECT donation_type, COUNT(*) as count FROM donations GROUP BY donation_type");
    $typeResults = $stmt->fetchAll();
    
    foreach ($typeResults as $row) {
        $donationTypeData[$row['donation_type']] = $row['count'];
    }
} catch (Exception $e) {
    $donationTypeData = [
        'Goods' => 0,
        'Services' => 0,
        'Other' => 0
    ];
}

// Get case status distribution for bar chart
$caseStatusData = [];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM cases GROUP BY status");
    $statusResults = $stmt->fetchAll();
    
    foreach ($statusResults as $row) {
        $caseStatusData[$row['status']] = $row['count'];
    }
} catch (Exception $e) {
    $caseStatusData = [
        'Open' => 0,
        'Under Investigation' => 0,
        'Court Action Pending' => 0,
        'Closed' => 0
    ];
}

// Get case type distribution for second chart
$caseTypeData = [];
try {
    $stmt = $pdo->query("SELECT case_type, COUNT(*) as count FROM cases GROUP BY case_type");
    $typeResults = $stmt->fetchAll();
    
    foreach ($typeResults as $row) {
        $caseTypeData[$row['case_type']] = $row['count'];
    }
} catch (Exception $e) {
    $caseTypeData = [
        'Physical Abuse' => 0,
        'Emotional Abuse' => 0,
        'Neglect' => 0,
        'Abandonment' => 0
    ];
}

// Get child status distribution for bar chart
$childStatusData = [];
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM children GROUP BY status");
    $statusResults = $stmt->fetchAll();
    
    foreach ($statusResults as $row) {
        $childStatusData[$row['status']] = $row['count'];
    }
} catch (Exception $e) {
    $childStatusData = [
        'In Care' => 0,
        'Adoptable' => 0,
        'Adopted' => 0,
        'Reintegrated' => 0
    ];
}

// Get age distribution for second chart
$ageDistributionData = [];
try {
    $ageGroups = [
        '0-5' => "age BETWEEN 0 AND 5",
        '6-10' => "age BETWEEN 6 AND 10", 
        '11-15' => "age BETWEEN 11 AND 15",
        '16+' => "age >= 16"
    ];
    
    foreach ($ageGroups as $group => $condition) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM children WHERE $condition");
        $ageDistributionData[$group] = $stmt->fetch()['count'];
    }
} catch (Exception $e) {
    $ageDistributionData = [
        '0-5' => 0,
        '6-10' => 0,
        '11-15' => 0,
        '16+' => 0
    ];
}

// Function to format user display with role
function formatUserDisplay($username, $role) {
    if (empty($username)) {
        return 'System';
    }
    
    $roleDisplay = match($role) {
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'Social Worker' => 'Social Worker',
        'Social Welfare Assistant' => 'Social Welfare Assistant',
        default => ucfirst($role)
    };
    
    return $username . ' (' . $roleDisplay . ')';
}

// Get monthly case trends for CURRENT YEAR only - CORRECTED
$monthlyCaseData = [];
$currentYear = date('Y');

try {
    // Create array for all months of current year
    $months = [];
    for ($month = 1; $month <= 12; $month++) {
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $months[] = "{$currentYear}-{$monthStr}";
    }
    
    // Initialize all months with 0
    foreach ($months as $month) {
        $monthlyCaseData[$month] = 0;
    }
    
    // Query to get case counts for current year only
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(created_date, '%Y-%m') as month, 
               COUNT(*) as count 
        FROM cases 
        WHERE YEAR(created_date) = ?
        GROUP BY DATE_FORMAT(created_date, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute([$currentYear]);
    $monthlyResults = $stmt->fetchAll();
    
    // Fill with actual data
    foreach ($monthlyResults as $row) {
        $monthlyCaseData[$row['month']] = (int)$row['count'];
    }
    
    // DEBUG: Add this to see what's happening
    $totalCasesInYear = array_sum($monthlyCaseData);
    error_log("=== CASE TRENDS 2025 DEBUG ===");
    error_log("Year: {$currentYear}");
    error_log("Total cases in {$currentYear}: {$totalCasesInYear}");
    error_log("Monthly breakdown: " . print_r($monthlyCaseData, true));
    
    // Get list of all case IDs and their dates for debugging
    $debugStmt = $pdo->prepare("
        SELECT case_id, created_date, status 
        FROM cases 
        WHERE YEAR(created_date) = ? 
        ORDER BY created_date DESC
    ");
    $debugStmt->execute([$currentYear]);
    $allCases = $debugStmt->fetchAll();
    
    error_log("All cases in {$currentYear}: " . count($allCases));
    foreach ($allCases as $case) {
        error_log("Case {$case['case_id']} - {$case['created_date']} - {$case['status']}");
    }
    
} catch (Exception $e) {
    error_log("Case trends 2025 error: " . $e->getMessage());
    
    // Fallback: Empty data for current year
    for ($month = 1; $month <= 12; $month++) {
        $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
        $monthlyCaseData["{$currentYear}-{$monthStr}"] = 0;
    }
}

// Get child gender distribution for pie chart
$genderData = [];
try {
    $stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM children GROUP BY gender");
    $genderResults = $stmt->fetchAll();
    
    foreach ($genderResults as $row) {
        $genderData[$row['gender']] = $row['count'];
    }
} catch (Exception $e) {
    $genderData = [
        'Male' => 12,
        'Female' => 8
    ];
}

// Get urgent vs normal cases for mixed chart
$priorityCaseData = [];
try {
    $stmt = $pdo->query("SELECT priority, COUNT(*) as count FROM cases GROUP BY priority");
    $priorityResults = $stmt->fetchAll();
    
    foreach ($priorityResults as $row) {
        $priorityCaseData[$row['priority']] = $row['count'];
    }
} catch (Exception $e) {
    $priorityCaseData = [
        'urgent' => 5,
        'normal' => 15
    ];
}
?>

<main class="main-content">
    <h1 class="page-title">Dashboard Overview</h1>
    
    <!-- Current User Status Display -->
    <div class="current-user-status">
        <div class="user-info-card">
            <div class="user-avatar">
                <?php echo strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></div>
                <div class="user-role">
                    <?php 
                    $roleDisplay = match($currentUser['role']) {
                        'super_admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'Social Worker' => 'Social Worker',
                        'Social Welfare Assistant' => 'Social Welfare Assistant',
                        default => ucfirst($currentUser['role'])
                    };
                    echo htmlspecialchars($roleDisplay); 
                    ?>
                </div>
                <div class="login-time">Logged in since: <?php echo date('M j, Y g:i A', $_SESSION['login_time'] ?? time()); ?></div>
            </div>
        </div>
    </div>

    <div class="confidentiality-alert">
        <p>Confidentiality Reminder: All child data is protected. Access is logged and monitored.</p>
    </div>

    <!-- Key Metrics Section -->
    <section class="key-metrics">
        <h2 class="section-title">Key Metrics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                
                <div class="stat-content">
                    <div class="stat-header">Active Cases</div>
                    <div class="stat-value"><?php echo $activeCases; ?></div>
                </div>
            </div>
            <div class="stat-card">
                
                <div class="stat-content">
                    <div class="stat-header">Children in Care</div>
                    <div class="stat-value"><?php echo $childrenInCare; ?></div>
                </div>
            </div>
            <div class="stat-card">
                
                <div class="stat-content">
                    <div class="stat-header">Upcoming Events</div>
                    <div class="stat-value"><?php echo $upcomingEvents; ?></div>
                </div>
            </div>
            <div class="stat-card">
            
                <div class="stat-content">
                    <div class="stat-header">Successful Adoptions</div>
                    <div class="stat-value"><?php echo $successfulAdoptions; ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mixed Charts Section -->
    <section class="mixed-charts-section">
        <h2 class="section-title">Data Overview</h2>
        <div class="charts-grid">
            <!-- Case Trends Over Time (Line Chart) -->
            <div class="chart-card full-width">
                <div class="chart-header">
                    <h3>Case Trends (<?php echo date('Y'); ?>)</h3>
                    <small>Updated: <?php echo date('M j, Y g:i A'); ?></small>
                </div>
                <div class="chart-container">
                    <canvas id="caseTrendsChart" width="800" height="300"></canvas>
                </div>
            </div>

            <!-- Child Status Distribution (Landscape Bar Chart) -->
            <div class="chart-card full-width">
                <div class="chart-header">
                    <h3>Child Status Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart" width="800" height="250"></canvas>
                </div>
            </div>

            <!-- Child Gender Distribution (Pie Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Child Gender Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="genderChart" width="400" height="250"></canvas>
                </div>
            </div>

            <!-- Case Priority Distribution (Doughnut Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Case Priority Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="priorityChart" width="400" height="250"></canvas>
                </div>
            </div>

            <!-- Age Distribution (Bar Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Age Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="ageChart" width="400" height="250"></canvas>
                </div>
            </div>

            <!-- Case Status Distribution (Bar Chart) -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Case Status Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="caseStatusChart" width="400" height="250"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Activities Section -->
    <section class="recent-activities">
        <div class="activities-header">
            <h2 class="section-title">Recent Activities</h2>
            <button class="view-all-btn" onclick="window.location.href='case-management.php'">View All</button>
        </div>
        
        <table class="activities-table">
            <thead>
                <tr>
                    <th>Record ID</th>
                    <th>Activity</th>
                    <th>Performed By</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentActivities as $activity): ?>
                <tr>
                    <td class="record-id">
                        <?php 
                        if (!empty($activity['record_identifier'])) {
                            echo htmlspecialchars($activity['record_identifier']);
                        } elseif (!empty($activity['child_id'])) {
                            echo htmlspecialchars($activity['child_id']);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($activity['activity'])) {
                            echo htmlspecialchars($activity['activity']);
                        } elseif (!empty($activity['description'])) {
                            echo htmlspecialchars($activity['description']);
                        } else {
                            echo 'Activity recorded';
                        }
                        ?>
                    </td>
                    <td>
                        <?php 
                        if (!empty($activity['performed_by_username'])) {
                            echo htmlspecialchars(formatUserDisplay(
                                $activity['performed_by_username'],
                                $activity['performed_by_role']
                            ));
                        } else {
                            echo 'System';
                        }
                        ?>
                    </td>
                    <td><?php echo formatDate($activity['activity_date']); ?></td>
                    <td><span class="status-badge status-completed">COMPLETED</span></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($recentActivities)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #888; padding: 40px;">
                        No recent activities found. Activities will appear here as you use the system.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<style>
/* Enhanced Dashboard Styles */
:root {
    --primary-color: #3b82f6;
    --secondary-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --dark-bg: #1a1a1a;
    --card-bg: #2a2a2a;
    --text-light: #ffffff;
    --text-muted: #9ca3af;
    --border-color: #3a3a3a;
}

.page-title {
    color: var(--text-light);
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 20px;
}

.dark-theme .section-title {
    color: var(--text-light);
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

.light-theme .section-title {
    color: #1e293b;
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--primary-color);
    padding-bottom: 10px;
}

/* Current User Status Styles */
.current-user-status {
    margin-bottom: 30px;
}

.user-info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    max-width: 400px;
}

.user-avatar {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    margin-right: 15px;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.user-details {
    flex: 1;
}

.user-name {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 4px;
}

.user-role {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 4px;
}

.login-time {
    font-size: 12px;
    opacity: 0.7;
}

.confidentiality-alert {
    background: #fff3cd;
    color: #856404;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 30px;
    border: 1px solid #ffeaa7;
}

/* Key Metrics Section */
.key-metrics {
    margin-bottom: 30px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}

.stat-card {
    background: var(--card-bg);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 4px solid var(--primary-color);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
}

.stat-icon {
    font-size: 32px;
    margin-right: 15px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(59, 130, 246, 0.1);
    border-radius: 50%;
}

.stat-content {
    flex: 1;
}

.stat-header {
    color: var(--text-muted);
    font-size: 14px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    color: var(--text-light);
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-change {
    font-size: 12px;
    font-weight: 500;
}

.stat-change.positive {
    color: var(--secondary-color);
}

.stat-change.negative {
    color: var(--danger-color);
}

.stat-change.neutral {
    color: var(--text-muted);
}

/* Charts Section */
.mixed-charts-section {
    margin-bottom: 30px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.dark-theme .chart-card {
    background: var(--card-bg);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-left: 4px solid var(--primary-color);
}

.light-theme .chart-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border-left: 4px solid var(--primary-color);
}

/* Chart Theme Variables */
:root {
  /* Light Theme */
  --chart-bg-light: #ffffff;
  --chart-text-light: #333333;
  --chart-grid-light: rgba(0, 0, 0, 0.1);
  --chart-tooltip-bg-light: rgba(0, 0, 0, 0.8);
  --chart-tooltip-text-light: #ffffff;
  
  /* Dark Theme */
  --chart-bg-dark: #2a2a2a;
  --chart-text-dark: #cccccc;
  --chart-grid-dark: rgba(255, 255, 255, 0.1);
  --chart-tooltip-bg-dark: rgba(0, 0, 0, 0.8);
  --chart-tooltip-text-dark: #ffffff;
}

/* Default (Dark Theme) */
.chart-container canvas {
  background-color: var(--chart-bg-dark);
  border-radius: 8px;
  padding: 10px;
}

/* Chart Text Colors for Dark Theme */
.chart-card .chart-header h3 {
  color: var(--text-light);
}

/* Light Theme Overrides */
body.light-theme .chart-container canvas {
  background-color: rgba(167, 163, 163, 0.1);
}

body.light-theme .chart-card .chart-header h3 {
  color: var(--chart-text-light);
}

/* Chart.js Specific Styling for Both Themes */
.chartjs-render-monitor {
  border-radius: 6px;
}

/* Tooltip Styling for Both Themes */
.chartjs-tooltip {
  background: var(--chart-tooltip-bg-dark) !important;
  color: var(--chart-tooltip-text-dark) !important;
  border-radius: 6px;
  padding: 8px 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

body.light-theme .chartjs-tooltip {
  background: var(--chart-tooltip-bg-light) !important;
  color: var(--chart-tooltip-text-light) !important;
}

/* Legend Styling */
.chartjs-legend {
  color: var(--chart-text-dark) !important;
}

body.light-theme .chartjs-legend {
  color: var(--chart-text-light) !important;
}

/* Grid Line Styling */
.chartjs-grid-line {
  stroke: var(--chart-grid-dark) !important;
}

body.light-theme .chartjs-grid-line {
  stroke: var(--chart-grid-light) !important;
}

/* Tick Text Styling */
.chartjs-tick-text {
  fill: var(--chart-text-dark) !important;
}

body.light-theme .chartjs-tick-text {
  fill: var(--chart-text-light) !important;
}

.chart-card.full-width {
    grid-column: 1 / -1;
}

.chart-header {
    margin-bottom: 15px;
}

.chart-header h3 {
    color: var(--text-light);
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.chart-container {
    position: relative;
    height: 250px;
}

.chart-card.full-width .chart-container {
    height: 250px;
}

/* Recent Activities Section */
.recent-activities {
    margin-bottom: 30px;
}

.activities-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.view-all-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: background 0.3s ease;
}

.view-all-btn:hover {
    background: #2563eb;
}

.activities-table {
    background: var(--card-bg);
    border-radius: 8px;
    overflow: hidden;
    width: 100%;
    border-collapse: collapse;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.light-theme .activities-table th,
.light-theme .activities-table td {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 16px;
    color: #1e293b;
}

.dark-theme .activities-table th,
.dark-theme .activities-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
    font-size: 16px;
    color:rgb(255, 255, 255);
}

.activities-table th {
    background: #1a1a1a;
    color: #b8c5ff;
    font-weight: 600;
}

.activities-table tr:hover {
    background: rgba(59, 130, 246, 0.05);
}

.light-theme .activities-table th{
    background: #2d5f8d;
    color: rgb(255, 255, 255);
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #3a3a3a;
    font-size: 15px;
}

.record-id {
    color: #2d5f8d !important;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-completed {
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

/* Formal Chart Styles */
.formal-chart {
    background: #ffffff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.formal-chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eaeaea;
}

.formal-chart-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.formal-chart-legend {
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.legend-color {
    width: 10px;
    height: 10px;
    border-radius: 2px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card {
        padding: 15px;
    }
    
    .user-info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .user-avatar {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .activities-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<script>
// Enhanced Chart JavaScript with Formal Styling
document.addEventListener('DOMContentLoaded', function() {
    initializeCaseTrendsChart(); // Initialize Case Trends first
    initializeMixedCharts();
    initializeCaseCharts();
    
    // Listen for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                // Reinitialize charts when theme changes
                setTimeout(() => {
                    initializeCaseTrendsChart();
                    initializeMixedCharts();
                    initializeCaseCharts();
                }, 100);
            }
        });
    });
    
    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
});

function getChartColors() {
    const isLightTheme = document.body.classList.contains('light-theme');
    
    return {
        textColor: isLightTheme ? '#333333' : '#cccccc',
        gridColor: isLightTheme ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)',
        tooltipBg: isLightTheme ? 'rgba(0, 0, 0, 0.8)' : 'rgba(0, 0, 0, 0.8)',
        tooltipText: '#ffffff'
    };
}

// Case Trends Chart - Separate Function
function initializeCaseTrendsChart() {
    const colors = getChartColors();
    
    // Destroy existing chart if it exists
    const existingChart = Chart.getChart('caseTrendsChart');
    if (existingChart) {
        existingChart.destroy();
    }

    const caseTrendsCtx = document.getElementById('caseTrendsChart').getContext('2d');

    // Prepare data for the chart
    const monthlyLabels = <?php echo json_encode(array_keys($monthlyCaseData)); ?>;
    const monthlyData = <?php echo json_encode(array_values($monthlyCaseData)); ?>;

    // Format month labels for better readability
    const formattedLabels = monthlyLabels.map(label => {
        const [year, month] = label.split('-');
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${monthNames[parseInt(month) - 1]} ${year}`;
    });

    // Calculate dynamic Y-axis max
    const maxCases = Math.max(...monthlyData);
    const suggestedMax = maxCases > 0 ? Math.ceil(maxCases * 1.2) : 10;
    const stepSize = Math.ceil(maxCases / 5) || 1;

    return new Chart(caseTrendsCtx, {
        type: 'line',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Cases Opened',
                data: monthlyData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                tension: 0.2,
                fill: true,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: document.body.classList.contains('light-theme') ? '#ffffff' : '#2a2a2a',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: 'rgb(59, 130, 246)',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: colors.textColor,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    titleFont: {
                        size: 12,
                        weight: '500'
                    },
                    bodyFont: {
                        size: 11
                    },
                    padding: 10,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        title: function(tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function(context) {
                            return `Cases: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: suggestedMax,
                    ticks: {
                        color: colors.textColor,
                        stepSize: stepSize,
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    },
                    grid: {
                        color: colors.gridColor,
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Number of Cases',
                        color: colors.textColor,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                x: {
                    ticks: {
                        color: colors.textColor,
                        font: {
                            size: 11
                        },
                        maxRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animations: {
                tension: {
                    duration: 1000,
                    easing: 'linear'
                }
            }
        }
    });
}

function initializeMixedCharts() {
    const colors = getChartColors();
    
    // Destroy existing charts if they exist
    const existingCharts = ['genderChart', 'priorityChart'];
    existingCharts.forEach(chartId => {
        const chart = Chart.getChart(chartId);
        if (chart) {
            chart.destroy();
        }
    });

    // Child Gender Distribution (Pie Chart) - Formal Style
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_keys($genderData)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($genderData)); ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff'] : 
                    ['rgb(54, 162, 235)', 'rgb(255, 99, 132)'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.textColor,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Case Priority Distribution (Doughnut Chart) - Formal Style
    const priorityCtx = document.getElementById('priorityChart').getContext('2d');
    const priorityChart = new Chart(priorityCtx, {
        type: 'doughnut',
        data: {
            labels: ['Urgent', 'Normal'],
            datasets: [{
                data: [
                    <?php echo $priorityCaseData['urgent'] ?? 0; ?>,
                    <?php echo $priorityCaseData['normal'] ?? 0; ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff'] : 
                    ['rgb(255, 99, 132)', 'rgb(54, 162, 235)'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.textColor,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function initializeCaseCharts() {
    const colors = getChartColors();
    
    // Destroy existing charts if they exist
    const existingCharts = ['statusChart', 'ageChart', 'caseStatusChart'];
    existingCharts.forEach(chartId => {
        const chart = Chart.getChart(chartId);
        if (chart) {
            chart.destroy();
        }
    });

    // Child Status Distribution Chart - Landscape Bar Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($childStatusData)); ?>,
            datasets: [{
                label: 'Number of Children',
                data: <?php echo json_encode(array_values($childStatusData)); ?>,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff', '#ffffff', '#ffffff'] : 
                    ['rgb(54, 162, 235)', 'rgb(75, 192, 192)', 'rgb(153, 102, 255)', 'rgb(255, 159, 64)'],
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', // This makes the bar chart horizontal (landscape)
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        color: colors.textColor,
                        stepSize: 1
                    },
                    grid: {
                        color: colors.gridColor
                    }
                },
                y: {
                    ticks: {
                        color: colors.textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Age Distribution Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($ageDistributionData)); ?>,
            datasets: [{
                label: 'Number of Children',
                data: <?php echo json_encode(array_values($ageDistributionData)); ?>,
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: document.body.classList.contains('light-theme') ? '#ffffff' : 'rgb(99, 102, 241)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: colors.textColor,
                        stepSize: 1
                    },
                    grid: {
                        color: colors.gridColor
                    }
                },
                x: {
                    ticks: {
                        color: colors.textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Case Status Distribution Chart
    const caseStatusCtx = document.getElementById('caseStatusChart').getContext('2d');
    const caseStatusChart = new Chart(caseStatusCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($caseStatusData)); ?>,
            datasets: [{
                label: 'Number of Cases',
                data: <?php echo json_encode(array_values($caseStatusData)); ?>,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff', '#ffffff', '#ffffff'] : 
                    ['rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(255, 159, 64)', 'rgb(153, 102, 255)'],
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: colors.tooltipBg,
                    titleColor: colors.tooltipText,
                    bodyColor: colors.tooltipText
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: colors.textColor,
                        stepSize: 1
                    },
                    grid: {
                        color: colors.gridColor
                    }
                },
                x: {
                    ticks: {
                        color: colors.textColor
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Add auto-refresh functionality
function setupAutoRefresh() {
    // Refresh the page every 2 minutes to get updated data
    setInterval(() => {
        console.log('Auto-refreshing dashboard data...');
        location.reload();
    }, 120000); // 120000 ms = 2 minutes
}

// Start auto-refresh when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupAutoRefresh();
});


</script>

<?php require_once 'includes/footer.php'; ?>