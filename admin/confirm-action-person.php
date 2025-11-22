<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();
$currentUser = getCurrentUser();

// Only handle GET requests for confirmation page
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['protective_action_data'])) {
        header('Location: initiate-protective-action.php');
        exit();
    }
    
    $selectedCaseId = $_GET['selected_case'] ?? '';
    if (empty($selectedCaseId)) {
        die('No case selected');
    }
    
    $data = $_SESSION['protective_action_data'];
    
    $stmt = $pdo->prepare("
        SELECT case_id, child_name, child_age, case_type, status, priority, description
        FROM cases WHERE case_id = ?
    ");
    $stmt->execute([$selectedCaseId]);
    $selectedCase = $stmt->fetch();

    if (!$selectedCase) {
        die('Selected case not found: ' . $selectedCaseId);
    }
    
    $pageTitle = 'Confirm Protective Action - Orphanfare';
    require_once 'includes/header.php';
    ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Confirm Protective Action</h1>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="confidentiality-alert">
            <p>⚠️ Please review all details before confirming. This action cannot be undone.</p>
        </div>

        <div class="review-sections">
            <!-- Selected Child Information -->
            <div class="review-section">
                <h3>Selected Child</h3>
                <div class="review-card">
                    <div class="review-row">
                        <span class="review-label">Case ID:</span>
                        <span class="review-value"><?php echo htmlspecialchars($selectedCase['case_id']); ?></span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Child Name:</span>
                        <span class="review-value"><?php echo htmlspecialchars($selectedCase['child_name']); ?></span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Age:</span>
                        <span class="review-value"><?php echo htmlspecialchars($selectedCase['child_age'] ?? 'Unknown'); ?></span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Case Type:</span>
                        <span class="review-value"><?php echo htmlspecialchars($selectedCase['case_type']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Action Details -->
            <div class="review-section">
                <h3>Action Details</h3>
                <div class="review-card">
                    <div class="review-row">
                        <span class="review-label">Action Type:</span>
                        <span class="review-value"><?php echo htmlspecialchars($data['action_type']); ?></span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Priority Level:</span>
                        <span class="review-value priority-badge <?php echo htmlspecialchars($data['priority']); ?>">
                            <?php echo htmlspecialchars(ucfirst($data['priority'])); ?>
                        </span>
                    </div>
                    <div class="review-row">
                        <span class="review-label">Coordinating Officer:</span>
                        <span class="review-value"><?php echo htmlspecialchars($data['coordinating_officer']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Notification & Coordination -->
            <div class="review-section">
                <h3>Notification & Coordination</h3>
                <div class="review-card">
                    <div class="review-row">
                        <span class="review-label">Departments/People to Notify:</span>
                        <div class="review-value">
                            <?php if (!empty($data['notifications'])): ?>
                                <div class="notifications-list">
                                    <?php foreach ($data['notifications'] as $notification): ?>
                                        <span class="notification-tag"><?php echo htmlspecialchars($notification); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="no-notifications">No notifications selected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Justification & Additional Info -->
            <div class="review-section">
                <h3>Justification & Additional Information</h3>
                <div class="review-card">
                    <div class="review-row full-width">
                        <span class="review-label">Justification:</span>
                        <div class="review-value justification-text">
                            <?php echo nl2br(htmlspecialchars($data['justification'])); ?>
                        </div>
                    </div>
                    <?php if (!empty($data['case_description'])): ?>
                    <div class="review-row full-width">
                        <span class="review-label">Additional Information:</span>
                        <div class="review-value">
                            <?php echo nl2br(htmlspecialchars($data['case_description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($data['followup_date'])): ?>
                    <div class="review-row">
                        <span class="review-label">Follow-up Required By:</span>
                        <span class="review-value"><?php echo date('M j, Y g:i A', strtotime($data['followup_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Confirmation Form -->
        <form method="POST" action="process-protective-action.php" class="confirmation-form" id="confirmationForm">
            <input type="hidden" name="selected_case" value="<?php echo htmlspecialchars($selectedCaseId); ?>">
            <input type="hidden" name="confirm_action" value="1">
            
            <div class="form-actions">
                <a href="select-person.php" class="btn-cancel">Back to Person Selection</a>
                <button type="submit" class="btn-confirm" id="confirmButton">Confirm & Initiate Protective Action</button>
            </div>
        </form>

        <!-- Debug Info -->
        <div style="background: #333; padding: 15px; margin-top: 20px; border-radius: 5px; font-size: 12px; display: none;">
            <h4>Debug Info:</h4>
            <p>Case ID: <?php echo htmlspecialchars($selectedCaseId); ?></p>
            <p>Session exists: <?php echo isset($_SESSION['protective_action_data']) ? 'YES' : 'NO'; ?></p>
            <p>Form action: process-protective-action.php</p>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('confirmationForm');
        const confirmButton = document.getElementById('confirmButton');
        
        console.log('Confirmation page loaded');
        console.log('Form:', form);
        console.log('Confirm button:', confirmButton);
        
        if (form && confirmButton) {
            form.addEventListener('submit', function(e) {
                console.log('Form submission started');
                confirmButton.disabled = true;
                confirmButton.textContent = 'Processing...';
                
                // Let the form submit normally
                return true;
            });
        } else {
            console.error('Form or confirm button not found!');
        }
    });
    </script>

    <style>
    .review-sections {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 30px;
    }

    .review-section {
        background: #2a2a2a;
        border-radius: 8px;
        padding: 20px;
    }

    .review-section h3 {
        color: #fff;
        margin-bottom: 15px;
        font-size: 18px;
        border-bottom: 1px solid #444;
        padding-bottom: 8px;
    }

    .review-card {
        background: #333;
        border-radius: 6px;
        padding: 15px;
    }

    .review-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #444;
    }

    .review-row:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .review-row.full-width {
        flex-direction: column;
        align-items: flex-start;
    }

    .review-label {
        color: #b8c5ff;
        font-weight: 500;
        min-width: 180px;
    }

    .review-value {
        color: #fff;
        text-align: right;
        flex: 1;
    }

    .review-row.full-width .review-value {
        text-align: left;
        margin-top: 8px;
    }

    .priority-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-badge.urgent {
        background: #dc3545;
        color: white;
    }

    .priority-badge.mild {
        background: #ffc107;
        color: #000;
    }

    .priority-badge.common {
        background: #28a745;
        color: white;
    }

    .notifications-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .notification-tag {
        background: #007bff;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .justification-text {
        background: #1a1a1a;
        padding: 12px;
        border-radius: 6px;
        border-left: 4px solid #007bff;
    }

    .confidentiality-alert {
        background: #2d1f1f;
        border: 1px solid #dc3545;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        color: #fff;
    }

    .confirmation-form {
        background: #2a2a2a;
        border-radius: 12px;
        padding: 24px;
        margin-top: 20px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        gap: 16px;
    }

    .btn-cancel, .btn-confirm {
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
        color: white;
        text-decoration: none;
    }

    .btn-confirm {
        background: #dc3545;
        color: white;
    }

    .btn-confirm:hover {
        background: #c82333;
    }

    .btn-confirm:disabled {
        background: #495057;
        cursor: not-allowed;
    }

    .no-notifications {
        color: #888;
        font-style: italic;
    }

    .error-message {
        background: #dc3545;
        color: white;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
    </style>

    <?php 
    require_once 'includes/footer.php';
} else {
    // If someone tries to POST directly to this page, redirect them
    header('Location: select-person.php');
    exit();
}
?>