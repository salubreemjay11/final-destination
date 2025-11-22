<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection first
require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Initiate Protective Action - Orphanfare';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'initiate') {
    // Remove 'case_id' from required fields
    $required = ['case_type', 'action_type', 'priority', 'justification', 'coordinating_officer'];
    $missing = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        $error = "Missing required fields: " . implode(', ', $missing);
    } else {
        // DEBUG: Check what we're receiving
        error_log("Form submitted with case_type: " . $_POST['case_type']);
        
        // Store form data in session WITHOUT case_id
        $_SESSION['protective_action_data'] = [
            'case_type' => $_POST['case_type'],
            'action_type' => $_POST['action_type'],
            'priority' => $_POST['priority'],
            'justification' => $_POST['justification'],
            'notifications' => $_POST['notifications'] ?? [],
            'coordinating_officer' => $_POST['coordinating_officer'],
            'case_description' => $_POST['case_description'] ?? '',
            'followup_date' => $_POST['followup_date'] ?? '',
            'created_by' => $currentUser['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // DEBUG: Check session data
        error_log("Session data stored: " . print_r($_SESSION['protective_action_data'], true));
        
        // Log the activity
        logActivity($currentUser['id'], 'Protective Action Initiated', 'protective_actions', 'NEW');
        
        // Redirect to SELECT PERSON page
        header("Location: select-person.php");
        exit();
    }
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Initiate Protective Action</h1>
        <button class="btn btn-secondary" onclick="window.location.href='case-management.php'">← Back to Cases</button>
    </div>

    <div class="confidentiality-alert">
        <p>🚨 This action will trigger immediate protective measures and alert relevant authorities.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="protective-action-form" id="protectiveForm">
        <input type="hidden" name="action" value="initiate">

        <!-- Case Type Selection -->
        <div class="form-group">
            <label class="form-label required">Case Type</label>
            <div class="button-group">
                <button type="button" class="case-type-btn" onclick="selectCaseType('Physical Abuse', this)">Physical Abuse</button>
                <button type="button" class="case-type-btn" onclick="selectCaseType('Sexual Abuse', this)">Sexual Abuse</button>
                <button type="button" class="case-type-btn" onclick="selectCaseType('Neglect', this)">Neglect</button>
                <button type="button" class="case-type-btn" onclick="selectCaseType('Abandonment', this)">Abandonment</button>
                <button type="button" class="case-type-btn" onclick="selectCaseType('Exploitation', this)">Exploitation</button>
                <button type="button" class="case-type-btn" onclick="selectCaseType('Special Laws', this)">Special Laws</button>
            </div>
            <input type="hidden" name="case_type" id="selectedCaseType" required>
            <div id="caseTypeError" style="color: #dc3545; font-size: 14px; margin-top: 5px; display: none;">
                Please select a case type
            </div>
        </div>

        <!-- Action Details -->
        <div class="form-group">
            <label class="form-label required">Action Type</label>
            <select name="action_type" class="form-select" required>
                <option value="">Select action type</option>
                <option value="Emergency Child Removal">Emergency Child Removal</option>
                <option value="Safety Plan Implementation">Safety Plan Implementation</option>
                <option value="Immediate Investigation">Immediate Investigation</option>
                <option value="Court Order Request">Court Order Request</option>
                <option value="Medical Evaluation">Medical Evaluation</option>
                <option value="Temporary Custody">Temporary Custody</option>
                <option value="Emergency Shelter Placement">Emergency Shelter Placement</option>
            </select>
        </div>

        <!-- Priority Level -->
        <div class="form-group">
            <label class="form-label required">Priority Level</label>
            <div class="button-group">
                <button type="button" class="priority-btn common" onclick="selectPriority('low', this)">Low</button>
                <button type="button" class="priority-btn mild" onclick="selectPriority('medium', this)">Medium</button>
                <button type="button" class="priority-btn urgent active" onclick="selectPriority('urgent', this)">Urgent</button>
               
            </div>
            <input type="hidden" name="priority" id="selectedPriority" value="urgent" required>
        </div>

        <!-- Justification -->
        <div class="form-group">
            <label class="form-label required">Justification</label>
            <textarea name="justification" class="form-textarea" required placeholder="Provide detailed justification for this protective action..." rows="4"></textarea>
        </div>

        <!-- Notification & Coordination -->
        <div class="form-group">
            <label class="form-label required">Notification & Coordination</label>
            <div class="checkbox-grid">
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Supervisor" id="notify_supervisor" checked>
                    <label for="notify_supervisor">Supervisor</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Legal Department" id="notify_legal" checked>
                    <label for="notify_legal">Legal Department</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Court Services" id="notify_court">
                    <label for="notify_court">Court Services</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Law Enforcement" id="notify_law_enforcement" checked>
                    <label for="notify_law_enforcement">Law Enforcement</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Medical Services" id="notify_medical">
                    <label for="notify_medical">Medical Services</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Family Members" id="notify_family">
                    <label for="notify_family">Family Members</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Child Protection Unit" id="notify_cpu" checked>
                    <label for="notify_cpu">Child Protection Unit</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" name="notifications[]" value="Social Services" id="notify_social">
                    <label for="notify_social">Social Services</label>
                </div>
            </div>
        </div>

        <!-- Coordinating Officer -->
        <div class="form-group">
            <label class="form-label required">Coordinating Officer</label>
            <select name="coordinating_officer" class="form-select" required>
                <option value="">Assign coordinating officer</option>
                <option value="Officer Jean Martinez">Officer Jean Martinez</option>
                <option value="Officer Sarah Davis">Officer Sarah Davis</option>
                <option value="Officer Mike Johnson">Officer Mike Johnson</option>
                <option value="Officer Robert Wilson">Officer Robert Wilson</option>
                <option value="Officer Maria Garcia">Officer Maria Garcia</option>
            </select>
        </div>

        <!-- Additional Information -->
        <div class="form-group">
            <label class="form-label">Case Description</label>
            <textarea name="case_description" class="form-textarea" placeholder="Any additional information or special circumstances..." rows="3"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Follow-up Required By</label>
            <input type="datetime-local" name="followup_date" class="form-input" min="<?php echo date('Y-m-d\TH:i'); ?>">
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="goBackToCaseManagement()">Cancel</button>
            <button type="submit" class="btn-submit">Continue to Select Person →</button>
        </div>
    </form>
</main>

<style>
.confidentiality-alert {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    text-align: center;
    font-weight: 600;
    border-left: 4px solid #ff6b6b;
}

.protective-action-form {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
    border: 1px solid #3a3a3a;
}

.form-group {
    margin-bottom: 28px;
}

.form-label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #e0e0e0;
    font-size: 15px;
}

.form-select, .form-input, .form-textarea {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    border: 1px solid #444;
    background: #1a1a1a;
    color: #fff;
    font-size: 15px;
    transition: border-color 0.2s;
}

.form-select:focus, .form-input:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
    line-height: 1.5;
}

.button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
}

.case-type-btn, .priority-btn {
    padding: 14px 20px;
    border: 2px solid #444;
    border-radius: 8px;
    background: #333;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    flex: 1;
    min-width: 140px;
    font-weight: 500;
}

.case-type-btn:hover, .priority-btn:hover {
    background: #444;
    transform: translateY(-1px);
}

.case-type-btn.active {
    border-color: #3b82f6;
    background: #3b82f6;
    color: white;
}

.priority-btn.common.active { border-color: #17a2b8; background: #17a2b8; }
.priority-btn.mild.active { border-color: #ffc107; background: #ffc107; color: #000; }
.priority-btn.urgent.active { border-color: #fd7e14; background: #fd7e14; color: white; }
.priority-btn.critical.active { border-color: #dc3545; background: #dc3545; color: white; }

.checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-top: 10px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 6px;
    background: #333;
    transition: background 0.2s;
}

.checkbox-item:hover {
    background: #3a3a3a;
}

.checkbox-item input[type="checkbox"] {
    margin: 0;
    transform: scale(1.3);
    accent-color: #3b82f6;
}

.checkbox-item label {
    cursor: pointer;
    font-size: 14px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-top: 40px;
    padding-top: 24px;
    border-top: 1px solid #3a3a3a;
}

.btn-cancel, .btn-submit {
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s;
    font-weight: 600;
    min-width: 160px;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-submit {
    background: #28a745;
    color: white;
}

.btn-submit:hover {
    background: #218838;
    transform: translateY(-1px);
}

.required::after {
    content: " *";
    color: #dc3545;
}

.error-message {
    background: #dc3545;
    color: white;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-weight: 500;
    border-left: 4px solid #ff6b6b;
}

.success-message {
    background: #28a745;
    color: white;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-weight: 500;
    border-left: 4px solid #51cf66;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.btn-secondary:hover {
    background: #5a6268;
}
</style>

<script>
let selectedCaseType = '';

function selectCaseType(caseType, button) {
    document.querySelectorAll('.case-type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');
    selectedCaseType = caseType;
    document.getElementById('selectedCaseType').value = caseType;
    document.getElementById('caseTypeError').style.display = 'none';
}

function selectPriority(priority, button) {
    document.querySelectorAll('.priority-btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
    document.getElementById('selectedPriority').value = priority;
}

function goBackToCaseManagement() {
    if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
        window.location.href = 'case-management.php';
    }
}

function validateForm() {
    const caseType = document.getElementById('selectedCaseType').value;
    const actionType = document.querySelector('[name="action_type"]').value;
    const justification = document.querySelector('[name="justification"]').value;
    const coordinatingOfficer = document.querySelector('[name="coordinating_officer"]').value;
    const notifications = document.querySelectorAll('input[name="notifications[]"]:checked');

    if (!caseType) {
        document.getElementById('caseTypeError').style.display = 'block';
        alert('Please select a case type first.');
        return false;
    }
    if (!actionType) {
        alert('Please select an action type.');
        return false;
    }
    if (!justification.trim()) {
        alert('Please provide a justification.');
        return false;
    }
    if (!coordinatingOfficer) {
        alert('Please select a coordinating officer.');
        return false;
    }
    if (notifications.length === 0) {
        alert('Please select at least one department/person to notify.');
        return false;
    }
    return true;
}

// Initialize form on load
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('protectiveForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submission initiated');
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            // Show loading state
            const submitBtn = form.querySelector('.btn-submit');
            submitBtn.innerHTML = 'Processing...';
            submitBtn.disabled = true;
        });
    } else {
        console.error('Form with ID "protectiveForm" not found!');
    }

    // Set minimum datetime for followup date
    const followupInput = document.querySelector('input[name="followup_date"]');
    if (followupInput) {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        followupInput.min = now.toISOString().slice(0, 16);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>