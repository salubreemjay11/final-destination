<?php
$pageTitle = 'Donation Management - Orphanfare';
require_once 'includes/header.php';

// Check if user has view permission for donation management
if (!$permissionManager->hasPermission('donation', 'view')) {
    header('Location: access-denied.php');
    exit();
}

// Load Custom Field Manager
$fieldManager = null;
$donationCustomFields = [];

try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
    } elseif (file_exists('includes/CustomFieldManager.php')) {
        require_once 'includes/CustomFieldManager.php';
    }
    
    if (class_exists('CustomFieldManager')) {
        $fieldManager = new CustomFieldManager($pdo);
        $donationCustomFields = $fieldManager->getModuleFields('donations');
    }
} catch (Exception $e) {
    error_log("Custom Field Manager Error: " . $e->getMessage());
}

// Get donation statistics
try {
    // Total donations count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_donations FROM donations WHERE status = 'Received'");
    $stmt->execute();
    $totalDonations = $stmt->fetch()['total_donations'] ?? 0;

    // Completed donations count
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_donations FROM donations WHERE status = 'Completed'");
    $stmt->execute();
    $completedDonations = $stmt->fetch()['completed_donations'] ?? 0;

    // Recent donations
    $stmt = $pdo->prepare("
        SELECT donation_id, donor_name, donor_contact, donor_email, 
               donation_type, description, date_received, status, notes 
        FROM donations 
        ORDER BY date_received DESC, created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recentDonations = $stmt->fetchAll();

    // Recent donors (unique)
    $stmt = $pdo->prepare("
        SELECT DISTINCT donor_name, donor_email, donor_contact, 
               COUNT(*) as donation_count,
               MAX(date_received) as last_donation
        FROM donations 
        GROUP BY donor_name, donor_email, donor_contact
        ORDER BY last_donation DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentDonors = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Donation page error: " . $e->getMessage());
    $totalDonations = 0;
    $completedDonations = 0;
    $recentDonations = [];
    $recentDonors = [];
}

// Check permissions for display
$canCreate = $permissionManager->hasPermission('donation', 'create');
$canEdit = $permissionManager->hasPermission('donation', 'edit');
$canDelete = $permissionManager->hasPermission('donation', 'delete');
?>

<main class="main-content">
    <h1 class="page-title">Donation Management
        <?php if (!$canEdit): ?>
            <span class="status-badge status-mild" style="font-size: 14px; margin-left: 10px;">Read-Only</span>
        <?php endif; ?>
    </h1>
   

    <!-- Success/Error Notifications -->
    <?php if (isset($_GET['success'])): ?>
        <div class="notification success show">
            <div class="notification-icon">✓</div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['success']) {
                        case 'donation_added':
                            echo 'Donation recorded successfully!';
                            break;
                        case 'donation_updated':
                            echo 'Donation updated successfully!';
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
                        case 'donation_failed':
                            echo 'Failed to record donation. Please try again.';
                            break;
                        case 'invalid_data':
                            echo 'Invalid data provided. Please check your inputs.';
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

    <!-- Show read-only banner if no edit permission -->
    <?php if (!$canEdit): ?>
    <div class="read-only-banner">
        <strong>🔒 Read-Only Mode:</strong> You have view-only access to donation management. You cannot perform any actions.
    </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <!-- Record New Donation Button - Only show if user has create permission -->
        <?php if ($canCreate): ?>
            <button class="btn btn-primary" onclick="showRecordDonationModal()">Record New Donation</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to record donations">Record New Donation</button>
        <?php endif; ?>

        <!-- View Reports Button - Only show if user has view permission -->
        <?php if ($permissionManager->hasPermission('donation', 'view')): ?>
            <button class="btn btn-primary" onclick="window.location.href='donation-reports.php'">View Reports</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to view reports">View Reports</button>
        <?php endif; ?>

        <!-- View All Donations Button - Only show if user has view permission -->
        <?php if ($permissionManager->hasPermission('donation', 'view')): ?>
            <button class="btn btn-primary" onclick="window.location.href='donation-history.php'">View All Donations</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to view donations">View All Donations</button>
        <?php endif; ?>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Donors -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">Recent Donors</h3>
            </div>
            <div class="section-content">
                <?php if (empty($recentDonors)): ?>
                    <p style="color: #888; text-align: center; padding: 20px;">No donors found.</p>
                <?php else: ?>
                    <?php foreach ($recentDonors as $donor): ?>
                    <div class="donor-item">
                        <div class="donor-info">
                            <h4><?php echo htmlspecialchars($donor['donor_name']); ?></h4>
                            <div class="donor-contact">
                                <?php 
                                if (!empty($donor['donor_email'])) {
                                    echo htmlspecialchars($donor['donor_email']);
                                } elseif (!empty($donor['donor_contact'])) {
                                    echo "Contact: " . htmlspecialchars($donor['donor_contact']);
                                } else {
                                    echo "No contact info";
                                }
                                ?>
                            </div>
                            <div class="donor-meta">
                                <?php echo htmlspecialchars($donor['donation_count']); ?> donation(s) • 
                                Last: <?php echo formatDate($donor['last_donation']); ?>
                            </div>
                        </div>
                        <span class="donor-status status-active">Active</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Donations -->
        <div class="content-section">
            <div class="section-header">
                <h3 class="section-title">Recent Donations</h3>
            </div>
            <div class="section-content">
                <?php if (empty($recentDonations)): ?>
                    <p style="color: #888; text-align: center; padding: 20px;">No recent donations.</p>
                <?php else: ?>
                    <?php foreach ($recentDonations as $donation): ?>
                    <?php 
                    $currentStatus = $donation['status'] ?? 'Received';
                    $donationId = $donation['donation_id'];
                    ?>
                    <div class="donation-item" data-donation-id="<?php echo $donationId; ?>">
                        <div class="donation-info">
                            <h4><?php echo htmlspecialchars($donation['description'] ?? 'Donation'); ?></h4>
                            <div class="donation-donor">
                                <?php echo htmlspecialchars($donation['donor_name']); ?>
                                <?php if (!empty($donation['donor_contact'])): ?>
                                    • <?php echo htmlspecialchars($donation['donor_contact']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="donation-meta">
                                <span class="donation-type"><?php echo htmlspecialchars($donation['donation_type']); ?></span>
                                <span class="donation-date"><?php echo formatDate($donation['date_received']); ?></span>
                            </div>
                        </div>
                        <div class="donation-actions">
                            <!-- Status Select - Only show if user has edit permission -->
                            <?php if ($canEdit): ?>
                                <select class="status-select" data-donation-id="<?php echo $donationId; ?>" 
                                        onchange="updateDonationStatus('<?php echo $donationId; ?>', this.value)">
                                    <option value="Received" <?php echo strtolower($currentStatus) === 'received' ? 'selected' : ''; ?>>Received</option>
                                    <option value="Completed" <?php echo strtolower($currentStatus) === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            <?php else: ?>
                                <select class="status-select" disabled style="opacity: 0.6; cursor: not-allowed;">
                                    <option selected><?php echo htmlspecialchars($currentStatus); ?></option>
                                </select>
                            <?php endif; ?>
                            
                            <div class="cancel-button-container">
                                <?php if ($canDelete): ?>
                                    <button class="btn-cancel-small" 
                                            onclick="cancelDonation('<?php echo $donationId; ?>', 
                                            '<?php echo htmlspecialchars($donation['donor_name']); ?>', 
                                            '<?php echo htmlspecialchars($donation['description'] ?? ''); ?>')">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <span class="no-action">No permission</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Record Donation Modal -->
<div class="modal-overlay" id="recordDonationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Record New Donation</h3>
            <button class="modal-close" onclick="hideRecordDonationModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="recordDonationForm" method="POST" action="process-donation.php">
                <input type="hidden" name="action" value="record_donation">
                
                <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Donor Name (Optional)</label>
                    <input type="text" name="donor_name" class="form-input"  
                        placeholder="Full name or organization (leave blank for anonymous)">
                </div>
                    
                    <div class="form-group">
                        <label class="form-label">Donor Contact</label>
                        <input type="text" name="donor_contact" class="form-input" 
                               placeholder="Phone number">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Donor Email</label>
                        <input type="email" name="donor_email" class="form-input" 
                               placeholder="Email address">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Donation Type *</label>
                        <select name="donation_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Goods">Goods</option>
                            <option value="Services">Services</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-textarea" rows="3" required 
                                  placeholder="Describe the donation (e.g., 'Food supplies', 'Clothing', 'Volunteer services', 'Educational materials')"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Date Received *</label>
                        <input type="date" name="date_received" class="form-input" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Received">Received</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea" rows="2" 
                                  placeholder="Additional notes about the donation"></textarea>
                    </div>
                </div>

                <?php if ($fieldManager && !empty($donationCustomFields)): ?>
                <div class="form-section">
                    <h3>Additional Information</h3>
                    <div class="form-grid">
                        <?php 
                        $existingDonationCustomValues = [];
                        if (isset($donationId) && $donationId) {
                            $existingDonationCustomValues = $fieldManager->getFieldValues($donationId, 'donations');
                        }
                        
                        foreach ($donationCustomFields as $field): 
                            $existingValue = $existingDonationCustomValues[$field['field_name']] ?? '';
                            echo str_replace(
                                'name="custom_field[' . $field['field_name'] . ']"',
                                'name="custom_field_' . $field['field_name'] . '"',
                                $fieldManager->renderField($field, $existingValue)
                            );
                        endforeach; 
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideRecordDonationModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Record Donation</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

.read-only-banner {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

/* Rest of your existing donation styles... */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-header {
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 10px;
}

.stat-value {
    color: #ffffff;
    font-size: 32px;
    font-weight: 600;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 24px;
}

.dark-theme .content-section {
    background: #2a2a2a;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #3a3a3a;
}

.light-theme .content-section {
    border-radius: 12px;
    overflow: hidden;
    
    border: 1px solid #bfbbbb;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #3a3a3a;
    
}

.light-theme .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #bfbbbb;
    
}


.light-theme .section-title {
    color: #18338c;
    font-size: 18px;
    font-weight: 600;
}

.dark-theme .section-title {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
}

.section-content {
    padding: 0;
}

.donor-item, .donation-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 16px 24px;
    border-bottom: 1px solid #bfbbbb;
    transition: background-color 0.2s;
}

.donor-item:hover, .donation-item:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.donor-item:last-child, .donation-item:last-child {
    border-bottom: none;
}

.donor-info, .donation-info {
    flex: 1;
}

.dark-theme .donor-info h4, .dark-theme .donation-info h4 {
    color: #b8c5ff;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.light-theme .donor-info h4, .light-theme .donation-info h4 {
    color: #2d5f8d;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 4px;
}

.dark-theme .donor-contact, .dark-theme .donation-donor {
    color: #ffffff;
    font-size: 14px;
    margin-bottom: 4px;
}

.light-theme .donor-contact, .light-theme .donation-donor {
    color: #1e293b;
    font-size: 14px;
    margin-bottom: 4px;
}

.donor-meta, .donation-meta {
    color: #475569;
    font-size: 13px;
    display: flex;
    gap: 12px;
}

.donation-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.donor-status, .donation-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.status-active, .status-received, .status-completed {
    background-color: #28a745;
    color: white;
    font-weight: 600;
}

.btn-cancel-small {
    background: #dc3545;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel-small:hover {
    background: #c82333;
}

.donation-type {
    background-color: #3b82f6;
    color: white;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin: 24px 0;
    flex-wrap: wrap;
}

.no-action {
    color: #888;
    font-style: italic;
    font-size: 11px;
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: #2a2a2a;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid #3a3a3a;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #3a3a3a;
}

.modal-header h3 {
    color: #ffffff;
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    color: #cccccc;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    background-color: #1a1a1a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel:hover {
    background: #5a6268;
}

.btn-submit {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}


.btn-submit:hover {
    background: #2563eb;
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
    margin-right: 8px;
}

.light-theme .status-select {
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #3a3a3a;
    color: black;
    font-size: 12px;
    cursor: pointer;
    margin-right: 8px;
}

.status-select:focus {
    outline: none;
    border-color: #3b82f6;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.status-select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-cancel-small:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .donor-item, .donation-item {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .donor-status, .donation-status {
        align-self: flex-start;
    }
    
    .donation-actions {
        align-items: stretch;
        flex-direction: row;
        justify-content: space-between;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentDonationId = null;

// Get PHP permissions from the page
const canCreate = <?php echo $canCreate ? 'true' : 'false'; ?>;
const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
const canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;

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

function showRecordDonationModal() {
    if (!canCreate) {
        showNotification('Permission denied - You cannot record donations', 'error');
        return;
    }
    document.getElementById('recordDonationModal').classList.add('active');
}

function hideRecordDonationModal() {
    document.getElementById('recordDonationModal').classList.remove('active');
}

function showCancelDonationModal(donationId, donorName, description) {
    if (!canDelete) {
        showNotification('Permission denied - You cannot cancel donations', 'error');
        return;
    }
    
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
    if (!canDelete) {
        showNotification('Permission denied - You cannot cancel donations', 'error');
        return;
    }
    
    showCancelDonationModal(donationId, donorName, description);
}

function updateDonationStatus(donationId, newStatus) {
    if (!canEdit) {
        showNotification('Permission denied - You cannot update donation status', 'error');
        return;
    }
    
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
    
    if (!canDelete) {
        showNotification('Permission denied - You cannot cancel donations', 'error');
        return;
    }
    
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
            // Reload the page after a short delay
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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const recordDonationModal = document.getElementById('recordDonationModal');
    const cancelDonationModal = document.getElementById('cancelDonationModal');
    
    if (event.target === recordDonationModal) {
        hideRecordDonationModal();
    }
    if (event.target === cancelDonationModal) {
        hideCancelDonationModal();
    }
});


// Handle form submission
document.getElementById('recordDonationForm')?.addEventListener('submit', function(e) {
    if (!canCreate) {
        e.preventDefault();
        showNotification('Permission denied - You cannot record donations', 'error');
        return;
    }
    
    const description = this.querySelector('textarea[name="description"]').value.trim();
    
    if (!description) {
        e.preventDefault();
        showNotification('Please enter donation description', 'error');
        return;
    }
    
    // Donor name is now optional - no validation needed
});
</script>

<?php require_once 'includes/footer.php'; ?>