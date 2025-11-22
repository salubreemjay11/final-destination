<?php
$pageTitle = 'Case Registration - Orphanfare';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate case ID
    $caseId = generateId('CS', 'cases', 'case_id');

    $createdDate = date('Y-m-d'); // This is the fix!
    
    // Insert case data
    $stmt = $pdo->prepare("
        INSERT INTO cases (
            case_id, case_type, child_name, child_age, child_gender, current_location,
            birth_date, birth_place, educational_attention, contact_number, reported_by,
            reporter_relation, reporter_phone, reporter_email, expected_date, description,
            priority, investigator, status, created_date, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $caseId,
        $_POST['caseType'],
        trim($_POST['childName']),
        intval($_POST['age']),
        $_POST['gender'],
        trim($_POST['currentLocation'] ?? ''),
        $_POST['birthDate'] ?: null,
        trim($_POST['birthPlace'] ?? ''),
        trim($_POST['educationalAttention'] ?? ''),
        trim($_POST['contactNumber'] ?? ''),
        trim($_POST['reportedBy']),
        trim($_POST['reporterRelation'] ?? ''),
        trim($_POST['reporterPhone'] ?? ''),
        trim($_POST['reporterEmail'] ?? ''),
        $_POST['expectedDate'],
        trim($_POST['caseDescription']),
        $_POST['priority'],
        $_POST['investigator'],
        'Open',
        $createdDate, 
        $currentUser['id'] // Use actual user ID instead of hardcoded value
    ]);
    
    if ($result) {
        logActivity($currentUser['id'], 'Case Created', 'cases', $caseId);
        echo "<script>
            alert('Case successfully created and assigned to investigator!');
            window.location.href = 'case-management.php';
        </script>";
        exit;
    } else {
        $error = "Failed to create case. Please try again.";
    }
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Case Registration</h1>
        <button class="btn btn-secondary" onclick="window.location.href='case-management.php'">← Back to Cases</button>
    </div>

    <div class="confidentiality-alert">
        <p>Confidentiality Notice: All case information is strictly confidential and protected under child welfare laws.</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="case-form">
        <!-- Case Type Selection -->
        <div class="form-group">
            <label class="form-label">Case Type *</label>
            <select name="caseType" class="form-select" required>
                <option value="">Select Case Type</option>
                <option value="Physical Abuse">Physical Abuse</option>
                <option value="Sexual Abuse">Sexual Abuse</option>
                <option value="Neglect">Neglect</option>
                <option value="Abandonment">Abandonment</option>
                <option value="Exploitation">Exploitation</option>
                <option value="Special Laws">Special Laws</option>
            </select>
        </div>

        <!-- Priority Level -->
        <div class="priority-section">
            <label class="priority-label">Priority Level *</label>
            <div class="priority-buttons">
                <button type="button" class="priority-btn urgent" onclick="setPriority('urgent', this)">
                    URGENT - Immediate Action Required
                </button>
                <button type="button" class="priority-btn mild" onclick="setPriority('mild', this)">
                    MILD - Standard Processing
                </button>
                <button type="button" class="priority-btn common" onclick="setPriority('common', this)">
                    COMMON - Routine Processing
                </button>
            </div>
            <input type="hidden" name="priority" id="priorityInput" required>
        </div>

        <!-- Child Information -->
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="childName">Child's Full Name *</label>
                <input type="text" id="childName" name="childName" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="age">Age *</label>
                <input type="number" id="age" name="age" class="form-input" min="0" max="18" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="gender">Gender *</label>
                <select id="gender" name="gender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="currentLocation">Current Location</label>
                <input type="text" id="currentLocation" name="currentLocation" class="form-input" placeholder="e.g., Barangay 123, City">
            </div>

            <div class="form-group">
                <label class="form-label" for="birthDate">Date of Birth</label>
                <input type="date" id="birthDate" name="birthDate" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="birthPlace">Place of Birth</label>
                <input type="text" id="birthPlace" name="birthPlace" class="form-input" placeholder="e.g., Hospital, City">
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="contactNumber">Contact Number</label>
                <input type="tel" id="contactNumber" name="contactNumber" class="form-input" placeholder="+63 XXX XXX XXXX">
            </div>

            <div class="form-group">
                <label class="form-label" for="educationalAttention">Educational Attention</label>
                <input type="text" id="educationalAttention" name="educationalAttention" class="form-input" placeholder="e.g., Grade 5, Special Education Needs">
            </div>
        </div>

        <!-- Reporter Information -->
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="reportedBy">Reported By *</label>
                <input type="text" id="reportedBy" name="reportedBy" class="form-input" required placeholder="Full name of reporter">
            </div>

            <div class="form-group">
                <label class="form-label" for="reporterRelation">Relation to Child</label>
                <input type="text" id="reporterRelation" name="reporterRelation" class="form-input" placeholder="e.g., Teacher, Neighbor, Relative">
            </div>

            <div class="form-group">
                <label class="form-label" for="reporterPhone">Reporter Phone</label>
                <input type="tel" id="reporterPhone" name="reporterPhone" class="form-input" placeholder="+63 XXX XXX XXXX">
            </div>

            <div class="form-group">
                <label class="form-label" for="reporterEmail">Reporter Email</label>
                <input type="email" id="reporterEmail" name="reporterEmail" class="form-input" placeholder="reporter@email.com">
            </div>
        </div>

        <!-- Case Details -->
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label" for="expectedDate">Expected Date *</label>
                <input type="date" id="expectedDate" name="expectedDate" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="investigator">Assigned Investigator *</label>
                <select id="investigator" name="investigator" class="form-select" required>
                    <option value="">Select Investigator</option>
                    <option value="john-doe">Officer John Doe</option>
                    <option value="jane-smith">Officer Jane Smith</option>
                    <option value="mike-johnson">Officer Mike Johnson</option>
                    <option value="sarah-wilson">Officer Sarah Wilson</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="caseDescription">Case Description *</label>
            <textarea id="caseDescription" name="caseDescription" class="form-textarea" rows="6" required 
                      placeholder="Provide detailed description of the case, circumstances, and any relevant information..."></textarea>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="cancelForm()">Cancel</button>
            <button type="submit" class="btn-submit">Create Case</button>
        </div>
    </form>
</main>

<style>
.case-form {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.priority-section {
    margin-bottom: 24px;
}

.priority-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 12px;
}

.priority-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.priority-btn {
    flex: 1;
    min-width: 200px;
    padding: 16px;
    border: 2px solid #3a3a3a;
    border-radius: 8px;
    background: #333;
    color: white;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    text-align: center;
}

.priority-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.priority-btn.active {
    border-width: 3px;
    font-weight: 600;
}

.priority-btn.urgent {
    border-color: #dc3545;
    background: rgba(220, 53, 69, 0.1);
}

.priority-btn.urgent.active {
    background: rgba(220, 53, 69, 0.2);
    border-color: #dc3545;
}

.priority-btn.mild {
    border-color: #ffc107;
    background: rgba(255, 193, 7, 0.1);
}

.priority-btn.mild.active {
    background: rgba(255, 193, 7, 0.2);
    border-color: #ffc107;
}

.priority-btn.common {
    border-color: #17a2b8;
    background: rgba(23, 162, 184, 0.1);
}

.priority-btn.common.active {
    background: rgba(23, 162, 184, 0.2);
    border-color: #17a2b8;
}

.alert-error {
    background: #dc3545;
    color: white;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
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
    min-height: 120px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
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
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit:hover {
    background: #2563eb;
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
let selectedPriority = null;

function setPriority(priority, button) {
    // Remove active class from all buttons
    document.querySelectorAll('.priority-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    button.classList.add('active');
    
    // Set the priority value
    selectedPriority = priority;
    document.getElementById('priorityInput').value = priority;
}

function cancelForm() {
    if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
        window.location.href = 'case-management.php';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.case-form');
    
    form.addEventListener('submit', function(e) {
        if (!selectedPriority) {
            e.preventDefault();
            alert('Please select a priority level.');
            return;
        }
        
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                valid = false;
                field.style.borderColor = '#dc3545';
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!valid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>