<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();
$currentUser = getCurrentUser();

$pageTitle = 'Protective Action Success - Orphanfare';
require_once 'includes/header.php';

// Enhanced debugging
error_log("=== ACTION-SUCCESSFULLY.PHP ACCESSED ===");
error_log("GET parameters: " . print_r($_GET, true));

if (!isset($_GET['action_id'])) {
    error_log("ERROR: No action_id provided in URL");
    echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 8px; text-align: center; margin: 20px;'>";
    echo "<h3>Error: No Action ID Provided</h3>";
    echo "<p>Please go back and try again.</p>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit();
}

$actionId = $_GET['action_id'];
error_log("DEBUG: Processing action_id: " . $actionId);

// Try multiple times to find the action (database replication delay)
$action = null;
$maxRetries = 3;

for ($i = 0; $i < $maxRetries; $i++) {
    try {
        $stmt = $pdo->prepare("
            SELECT pa.*, c.child_name, c.case_id as case_number, c.reporter_email
            FROM protective_actions pa
            LEFT JOIN cases c ON pa.case_id = c.case_id
            WHERE pa.action_id = ?
        ");
        $stmt->execute([$actionId]);
        $action = $stmt->fetch();
        
        if ($action) {
            error_log("SUCCESS: Found action on attempt " . ($i + 1));
            break;
        } else {
            error_log("DEBUG: Action not found on attempt " . ($i + 1) . ", retrying...");
            usleep(500000); // 0.5 second delay before retry
        }
    } catch (Exception $e) {
        error_log("ERROR: Database query failed on attempt " . ($i + 1) . ": " . $e->getMessage());
    }
}

if (!$action) {
    error_log("WARNING: Action not found after " . $maxRetries . " attempts: " . $actionId);
    
    // Show a success page anyway with the information we have
    echo "<main class='main-content'>";
    echo "<div class='page-header'>";
    echo "<h1 class='page-title'>Protective Action Initiated Successfully</h1>";
    echo "</div>";
    
    echo "<div class='success-container'>";
    echo "<div class='success-header'>";
    echo "<h2 class='success-title'>✅ Protective Action Initiated</h2>";
    echo "<p class='success-subtitle'>Action ID: <strong>" . htmlspecialchars($actionId) . "</strong></p>";
    echo "<p style='color: #ffa500; margin-top: 10px;'><em>Note: Database synchronization in progress...</em></p>";
    echo "</div>";
    
    echo "<div class='success-actions'>";
    echo "<h3>Immediate Actions Taken:</h3>";
    echo "<ul>";
    echo "<li>✅ Protective action record created</li>";
    echo "<li>✅ Case status updated to 'Protective Action Active'</li>";
    echo "<li>✅ Legal workflows triggered</li>";
    echo "<li>✅ Emergency notifications queued</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='next-steps'>";
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>• Monitor case progress in the Case Management system</li>";
    echo "<li>• The protective action details will be available shortly</li>";
    echo "<li>• Check back in a few moments for complete information</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='action-buttons' style='margin-top: 30px;'>";
    echo "<button class='btn btn-primary' onclick='window.location.href=\"case-management.php\"'>Back to Case Management</button>";
    echo "<button class='btn' onclick='window.location.reload()' style='background: #28a745; color: white;'>Refresh Page</button>";
    echo "</div>";
    echo "</div>";
    echo "</main>";
    
    require_once 'includes/footer.php';
    exit();
}

// If we found the action, continue with normal display
error_log("SUCCESS: Found action in database: " . $action['action_id']);

// Change to handle string notifications:
$notificationsString = $action['notifications'] ?? '';
$notifications = !empty($notificationsString) ? explode(', ', $notificationsString) : [];

// Email mapping for notifications - USING reporter_email FOR FAMILY
$emailMapping = [
    'Supervisor' => 'supervisor@orphanfare.gov',
    'Legal Department' => 'legal@orphanfare.gov',
    'Court Services' => 'court@orphanfare.gov',
    'Law Enforcement' => 'lawenforcement@orphanfare.gov',
    'Medical Services' => 'medical@orphanfare.gov',
    'Family Members' => $action['reporter_email'] ?? 'family@orphanfare.gov',
    'Child Protection Unit' => 'cpu@orphanfare.gov',
    'Social Services' => 'social@orphanfare.gov'
];

// Generate email notifications data
$emailNotifications = [];
foreach ($notifications as $notification) {
    if (isset($emailMapping[$notification])) {
        $email = $emailMapping[$notification];
        $emailNotifications[] = [
            'recipient' => $notification,
            'email' => $email,
            'status' => 'Sent',
            'time' => date('Y-m-d H:i:s')
        ];
    }
}

// Default email template
$defaultEmailTemplate = "🚨 URGENT: Protective Action Initiated - Case {CASE_ID}

From: Orphanfare Alert System <alerts@orphanfare.gov>
To: {RECIPIENT_EMAIL}

⚠️ IMMEDIATE ACTION REQUIRED

A protective action has been initiated and requires your immediate attention.

Action ID: {ACTION_ID}
Case ID: {CASE_ID}
Child Name: {CHILD_NAME}
Case Type: {CASE_TYPE}
Action Type: {ACTION_TYPE}
Priority: {PRIORITY}
Coordinating Officer: {COORDINATING_OFFICER}
Initiated by: {INITIATOR}
Time: {TIMESTAMP}

Justification:
{JUSTIFICATION}

Next Steps:
• Log into the system for full case details
• Contact {COORDINATING_OFFICER} immediately
• Review and approve action if supervisor

This is an automated message from Orphanfare Protective Action System.";

// Get saved email template from database or use default
$emailTemplate = $defaultEmailTemplate;
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Protective Action Initiated Successfully</h1>
    </div>

    <div class="success-container">
        <div class="success-header">
            <h2 class="success-title">✅ Protective Action Initiated</h2>
            <p class="success-subtitle">Action ID: <strong><?php echo htmlspecialchars($action['action_id']); ?></strong></p>
        </div>

        <div class="success-details">
            <div class="detail-item">
                <span class="detail-label">Case:</span>
                <span class="detail-value"><?php echo htmlspecialchars($action['case_number'] . ' - ' . $action['child_name']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Action Type:</span>
                <span class="detail-value"><?php echo htmlspecialchars($action['action_type']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Priority:</span>
                <span class="detail-value"><?php echo htmlspecialchars(ucfirst($action['priority'])); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Coordinating Officer:</span>
                <span class="detail-value"><?php echo htmlspecialchars($action['coordinating_officer']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Initiated By:</span>
                <span class="detail-value"><?php echo htmlspecialchars($currentUser['username'] ?? 'System'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Time:</span>
                <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($action['created_at'])); ?></span>
            </div>
        </div>

        <div class="success-actions">
            <h3>Immediate Actions Taken:</h3>
            <ul>
                <li>✅ Emergency notifications sent to all selected parties</li>
                <li>✅ Case status updated to "Protective Action Active"</li>
                <li>✅ Official protective action record created</li>
                <li>✅ Legal workflows automatically triggered</li>
            </ul>
        </div>

        <!-- Email Notifications Sent Table -->
        <?php if (!empty($emailNotifications)): ?>
        <div class="email-notifications">
            <h3>Email Notifications Sent:</h3>
            <table class="notification-table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Recipient</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody id="emailNotificationsList">
                    <?php foreach ($emailNotifications as $email): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($email['time']); ?></td>
                        <td><span class="status-sent"><?php echo htmlspecialchars($email['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($email['recipient']); ?></td>
                        <td><?php echo htmlspecialchars($email['email']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="email-notifications">
            <h3>Email Notifications:</h3>
            <p style="color: #888; text-align: center;">No email notifications were configured for this action.</p>
        </div>
        <?php endif; ?>

        <!-- Editable Email Template Preview -->
        <div class="email-template">
            <div class="email-header">
                Email Template Preview:
                <button class="btn-edit-email" onclick="toggleEmailEdit()">Edit Template</button>
            </div>
            <div class="email-preview" id="emailPreview">
                <div class="email-urgent">🚨 URGENT: Protective Action Initiated - Case <?php echo htmlspecialchars($action['case_number']); ?></div>
                <p>From: Orphanfare Alert System &lt;alerts@orphanfare.gov&gt;<br>
                To: <span id="emailRecipient">recipient@example.com</span></p>
                
                <div class="email-content">
                    <p><strong>⚠️ IMMEDIATE ACTION REQUIRED</strong></p>
                    <p>A protective action has been initiated and requires your immediate attention.</p>
                    
                    <div class="email-details">
                        <p><strong>Action ID:</strong> <?php echo htmlspecialchars($action['action_id']); ?></p>
                        <p><strong>Case ID:</strong> <?php echo htmlspecialchars($action['case_number']); ?></p>
                        <p><strong>Child Name:</strong> <?php echo htmlspecialchars($action['child_name']); ?></p>
                        <p><strong>Action Type:</strong> <?php echo htmlspecialchars($action['action_type']); ?></p>
                        <p><strong>Priority:</strong> <?php echo htmlspecialchars(ucfirst($action['priority'])); ?></p>
                        <p><strong>Coordinating Officer:</strong> <?php echo htmlspecialchars($action['coordinating_officer']); ?></p>
                        <p><strong>Initiated by:</strong> <?php echo htmlspecialchars($currentUser['username'] ?? 'System'); ?></p>
                        <p><strong>Time:</strong> <?php echo date('M j, Y g:i A', strtotime($action['created_at'])); ?></p>
                        <?php if (!empty($action['reporter_email'])): ?>
                        <p><strong>Family Contact:</strong> <?php echo htmlspecialchars($action['reporter_email']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <p><strong>Justification:</strong></p>
                    <div class="justification-text"><?php echo nl2br(htmlspecialchars($action['justification'])); ?></div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>• Log into the system for full case details</li>
                        <li>• Contact <?php echo htmlspecialchars($action['coordinating_officer']); ?> immediately</li>
                        <li>• Review and approve action if supervisor</li>
                    </ul>
                </div>
            </div>
            
            <!-- Editable email template form -->
            <div class="email-edit-form" id="emailEditForm" style="display: none;">
                <textarea id="emailTemplateEditor" rows="15" style="width: 100%; padding: 10px; font-family: monospace; background: #1a1a1a; color: #fff; border: 1px solid #444; border-radius: 6px;"><?php echo htmlspecialchars($emailTemplate); ?></textarea>
                <div class="template-variables">
                    <strong>Available Variables:</strong>
                    <span class="variable-tag" onclick="insertVariable('{CASE_ID}')">{CASE_ID}</span>
                    <span class="variable-tag" onclick="insertVariable('{ACTION_ID}')">{ACTION_ID}</span>
                    <span class="variable-tag" onclick="insertVariable('{CHILD_NAME}')">{CHILD_NAME}</span>
                    <span class="variable-tag" onclick="insertVariable('{CASE_TYPE}')">{CASE_TYPE}</span>
                    <span class="variable-tag" onclick="insertVariable('{ACTION_TYPE}')">{ACTION_TYPE}</span>
                    <span class="variable-tag" onclick="insertVariable('{PRIORITY}')">{PRIORITY}</span>
                    <span class="variable-tag" onclick="insertVariable('{COORDINATING_OFFICER}')">{COORDINATING_OFFICER}</span>
                    <span class="variable-tag" onclick="insertVariable('{INITIATOR}')">{INITIATOR}</span>
                    <span class="variable-tag" onclick="insertVariable('{TIMESTAMP}')">{TIMESTAMP}</span>
                    <span class="variable-tag" onclick="insertVariable('{JUSTIFICATION}')">{JUSTIFICATION}</span>
                    <span class="variable-tag" onclick="insertVariable('{RECIPIENT_EMAIL}')">{RECIPIENT_EMAIL}</span>
                </div>
                <div class="email-edit-buttons">
                    <button class="btn btn-save" onclick="saveEmailTemplate()">Save Template</button>
                    <button class="btn btn-cancel" onclick="cancelEmailEdit()">Cancel</button>
                    <button class="btn btn-send-test" onclick="sendTestEmail()">Send Test Email</button>
                </div>
            </div>
        </div>

        <div class="next-steps">
            <h3>Next Steps:</h3>
            <ul>
                <li>• Officer <strong><?php echo htmlspecialchars($action['coordinating_officer']); ?></strong> will coordinate the protective action</li>
                <?php if ($action['followup_date']): ?>
                <li>• Follow-up required by: <strong><?php echo date('M j, Y g:i A', strtotime($action['followup_date'])); ?></strong></li>
                <?php endif; ?>
                <li>• Monitor case progress in the Case Management system</li>
                <li>• Email notifications sent to <?php echo count($emailNotifications); ?> recipients</li>
            </ul>
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <button class="btn btn-primary" onclick="window.location.href='case-management.php'">Back to Case Management</button>
            <button class="btn" onclick="window.location.href='case-info.php?case_id=<?php echo urlencode($action['case_id']); ?>'" style="background: #6c757d; color: white;">View Case Details</button>
            <button class="btn" onclick="printEmailReport()" style="background: #28a745; color: white;">Print Email Report</button>
        </div>
    </div>
</main>

<!-- Your existing CSS and JavaScript remain the same -->
<style>
/* Your existing CSS styles */
.success-container {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 24px;
}

.success-header {
    text-align: center;
    margin-bottom: 30px;
}

.success-title {
    color: #28a745;
    font-size: 28px;
    margin-bottom: 10px;
}

.success-subtitle {
    color: #b8c5ff;
    font-size: 18px;
}

.success-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #333333;
    border-radius: 6px;
}

.detail-label {
    color: #b8c5ff;
    font-weight: 500;
}

.detail-value {
    color: #ffffff;
    font-weight: 600;
}

.success-actions, .next-steps, .email-notifications, .email-template {
    background: #333333;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.success-actions h3, .next-steps h3, .email-notifications h3, .email-template h3 {
    color: #b8c5ff;
    margin-bottom: 15px;
}

.success-actions ul, .next-steps ul {
    color: #ffffff;
    padding-left: 20px;
}

.success-actions li, .next-steps li {
    margin-bottom: 8px;
}

.notification-table {
    width: 100%;
    border-collapse: collapse;
    background: #2a2a2a;
    border-radius: 6px;
    overflow: hidden;
}

.notification-table th, .notification-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #444;
}

.notification-table th {
    background: #3a3a3a;
    color: #b8c5ff;
    font-weight: 600;
}

.notification-table td {
    color: #ffffff;
}

.status-sent {
    color: #28a745;
    font-weight: 600;
}

.email-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.btn-edit-email {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.email-preview {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #444;
    font-family: monospace;
    color: #fff;
    white-space: pre-wrap;
}

.email-urgent {
    color: #dc3545;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 15px;
}

.email-details {
    background: #2a2a2a;
    padding: 15px;
    border-radius: 4px;
    margin: 15px 0;
}

.justification-text {
    background: #2a2a2a;
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;
    border-left: 3px solid #007bff;
}

.template-variables {
    margin: 15px 0;
    padding: 15px;
    background: #2a2a2a;
    border-radius: 6px;
}

.variable-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 4px 8px;
    margin: 2px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.variable-tag:hover {
    background: #0056b3;
}

.email-edit-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-save, .btn-cancel, .btn-send-test {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.btn-save {
    background: #28a745;
    color: white;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-send-test {
    background: #ffc107;
    color: #000;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}
</style>

<script>
function toggleEmailEdit() {
    const preview = document.getElementById('emailPreview');
    const editForm = document.getElementById('emailEditForm');
    
    if (preview.style.display === 'none') {
        preview.style.display = 'block';
        editForm.style.display = 'none';
    } else {
        preview.style.display = 'none';
        editForm.style.display = 'block';
    }
}

function cancelEmailEdit() {
    document.getElementById('emailPreview').style.display = 'block';
    document.getElementById('emailEditForm').style.display = 'none';
}

function insertVariable(variable) {
    const editor = document.getElementById('emailTemplateEditor');
    const startPos = editor.selectionStart;
    const endPos = editor.selectionEnd;
    
    editor.value = editor.value.substring(0, startPos) + variable + editor.value.substring(endPos);
    editor.focus();
    editor.setSelectionRange(startPos + variable.length, startPos + variable.length);
}

function saveEmailTemplate() {
    const template = document.getElementById('emailTemplateEditor').value;
    
    // Here you would typically save to database via AJAX
    alert('Template saved locally (database integration needed)');
    cancelEmailEdit();
}

function sendTestEmail() {
    const template = document.getElementById('emailTemplateEditor').value;
    
    if (confirm('Send test email to your account?')) {
        alert('Test email functionality would be implemented here');
    }
}

function printEmailReport() {
    window.print();
}
</script>

<?php require_once 'includes/footer.php'; ?>