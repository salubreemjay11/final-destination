<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();

if (!isset($_SESSION['protective_action_data'])) {
    header('Location: initiate-protective-action.php');
    exit();
}

$data = $_SESSION['protective_action_data'];
$caseType = $data['case_type'];

$pageTitle = 'Select Person - Orphanfare';
require_once 'includes/header.php';

// Get cases based on the selected case type
$cases = [];
try {
    $stmt = $pdo->prepare("
        SELECT case_id, child_name, child_age, case_type, status, priority, description
        FROM cases 
        WHERE case_type = ? 
        AND status IN ('Open', 'Under Investigation', 'Court Action Pending')
        ORDER BY 
            CASE 
                WHEN priority = 'urgent' THEN 1
                WHEN priority = 'high' THEN 2
                WHEN priority = 'medium' THEN 3
                WHEN priority = 'low' THEN 4
                ELSE 5
            END,
            created_date DESC
    ");
    
    $stmt->execute([$caseType]);
    $cases = $stmt->fetchAll();
    
    if (empty($cases)) {
        $fallbackStmt = $pdo->prepare("
            SELECT case_id, child_name, child_age, case_type, status, priority, description
            FROM cases 
            WHERE LOWER(case_type) LIKE LOWER(?)
            AND status IN ('Open', 'Under Investigation', 'Court Action Pending')
            ORDER BY created_date DESC
        ");
        $fallbackStmt->execute(["%$caseType%"]);
        $cases = $fallbackStmt->fetchAll();
    }
} catch (Exception $e) {
    error_log("Database error in select-person.php: " . $e->getMessage());
    $cases = [];
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Select Person for Protective Action</h1>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='initiate-protective-action.php'">← Back to Form</button>
    </div>

    <div class="confidentiality-alert">
        <p>Select the individual requiring immediate protective action</p>
    </div>

    <div class="case-info-section">
        <h3 style="color: #dc3545; font-size: 16px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($caseType); ?> Cases
        </h3>
        <p style="color: #a0a0a0; margin-bottom: 20px;">
            Found <?php echo count($cases); ?> active case<?php echo count($cases) !== 1 ? 's' : ''; ?> requiring protective action.
        </p>
    </div>

    <?php if (!empty($cases)): ?>
    <form method="GET" action="confirm-action-person.php" class="person-selection-form" id="personForm">
        <div class="person-list">
            <?php foreach ($cases as $case): ?>
            <div class="person-card">
                <input type="radio" name="selected_case" value="<?php echo htmlspecialchars($case['case_id']); ?>" 
                       id="case_<?php echo htmlspecialchars($case['case_id']); ?>" 
                       class="person-radio" required>
                <label for="case_<?php echo htmlspecialchars($case['case_id']); ?>" class="person-info">
                    <div class="person-header">
                        <span class="person-name"><?php echo htmlspecialchars($case['child_name']); ?></span>
                        <span class="person-role victim">VICTIM</span>
                    </div>
                    <div class="person-details">
                        <span>Age: <?php echo htmlspecialchars($case['child_age'] ?? 'Unknown'); ?></span>
                        <span>Case ID: <?php echo htmlspecialchars($case['case_id']); ?></span>
                        <span>Status: <?php echo htmlspecialchars($case['status']); ?></span>
                    </div>
                    <div class="risk-level <?php echo getRiskLevelClass($case['priority'] ?? 'medium'); ?>">
                        <?php echo htmlspecialchars(ucfirst($case['priority'] ?? 'medium') . ' Priority'); ?>
                    </div>
                    <?php if (!empty($case['description'])): ?>
                    <div class="case-description">
                        <?php echo htmlspecialchars(substr($case['description'], 0, 100) . (strlen($case['description']) > 100 ? '...' : '')); ?>
                    </div>
                    <?php endif; ?>
                </label>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="warning-banner">
            ⚠️ Important: Protective actions will be applied specifically to the selected individual. Multiple actions may be required for multiple persons.
        </div>

        <div class="form-actions">
            <a href="initiate-protective-action.php" class="btn-cancel">← Back to Form</a>
            <button type="submit" class="btn-submit" id="continueBtn" disabled>Continue to Confirmation →</button>
        </div>
    </form>
    <?php else: ?>
        <div class="no-cases-found">
            <div style="text-align: center; color: #888; padding: 40px; background: #2a2a2a; border-radius: 8px;">
                <h3 style="color: #ffc107; margin-bottom: 15px;">No Active Cases Found</h3>
                <p>No active <?php echo htmlspecialchars($caseType); ?> cases found in the system.</p>
                <p style="font-size: 14px; margin-top: 10px;">Please check if:</p>
                <ul style="text-align: left; display: inline-block; margin: 10px 0;">
                    <li>Cases exist in the case management system</li>
                    <li>Cases have status: Open, Under Investigation, or Court Action Pending</li>
                    <li>Case type matches exactly</li>
                </ul>
                <div style="margin-top: 20px;">
                    <a href="initiate-protective-action.php" class="btn-cancel" style="margin-right: 10px;">← Back to Form</a>
                    <a href="case-management.php" class="btn-submit" style="background: #6c757d;">View All Cases</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radioButtons = document.querySelectorAll('.person-radio');
    const continueBtn = document.getElementById('continueBtn');
    
    // Simple radio button change handler
    radioButtons.forEach(radio => {
        radio.addEventListener('click', function() {
            continueBtn.disabled = false;
        });
    });
    
    // Simple form validation
    const form = document.getElementById('personForm');
    form.addEventListener('submit', function(e) {
        const selected = document.querySelector('input[name="selected_case"]:checked');
        if (!selected) {
            e.preventDefault();
            alert('Please select a case first.');
            return false;
        }
        return true;
    });
});
</script>

<style>
.confidentiality-alert {
    background: #dc3545;
    color: white;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 24px;
    text-align: center;
    font-weight: 500;
}

.person-selection-form {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    border: 1px solid #3a3a3a;
}

.person-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.person-card {
    display: flex;
    align-items: flex-start;
    background: #333333;
    border-radius: 8px;
    padding: 20px;
    border: 2px solid transparent;
    transition: all 0.2s;
    cursor: pointer;
    position: relative;
}

.person-card:hover {
    border-color: #3b82f6;
    background: #3a3a3a;
    transform: translateY(-1px);
}

.person-card:has(.person-radio:checked) {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.person-radio {
    margin-right: 16px;
    margin-top: 4px;
    transform: scale(1.2);
    accent-color: #3b82f6;
}

.person-info {
    flex: 1;
    cursor: pointer;
}

.person-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    flex-wrap: wrap;
    gap: 10px;
}

.person-name {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
}

.person-role {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.person-role.victim {
    background: #dc3545;
    color: white;
}

.person-details {
    display: flex;
    gap: 20px;
    color: #b8c5ff;
    font-size: 14px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.risk-level {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.risk-level.high-risk {
    background: #dc3545;
    color: white;
}

.risk-level.medium-risk {
    background: #ffc107;
    color: #000;
}

.risk-level.low-risk {
    background: #28a745;
    color: white;
}

.case-description {
    color: #cccccc;
    font-size: 14px;
    margin-top: 10px;
    line-height: 1.4;
    padding: 8px;
    background: rgba(255,255,255,0.05);
    border-radius: 4px;
    border-left: 3px solid #3b82f6;
}

.warning-banner {
    background: #dc3545;
    color: white;
    padding: 16px 20px;
    border-radius: 8px;
    margin: 24px 0;
    font-weight: 500;
    text-align: center;
    border-left: 4px solid #ff6b6b;
}

.form-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid #3a3a3a;
}

.btn-cancel, .btn-submit {
    padding: 14px 28px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.2s;
    min-width: 180px;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
    color: white;
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

.btn-submit:disabled {
    background: #495057;
    cursor: not-allowed;
    transform: none;
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

.no-cases-found {
    text-align: center;
    padding: 20px;
}

.no-cases-found ul {
    text-align: left;
    display: inline-block;
    margin: 15px 0;
}

.no-cases-found li {
    margin-bottom: 5px;
    color: #cccccc;
}
</style>

<?php
function getRiskLevelClass($priority) {
    switch (strtolower($priority)) {
        case 'urgent':
        case 'high':
            return 'high-risk';
        case 'medium':
        case 'mild':
            return 'medium-risk';
        case 'low':
        case 'common':
            return 'low-risk';
        default:
            return 'medium-risk';
    }
}

require_once 'includes/footer.php';
?>