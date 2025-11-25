<?php
$pageTitle = 'Donation History - Orphanfare';
require_once 'includes/header.php';

// Get filter parameters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$donationType = $_GET['donation_type'] ?? '';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// SIMPLE DIRECT APPROACH - Let's just get all donations and display them
try {
    // First, let's see what's actually in the database
    $testStmt = $pdo->query("SELECT * FROM donations LIMIT 5");
    $testData = $testStmt->fetchAll();
    
    echo "<!-- DEBUG: Found " . count($testData) . " sample records -->";
    
    // Build simple query that will definitely work
    $query = "SELECT * FROM donations WHERE 1=1";
    $params = [];
    
    // Simple filters
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
    
    if (!empty($search)) {
        $query .= " AND (donor_name LIKE ? OR description LIKE ? OR donation_id LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $query .= " ORDER BY date_received DESC, created_at DESC";
    
    // Get total count
    $countQuery = str_replace("*", "COUNT(*)", $query);
    $countQuery = preg_replace('/ORDER BY.*$/', '', $countQuery);
    
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch()['total'] ?? 0;
    
    // Pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 20;
    $totalPages = ceil($totalCount / $perPage);
    $offset = ($page - 1) * $perPage;
    
    $query .= " LIMIT $perPage OFFSET $offset";
    
    // Get the donations
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donations = $stmt->fetchAll();
    
    echo "<!-- DEBUG: Query executed successfully -->";
    echo "<!-- DEBUG: Total donations found: " . $totalCount . " -->";
    echo "<!-- DEBUG: Donations to display: " . count($donations) . " -->";

} catch (Exception $e) {
    error_log("Donation history error: " . $e->getMessage());
    echo "<!-- DEBUG: Error: " . $e->getMessage() . " -->";
    $donations = [];
    $totalCount = 0;
    $totalPages = 1;
    $page = 1;
    $testData = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Donation History</h1>
        <button class="btn btn-primary" onclick="window.location.href='donation.php'">Back to Donation Management</button>
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
                        case 'donation_cancelled':
                            echo 'Donation cancelled successfully!';
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
                        case 'cancellation_failed':
                            echo 'Failed to cancel donation. Please try again.';
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

    <!-- Quick Stats -->
    <div class="donation-history" >
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="total-donation">
                <strong>Showing:</strong> <?php echo count($donations); ?> records
            </div>
            <?php if (!empty($testData)): ?>
            <div style="color: #28a745;">
                <strong>Database Connection:</strong> ✓ Working
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <h3 class="donations-filter">Filter Donations</h3>
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
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-input" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search donor, description, or ID...">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <button type="button" class="btn-cancel" onclick="resetFilters()">Reset Filters</button>
            </div>
        </form>
    </div>

    <!-- Donations Table -->
    <div class="table-section">
        <?php if (empty($donations)): ?>
            <div class="no-data">
                <h3>No Donations Found</h3>
                <p>No donations match your current filters.</p>
                
                <?php if (!empty($testData)): ?>
                    <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0; text-align: left;">
                        <h4>Debug Information:</h4>
                        <p><strong>Database has data but filters may be hiding it.</strong></p>
                        <p>Sample of what's in your database:</p>
                        <div style="background: white; padding: 10px; border-radius: 5px; margin-top: 10px;">
                            <?php foreach($testData as $index => $donation): ?>
                                <div style="margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                    <strong>Record <?php echo $index + 1; ?>:</strong><br>
                                    <?php foreach($donation as $key => $value): ?>
                                        <span style="color: #666;"><?php echo htmlspecialchars($key); ?>:</span> 
                                        <strong><?php echo htmlspecialchars($value ?? 'NULL'); ?></strong><br>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: #dc3545;">The donations table appears to be empty or there's a database connection issue.</p>
                <?php endif; ?>
                
                <button class="btn btn-primary" onclick="resetFilters()">View All Donations</button>
                <button class="btn" onclick="window.location.href='donation.php'" style="background: #6c757d; color: white; margin-left: 10px;">Add New Donation</button>
            </div>
        <?php else: ?>
            <!-- Success! We found donations to display -->
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
            </svg> Successfully loaded <?php echo count($donations); ?> donations from the database.
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Donor Information</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Additional Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                        <?php 
                        // Consistent status handling with case-insensitive comparison
                        $currentStatus = $donation['status'] ?? 'Received';
                        $donationId = $donation['donation_id'] ?? $donation['id'] ?? 'unknown';
                        $donorName = $donation['donor_name'] ?? $donation['donor'] ?? '';
                        $description = $donation['description'] ?? $donation['desc'] ?? '';
                        ?>
                        <tr data-donation-id="<?php echo $donationId; ?>">
                            <td class="donation-id">
                                <?php echo htmlspecialchars($donationId); ?>
                                <!-- Debug status -->
                                <div style="font-size: 12px; color: #3b82f6; margin-top: 2px;">
                                    Status: <?php echo htmlspecialchars($currentStatus); ?>
                                </div>
                            </td>
                            <td>
                                <div class="donor-cell">
                                    <strong style="font-size: 15px;">
                                        <?php echo htmlspecialchars($donorName ?: 'Unknown Donor'); ?>
                                    </strong>
                                    <?php if (!empty($donation['donor_contact']) || !empty($donation['contact'])): ?>
                                        <div class="donor-contact" style="font-size: 14px; margin-top: 2px;">
                                             <?php echo htmlspecialchars($donation['donor_contact'] ?? $donation['contact'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($donation['donor_email']) || !empty($donation['email'])): ?>
                                        <div class="donor-email" style="color: #3b82f6; font-size: 14px; margin-top: 2px;">
                                            <?php echo htmlspecialchars($donation['donor_email'] ?? $donation['email'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $type = $donation['donation_type'] ?? $donation['type'] ?? 'Other';
                                $typeClass = 'type-' . strtolower($type);
                                ?>
                                <span class="type-badge <?php echo $typeClass; ?>">
                                    <?php echo htmlspecialchars($type); ?>
                                </span>
                            </td>
                            <td class="description-cell">
                                <div class="description" style="font-weight: 500;">
                                    <?php echo htmlspecialchars($description ?: 'No description'); ?>
                                </div>
                                <?php if (!empty($donation['notes'])): ?>
                                    <div class="donation-notes">
                                        <small><?php echo htmlspecialchars($donation['notes']); ?></small>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="date-cell">
                                <?php 
                                $date = $donation['date_received'] ?? $donation['received_date'] ?? $donation['created_at'] ?? date('Y-m-d');
                                echo formatDate($date); 
                                ?>
                            </td>
                            <td>
                                <select class="status-select" data-donation-id="<?php echo $donationId; ?>" 
                                        onchange="updateDonationStatus('<?php echo $donationId; ?>', this.value)">
                                    <option value="Received" <?php echo strtolower($currentStatus) === 'received' ? 'selected' : ''; ?>>Received</option>
                                    <option value="Completed" <?php echo strtolower($currentStatus) === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </td>
                            <td>
                                <?php
                                // Load and display custom field values
                                if ($fieldManager) {
                                    $customValues = $fieldManager->getFieldValues($donationId, 'donations');
                                    if (!empty($customValues)) {
                                        foreach ($customValues as $fieldName => $value) {
                                            if (!empty($value)) {
                                                echo '<div class="custom-field-value">';
                                                echo '<small><strong>' . htmlspecialchars($fieldName) . ':</strong> ' . htmlspecialchars($value) . '</small>';
                                                echo '</div>';
                                            }
                                        }
                                    } else {
                                        echo '<span class="no-custom-fields">-</span>';
                                    }
                                }
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons-small cancel-button-container">
                                    <button class="btn-cancel-small" 
                                            onclick="cancelDonation('<?php echo $donationId; ?>', 
                                            '<?php echo htmlspecialchars($donorName); ?>', 
                                            '<?php echo htmlspecialchars($description); ?>')"
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

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="page-link">First</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">Previous</a>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++): 
                ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">Next</a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>" class="page-link">Last</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<!-- Cancel Donation Confirmation Modal -->
<div class="modal-overlay" id="cancelDonationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cancel Donation</h3>
            <button class="modal-close" onclick="hideCancelDonationModal()">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel this donation? This action cannot be undone.</p>
            <div class="cancel-donation-info" id="cancelDonationInfo"></div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="hideCancelDonationModal()">No, Keep Donation</button>
                <button type="button" class="btn-submit btn-danger" onclick="confirmCancelDonation()">Yes, Cancel Donation</button>
            </div>
        </div>
    </div>
</div>

<style>
.light-theme .donation-history {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 8px; 
    margin-bottom: 20px;
}

.dark-theme .donation-history {
    background: #2a2a2a;
    padding: 15px;
    border-radius: 8px; 
    margin-bottom: 20px;
}

.light-theme .total-donation {
    font-size: 16px;
    color: #0E7490;
}

/* Notification Styles */
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

.light-theme .donor-cell {
    color: #1e293b;
}

.light-theme .donor-contact {
    color: #1e293b;
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

/* Rest of existing styles... */
.dark-theme .filters-section {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.light-theme .filters-section {
    background: #f1f1f1;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
}

.light-theme .description {
    color: black;
}

.light-theme .date-cell {
    color: #1e293b;
    padding: 12px 16px;
    font-size: 14px;
}

.filters-section h3 {
    color: #ffffff;
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
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #3a3a3a;
}

.result-count {
    color: #0E7490;
    font-size: 14px;
    font-weight: 500;
}

.dark-theme .table-section {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
}

.light-theme .table-section {
    border-radius: 12px;
    padding: 24px;
}

.no-data {
    text-align: center;
    padding: 60px 20px;
    color: #888;
}

.no-data h3 {
    color: #ffffff;
    margin-bottom: 12px;
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
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.light-theme .data-table th {
    background: #2d5f8d;
    color: #b8c5ff;
    font-weight: 600;
    padding: 12px 16px;
    text-align: left;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table td {
    padding: 12px 16px;
    font-size: 14px;
}

.data-table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.donation-id {
    color: #1e293b;
    font-family: monospace;
    font-size: 12px;
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
}

.type-services {
    background: #17a2b8;
    color: white;
}

.light-theme .donations-filter{
    color: black;
}

.type-other {
    background: #6c757d;
    color: white;
}

.description-cell {
    max-width: 250px;
}

.donation-notes {
    color: #888;
    font-size: 14px;
    margin-top: 4px;
    font-style: italic;
}

.date-cell {
    color: #b8c5ff;
    font-size: 13px;
}

.action-buttons-small {
    display: flex;
    gap: 8px;
}

.no-action {
    color: #888;
    font-style: italic;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #3a3a3a;
}

.page-link {
    padding: 8px 12px;
    background: #333;
    color: #b8c5ff;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.2s;
}

.page-link:hover {
    background: #3b82f6;
    color: white;
}

.page-link.active {
    background: #3b82f6;
    color: white;
    font-weight: 600;
}

.btn-danger {
    background: #dc3545 !important;
}

.btn-danger:hover {
    background: #c82333 !important;
}

.cancel-donation-info {
    background: #333;
    padding: 12px;
    border-radius: 6px;
    margin: 16px 0;
    border-left: 4px solid #dc3545;
}

.cancel-donation-info h4 {
    color: #fff;
    margin-bottom: 8px;
}

.cancel-donation-details {
    color: #b8c5ff;
    font-size: 14px;
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
    max-width: 120px;
}

.status-select:focus {
    outline: none;
    border-color: #3b82f6;
}

.action-buttons-small {
    display: flex;
    gap: 8px;
    align-items: center;
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
</style>

<script>
let currentDonationId = null;

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

function resetFilters() {
    window.location.href = 'donation-history.php';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const cancelDonationModal = document.getElementById('cancelDonationModal');
    
    if (event.target === cancelDonationModal) {
        hideCancelDonationModal();
    }
});

function updateDonationStatus(donationId, newStatus) {
    if (!donationId || !newStatus) return;
    
    const selectElement = document.querySelector(`select[data-donation-id="${donationId}"]`);
    const originalStatus = selectElement.value;
    
    selectElement.disabled = true;
    
    const formData = new URLSearchParams();
    formData.append('action', 'update_status');
    formData.append('donation_id', donationId);
    formData.append('status', newStatus);
    
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
        selectElement.disabled = false;
        
        if (data.success) {
            showNotification('Status updated successfully!', 'success');
        } else {
            showNotification('Failed: ' + (data.message || 'Unknown error'), 'error');
            selectElement.value = originalStatus;
        }
    })
    .catch(error => {
        selectElement.disabled = false;
        selectElement.value = originalStatus;
        showNotification('Error: ' + error.message, 'error');
    });
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