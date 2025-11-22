[file name]: donation-reports.php
[file content begin]
<?php
$pageTitle = 'Donation Reports - Orphanfare';
require_once 'includes/header.php';

// Get filter parameters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month
$donationType = $_GET['donation_type'] ?? '';
$status = $_GET['status'] ?? '';

// Build query for donations
$query = "
    SELECT donation_id, donor_name, donor_contact, donor_email, 
           donation_type, description, date_received, status, notes,
           created_at
    FROM donations 
    WHERE 1=1
";

$params = [];

// Apply filters
if (!empty($startDate)) {
    $query .= " AND date_received >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $query .= " AND date_received <= ?";
    $params[] = $endDate;
}

if (!empty($donationType) && $donationType !== 'all') {
    $query .= " AND donation_type = ?";
    $params[] = $donationType;
}

if (!empty($status) && $status !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY date_received DESC, created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donations = $stmt->fetchAll();

    // Calculate statistics
    $totalDonations = count($donations);
    $typeCounts = ['Goods' => 0, 'Services' => 0, 'Other' => 0];
    $statusCounts = ['Received' => 0, 'Completed' => 0];
    
    foreach ($donations as $donation) {
        if (isset($typeCounts[$donation['donation_type']])) {
            $typeCounts[$donation['donation_type']]++;
        }
        if (isset($statusCounts[$donation['status']])) {
            $statusCounts[$donation['status']]++;
        }
    }

    // Get top donors
    $stmt = $pdo->prepare("
        SELECT donor_name, donor_email, COUNT(*) as donation_count
        FROM donations 
        WHERE date_received BETWEEN ? AND ?
        GROUP BY donor_name, donor_email 
        ORDER BY donation_count DESC 
        LIMIT 10
    ");
    $stmt->execute([$startDate, $endDate]);
    $topDonors = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Donation reports error: " . $e->getMessage());
    $donations = [];
    $totalDonations = 0;
    $typeCounts = ['Goods' => 0, 'Services' => 0, 'Other' => 0];
    $statusCounts = ['Received' => 0, 'Completed' => 0];
    $topDonors = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Donation Reports</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.location.href='donation.php'">Back to Donation Management</button>
            <button class="btn btn-primary" onclick="printReport()">Print Report</button>
            <button class="btn" onclick="exportToExcel()" style="background: #28a745; color: white;">Export to Excel</button>
        </div>
    </div>

    <!-- Success/Error Notifications -->
    <?php if (isset($_GET['success'])): ?>
        <div class="notification success show">
            <div class="notification-icon">✓</div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['success']) {
                        case 'status_updated':
                            echo 'Donation status updated successfully!';
                            break;
                        default:
                            echo 'Operation completed successfully!';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['error']) {
                        case 'permission_denied':
                            echo 'You do not have permission to perform this action.';
                            break;
                        case 'update_failed':
                            echo 'Failed to update donation status. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="filters-section">
        <h3>Filter Reports</h3>
        <form method="GET" class="filters-form">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-input" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-input" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Donation Type</label>
                    <select name="donation_type" class="form-select">
                        <option value="all">All Types</option>
                        <option value="Goods" <?php echo $donationType === 'Goods' ? 'selected' : ''; ?>>Goods</option>
                        <option value="Services" <?php echo $donationType === 'Services' ? 'selected' : ''; ?>>Services</option>
                        <option value="Other" <?php echo $donationType === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all">All Status</option>
                        <option value="Received" <?php echo $status === 'Received' ? 'selected' : ''; ?>>Received</option>
                        <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" class="btn-cancel" onclick="resetFilters()">Reset Filters</button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($totalDonations); ?></div>
            <div class="stat-header">Total Donations</div>
            <div class="stat-period">
                <?php echo date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate)); ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($typeCounts['Goods']); ?></div>
            <div class="stat-header">Goods Donations</div>
            <div class="stat-period">Physical items received</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($typeCounts['Services']); ?></div>
            <div class="stat-header">Service Donations</div>
            <div class="stat-period">Volunteer services</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars(count($topDonors)); ?></div>
            <div class="stat-header">Active Donors</div>
            <div class="stat-period">In Selected Period</div>
        </div>
    </div>

    <!-- Charts and Visualizations -->
    <div class="charts-grid">
        <!-- Donation Types Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Donations by Type</h3>
            </div>
            <div class="chart-container">
                <canvas id="typeChart" width="400" height="250"></canvas>
            </div>
            <div class="chart-data">
                <?php foreach ($typeCounts as $type => $count): ?>
                <div class="data-item">
                    <span class="data-label"><?php echo htmlspecialchars($type); ?>:</span>
                    <span class="data-value"><?php echo htmlspecialchars($count); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Status Distribution Chart -->
        <div class="chart-card">
            <div class="chart-header">
                <h3>Donations by Status</h3>
            </div>
            <div class="chart-container">
                <canvas id="statusChart" width="400" height="250"></canvas>
            </div>
            <div class="chart-data">
                <?php foreach ($statusCounts as $statusItem => $count): ?>
                <div class="data-item">
                    <span class="data-label"><?php echo htmlspecialchars($statusItem); ?>:</span>
                    <span class="data-value"><?php echo htmlspecialchars($count); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Top Donors -->
    <div class="report-section">
        <div class="section-header">
            <h3>Top Donors</h3>
            <span class="section-subtitle">Period: <?php echo date('M j, Y', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate)); ?></span>
        </div>
        <div class="section-content">
            <?php if (empty($topDonors)): ?>
                <p style="color: #888; text-align: center; padding: 20px;">No donors found in selected period.</p>
            <?php else: ?>
                <div class="donors-grid">
                    <?php foreach ($topDonors as $donor): ?>
                    <div class="donor-card">
                        <div class="donor-rank">#<?php echo array_search($donor, $topDonors) + 1; ?></div>
                        <div class="donor-info">
                            <h4><?php echo htmlspecialchars($donor['donor_name']); ?></h4>
                            <?php if (!empty($donor['donor_email'])): ?>
                                <div class="donor-email"><?php echo htmlspecialchars($donor['donor_email']); ?></div>
                            <?php endif; ?>
                            <div class="donor-stats">
                                <span class="stat"><?php echo htmlspecialchars($donor['donation_count']); ?> donations</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Detailed Donations Table -->
    <div class="report-section">
        <div class="section-header">
            <h3>Detailed Donations</h3>
            <span class="section-subtitle"><?php echo htmlspecialchars($totalDonations); ?> records found</span>
        </div>
        <div class="section-content">
            <?php if (empty($donations)): ?>
                <p style="color: #888; text-align: center; padding: 40px;">No donations found matching your criteria.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donor</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Custom Fields</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td class="donation-id"><?php echo htmlspecialchars($donation['donation_id']); ?></td>
                                <td>
                                    <div class="donor-cell">
                                        <strong><?php echo htmlspecialchars($donation['donor_name']); ?></strong>
                                        <?php if (!empty($donation['donor_contact'])): ?>
                                            <div class="donor-contact"><?php echo htmlspecialchars($donation['donor_contact']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="type-badge type-<?php echo strtolower($donation['donation_type']); ?>">
                                        <?php echo htmlspecialchars($donation['donation_type']); ?>
                                    </span>
                                </td>
                                <td class="description-cell"><?php echo htmlspecialchars($donation['description']); ?></td>
                                <td class="date-cell"><?php echo formatDate($donation['date_received']); ?></td>
                                <td>
                                    <select class="status-select" data-donation-id="<?php echo $donation['donation_id']; ?>" onchange="updateDonationStatus('<?php echo $donation['donation_id']; ?>', this.value)">
                                        <option value="Received" <?php echo $donation['status'] === 'Received' ? 'selected' : ''; ?>>Received</option>
                                        <option value="Completed" <?php echo $donation['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </td>
                                <td class="custom-fields-cell">
                                    <?php
                                    if ($fieldManager) {
                                        $customValues = $fieldManager->getFieldValues($donation['donation_id'], 'donations');
                                        if (!empty($customValues)) {
                                            echo '<div class="custom-fields-preview">';
                                            foreach (array_slice($customValues, 0, 2) as $fieldName => $value) {
                                                if (!empty($value)) {
                                                    echo '<span class="custom-field-badge">' . htmlspecialchars($value) . '</span>';
                                                }
                                            }
                                            if (count($customValues) > 2) {
                                                echo '<span class="more-fields">+' . (count($customValues) - 2) . ' more</span>';
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '-';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons-small">
                                        <button class="btn-cancel-small" 
                                                onclick="cancelDonation('<?php echo $donation['donation_id']; ?>', 
                                                '<?php echo htmlspecialchars($donation['donor_name']); ?>', 
                                                '<?php echo htmlspecialchars($donation['description']); ?>')"
                                                style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 500;">
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Report Summary -->
    <div class="report-summary">
        <h3>Report Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-label">Report Period:</span>
                <span class="summary-value"><?php echo date('F j, Y', strtotime($startDate)) . ' to ' . date('F j, Y', strtotime($endDate)); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Generated On:</span>
                <span class="summary-value"><?php echo date('F j, Y \a\t g:i A'); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Generated By:</span>
                <span class="summary-value"><?php echo htmlspecialchars($currentUser['username'] ?? 'Admin User'); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Records:</span>
                <span class="summary-value"><?php echo htmlspecialchars($totalDonations); ?> donations</span>
            </div>
        </div>
    </div>
</main>

<!-- Include Chart.js for charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* Chart Card Styles - Matching Dashboard */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin: 30px 0;
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

.chart-header {
    margin-bottom: 15px;
}

.dark-theme .chart-header h3 {
    color: var(--text-light);
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.light-theme .chart-header h3 {
    color: var(--chart-text-light);
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.chart-container {
    position: relative;
    height: 250px;
    margin-bottom: 15px;
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

/* Light Theme Overrides */
body.light-theme .chart-container canvas {
  background-color: rgba(167, 163, 163, 0.1);
}

/* Chart Data Styles */
.chart-data {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.data-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #3a3a3a;
}

.dark-theme .data-label {
    color: #b8c5ff;
}

.light-theme .data-label {
    color: #18338c;
}

.dark-theme .data-value {
    color: #ffffff;
    font-weight: 600;
}

.light-theme .data-value {
    color: black;
    font-weight: 600;
}   

/* Rest of existing styles remain the same... */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 400px;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.notification.error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.notification.warning {
    border-left-color: #ffc107;
    background: #fff3cd;
    color: #856404;
}

.notification.info {
    border-left-color: #17a2b8;
    background: #d1ecf1;
    color: #0c5460;
}

.notification-icon {
    font-size: 20px;
    font-weight: bold;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 14px;
    opacity: 0.9;
}

.notification-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-close:hover {
    opacity: 1;
}

.custom-field-value {
    margin-bottom: 2px;
}

.custom-field-value small {
    color: #888;
    font-size: 11px;
}

.no-custom-fields {
    color: #888;
    font-style: italic;
}

.custom-fields-preview {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.custom-field-badge {
    background: #333;
    color: #b8c5ff;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 10px;
    white-space: nowrap;
}

.more-fields {
    color: #888;
    font-size: 10px;
    font-style: italic;
}

.form-section .custom-field {
    margin-bottom: 15px;
}

.custom-field .form-label {
    color: #b8c5ff;
    font-weight: 600;
    margin-bottom: 5px;
}

.custom-field .help-text {
    color: #888;
    font-size: 12px;
    font-style: italic;
    margin-top: 4px;
}

.dark-theme .filters-section {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.light-theme .filters-section {
    
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.dark-theme .filters-section h3 {
    color: #ffffff;
    margin-bottom: 16px;
    font-size: 18px;
}

.light-theme .filters-section h3 {
    color: #1e293b;
    margin-bottom: 16px;
    font-size: 18px;
}

.filters-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.filter-actions {
    display: flex;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid #3a3a3a;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.dark-theme .report-section {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.light-theme .report-section {
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.dark-theme .section-header h3 {
    color: #ffffff;
    font-size: 18px;
}

.light-theme .section-header h3 {
    color: black;
    font-size: 18px;
}

.light-theme .section-subtitle {
    color: #b8c5ff;
    font-size: 14px;
}

.light-theme .section-subtitle {
    color: black;
    font-size: 14px;
}

.donors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
}

.dark-theme .donor-card {
    background: #333;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.2s;
}

.light-theme .donor-card {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: transform 0.2s;
}

.dark-theme .donor-card:hover {
    transform: translateY(-2px);
    background: #3a3a3a;
}

.light-theme .donor-card:hover {
    transform: translateY(-2px);
    background:rgb(162, 229, 208);
}

.donor-rank {
    background: #3b82f6;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.dark-theme .donor-info h4 {
    color: #ffffff;
    margin-bottom: 4px;
    font-size: 15px;
}

.light-theme .donor-info h4 {
    color: #1a2744;
    margin-bottom: 4px;
    font-size: 15px;
}

.dark-theme .donor-email {
    color: #b8c5ff;
    font-size: 13px;
    margin-bottom: 6px;
}

.light-theme .donor-email {
    color: #0E7490;
    font-size: 13px;
    margin-bottom: 6px;
}

.donor-stats {
    display: flex;
    gap: 12px;
}

.dark-theme .donor-stats .stat {
    color: #888;
    font-size: 11px;
    background: #2a2a2a;
    padding: 2px 6px;
    border-radius: 12px;
}

.light-theme .donor-stats .stat {
    color: #ffffff;
    font-size: 11px;
    background: #2a2a2a;
    padding: 2px 6px;
    border-radius: 12px;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: #333;
    border-radius: 8px;
    overflow: hidden;
}

.dark-theme .data-table th {
    background: #404040;
    color: #b8c5ff;
    font-weight: 600;
    padding: 12px 16px;
    text-align: left;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.light-theme .data-table th {
    background: #2d5f8d;
    color: rgb(255, 255, 255);
    font-weight: 600;
    padding: 12px 16px;
    text-align: left;
    font-size: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dark-theme .data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #3a3a3a;
    font-size: 15px;
}

.light-theme .data-table td {
    padding: 12px 16px;
    font-size: 14px;
    color: black ;
}

.data-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.dark-theme .donation-id {
    color: #3b82f6;
    font-family: monospace;
    font-size: 12px;
}

.light-theme .donation-id {
    color: #059669;
    font-family: monospace;
    font-size: 12px;
}

.donor-cell strong {
    color: #1e293b; 
    display: block;
    margin-bottom: 2px;
    font-size: 15px;
}

.light-theme .donor-contact {
    color: #1e293b;
    font-size: 15px;
}

.type-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.type-money {
    background: #28a745;
    color: white;
}

.type-goods {
    background: #ffc107;
    color: #000;
    font-size: 13px;
}

.type-other {
    background: #ef9a00;
    color: #000;
}
.type-services {
    background: #17a2b8;
    color: white;
}

.amount-cell {
    font-weight: 600;
    color: #28a745;
}

.no-amount {
    color: #888;
    font-style: italic;
}

.description-cell {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.date-cell {
    color: #b8c5ff;
    font-size: 13px;
}

.dark-theme .report-summary {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.light-theme .report-summary {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    padding: 20px;
    margin-top: 24px;
}

.dark-theme .report-summary h3 {
    color: #ffffff;
    margin-bottom: 16px;
    font-size: 16px;
}

.light-theme .report-summary h3 {
    color: black;
    margin-bottom: 16px;
    font-size: 16px;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 12px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #3a3a3a;
}

.dark-theme .summary-label {
    color: #b8c5ff;
    font-size: 14px;
}

.light-theme .summary-label {
    color: #0E7490;
    font-size: 14px;
}

.dark-theme .summary-value {
    color: #ffffff;
    font-weight: 500;
}

.light-theme .summary-value {
    color: black;
    font-weight: 500;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .header-actions {
        flex-direction: column;
    }
    
    .donors-grid {
        grid-template-columns: 1fr;
    }
    
    .table-container {
        font-size: 12px;
    }
    
    .data-table th,
    .data-table td {
        padding: 8px 12px;
    }
}

.dark-theme .status-select {
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #3a3a3a;
    background: #2a2a2a;
    color: #ffffff;
    font-size: 12px;
    cursor: pointer;
    width: 100%;
    max-width: 120px;
}

.light-theme .status-select {
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #3a3a3a;
    
    color: black;
    font-size: 12px;
    cursor: pointer;
    width: 100%;
    max-width: 120px;
}

.status-select:focus {
    outline: none;
    border-color: #3b82f6;
}
</style>

<script>
// Get chart colors based on theme
function getChartColors() {
    const isLightTheme = document.body.classList.contains('light-theme');
    
    return {
        textColor: isLightTheme ? '#333333' : '#cccccc',
        gridColor: isLightTheme ? 'rgba(0, 0, 0, 0.1)' : 'rgba(255, 255, 255, 0.1)',
        tooltipBg: isLightTheme ? 'rgba(0, 0, 0, 0.8)' : 'rgba(0, 0, 0, 0.8)',
        tooltipText: '#ffffff'
    };
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    initializeDonationCharts();
    
    // Listen for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                // Reinitialize charts when theme changes
                setTimeout(() => {
                    initializeDonationCharts();
                }, 100);
            }
        });
    });
    
    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });
});

function initializeDonationCharts() {
    const colors = getChartColors();
    
    // Destroy existing charts if they exist
    const existingCharts = ['typeChart', 'statusChart'];
    existingCharts.forEach(chartId => {
        const chart = Chart.getChart(chartId);
        if (chart) {
            chart.destroy();
        }
    });

    // Donation Types Chart (Pie Chart) - Formal Style
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: ['Goods', 'Services', 'Other'],
            datasets: [{
                data: [
                    <?php echo $typeCounts['Goods']; ?>,
                    <?php echo $typeCounts['Services']; ?>,
                    <?php echo $typeCounts['Other']; ?>
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.8)',    // Goods - Yellow
                    'rgba(23, 162, 184, 0.8)',   // Services - Blue
                    'rgba(108, 117, 125, 0.8)'   // Other - Gray
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff', '#ffffff'] : 
                    ['rgb(255, 193, 7)', 'rgb(23, 162, 184)', 'rgb(108, 117, 125)'],
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

    // Status Distribution Chart (Pie Chart) - Formal Style
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Received', 'Completed'],
            datasets: [{
                data: [
                    <?php echo $statusCounts['Received']; ?>,
                    <?php echo $statusCounts['Completed']; ?>
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',    // Received - Green
                    'rgba(23, 162, 184, 0.8)'    // Completed - Blue
                ],
                borderColor: document.body.classList.contains('light-theme') ? 
                    ['#ffffff', '#ffffff'] : 
                    ['rgb(40, 167, 69)', 'rgb(23, 162, 184)'],
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

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.innerHTML = `
        <div class="notification-icon">${type === 'success' ? '✓' : '⚠'}</div>
        <div class="notification-content">
            <div class="notification-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function printReport() {
    window.print();
}

function exportToExcel() {
    // Simple CSV export
    const table = document.querySelector('.data-table');
    if (!table) {
        showNotification('No data to export', 'error');
        return;
    }

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Clean the text content
            let text = cols[j].innerText.replace(/,/g, '').replace(/\n/g, ' ');
            row.push(text);
        }
        
        csv.push(row.join(','));
    }

    // Download CSV file
    const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `donation-report-${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function resetFilters() {
    window.location.href = 'donation-reports.php';
}

function updateDonationStatus(donationId, newStatus) {
    if (!donationId || !newStatus) return;
    
    fetch('process-donation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_status&donation_id=${donationId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status updated successfully!', 'success');
            // For reports page, we might want to reload to update charts
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification('Failed to update status', 'error');
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating status', 'error');
        window.location.reload();
    });
}

let currentDonationId = null;

function showCancelDonationModal(donationId, donorName, description) {
    currentDonationId = donationId;
    
    const infoDiv = document.getElementById('cancelDonationInfo');
    infoDiv.innerHTML = `
        <h4>Donation Details</h4>
        <div class="cancel-donation-details">
            <strong>Donor:</strong> ${donorName}<br>
            <strong>Description:</strong> ${description}<br>
            <strong>Donation ID:</strong> ${donationId}
        </div>
    `;
    
    document.getElementById('cancelDonationModal').classList.add('active');
}

function hideCancelDonationModal() {
    document.getElementById('cancelDonationModal').classList.remove('active');
    currentDonationId = null;
}

function cancelDonation(donationId, donorName, description) {
    showCancelDonationModal(donationId, donorName, description);
}

function confirmCancelDonation() {
    if (!currentDonationId) return;
    
    const formData = new URLSearchParams();
    formData.append('action', 'cancel_donation');
    formData.append('donation_id', currentDonationId);
    
    fetch('ajax-process-donation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData
    })
    .then(response => response.text().then(text => {
        try {
            return JSON.parse(text);
        } catch (e) {
            throw new Error('Invalid JSON response');
        }
    }))
    .then(data => {
        if (data.success) {
            showNotification('Donation cancelled successfully!', 'success');
            hideCancelDonationModal();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification('Failed to cancel: ' + (data.message || 'Unknown error'), 'error');
            hideCancelDonationModal();
        }
    })
    .catch(error => {
        showNotification('Error: ' + error.message, 'error');
        hideCancelDonationModal();
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
[file content end]