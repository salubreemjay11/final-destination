<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Add New Child & Case - Orphanfare';
require_once 'includes/header.php';

// Initialize variables
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle photo upload
        $photoPath = 'public/placeholder.jpg';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/children/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
            }
            
            if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File size must be less than 5MB');
            }
            
            $fileName = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                $photoPath = $uploadPath;
            }
        }
        
        // Validate required fields for child
        $requiredChild = ['name', 'age', 'gender', 'entryDate'];
        foreach ($requiredChild as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill in all required child information fields");
            }
        }
        
        // Validate required fields for case (if case is being created)
        if (isset($_POST['create_case']) && $_POST['create_case'] === 'yes') {
            $requiredCase = ['caseType', 'priority', 'caseDescription', 'reportedBy'];
            foreach ($requiredCase as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required case information fields");
                }
            }
        }
        
        // Generate child ID
        $childId = generateId('CH', 'children', 'child_id');
        
        // Insert child data
        $stmt = $pdo->prepare("
            INSERT INTO children (
                child_id, name, age, gender, date_of_birth, entry_date, status, 
                address, civil_status, health_status, allergies, emergency_contact, 
                contact_phone, problem_description, notes, photo_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $childResult = $stmt->execute([
            $childId,
            trim($_POST['name']),
            intval($_POST['age']),
            $_POST['gender'],
            $_POST['dateOfBirth'] ?: null,
            $_POST['entryDate'],
            $_POST['status'] ?? 'In Care',
            trim($_POST['address'] ?? ''),
            trim($_POST['civilStatus'] ?? ''),
            trim($_POST['healthStatus'] ?? 'Good'),
            trim($_POST['allergies'] ?? ''),
            trim($_POST['emergencyContact'] ?? ''),
            trim($_POST['contactPhone'] ?? ''),
            trim($_POST['problemDescription'] ?? ''),
            trim($_POST['notes'] ?? ''),
            $photoPath
        ]);
        
        if ($childResult) {
            logActivity($currentUser['id'], 'Child Added', 'children', $childId);
            
            // If case should be created
            if (isset($_POST['create_case']) && $_POST['create_case'] === 'yes') {
                // Generate case ID
                $caseId = generateId('CS', 'cases', 'case_id');
                
                // Insert case data
                $stmt = $pdo->prepare("
                    INSERT INTO cases (
                        case_id, case_type, child_name, child_age, child_gender, current_location,
                        birth_date, birth_place, educational_attention, contact_number, reported_by,
                        reporter_relation, reporter_phone, reporter_email, expected_date, description,
                        priority, investigator, status, created_date, created_by, linked_child_id
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $caseResult = $stmt->execute([
                    $caseId,
                    $_POST['caseType'],
                    trim($_POST['name']), // Same name from child form
                    intval($_POST['age']), // Same age from child form
                    $_POST['gender'], // Same gender from child form
                    trim($_POST['address'] ?? ''), // Use address from child form
                    $_POST['dateOfBirth'] ?: null, // Same birth date from child form
                    trim($_POST['birthPlace'] ?? ''),
                    trim($_POST['educationalAttention'] ?? ''),
                    trim($_POST['contactPhone'] ?? ''), // Use contact phone from child form
                    trim($_POST['reportedBy']),
                    trim($_POST['reporterRelation'] ?? ''),
                    trim($_POST['reporterPhone'] ?? ''),
                    trim($_POST['reporterEmail'] ?? ''),
                    $_POST['expectedDate'] ?: date('Y-m-d'),
                    trim($_POST['caseDescription']),
                    $_POST['priority'],
                    $_POST['investigator'] ?? 'john-doe',
                    'Open',
                    date('Y-m-d'),
                    $currentUser['id'],
                    $childId // Link to the child record
                ]);
                
                if ($caseResult) {
                    logActivity($currentUser['id'], 'Case Created', 'cases', $caseId);
                    $success = "Child and Case successfully created! Child ID: $childId, Case ID: $caseId";
                } else {
                    // Child was created but case failed - still show success for child
                    $success = "Child successfully created but case creation failed. Child ID: $childId";
                }
            } else {
                $success = "Child successfully created! Child ID: $childId";
            }
            
            // Redirect after success
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'child-management.php';
                }, 3000);
            </script>";
            
        } else {
            throw new Exception('Failed to add child to the database');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Child Intake & Case Registration</h1>
        <button class="btn btn-secondary" onclick="window.location.href='child-management.php'">← Back to Children</button>
    </div>

    <div class="confidentiality-alert">
        <p>Confidentiality Notice: All information collected is strictly confidential and protected under child welfare laws.</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <br><small>Redirecting to child management in 3 seconds...</small>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="combined-form">
        <!-- Toggle for Case Creation -->
        <div class="form-group">
            <label class="form-label">Also Create Case for this Child?</label>
            <div class="toggle-buttons">
                <button type="button" class="toggle-btn active" id="toggleYes" onclick="toggleCaseCreation(true)">Yes, Create Case</button>
                <button type="button" class="toggle-btn" id="toggleNo" onclick="toggleCaseCreation(false)">No, Just Add Child</button>
            </div>
            <input type="hidden" name="create_case" id="createCaseInput" value="yes">
        </div>

        <!-- CHILD INFORMATION SECTION -->
        <div class="form-section">
            <h3 class="section-title">Child Information</h3>
            
            <!-- Photo Upload -->
            <div class="form-group">
                <label class="form-label">Child Photo</label>
                <div class="photo-upload-area" onclick="document.getElementById('photoInput').click()">
                    <div class="photo-upload-text">Click to upload photo</div>
                    <div class="photo-upload-subtext">Maximum file size: 5MB (JPG, PNG)</div>
                </div>
                <input type="file" id="photoInput" name="photo" accept="image/*" style="display: none;" onchange="handlePhotoUpload(event)">
            </div>

            <!-- Basic Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-input" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="age">Age *</label>
                    <input type="number" id="age" name="age" class="form-input" min="0" max="18" required value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-select" required>
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="dateOfBirth">Date of Birth</label>
                    <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" value="<?php echo htmlspecialchars($_POST['dateOfBirth'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="entryDate">Entry Date *</label>
                    <input type="date" id="entryDate" name="entryDate" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="In Care" <?php echo ($_POST['status'] ?? '') === 'In Care' ? 'selected' : ''; ?>>In Care</option>
                        <option value="Adoptable" <?php echo ($_POST['status'] ?? '') === 'Adoptable' ? 'selected' : ''; ?>>Adoptable</option>
                        <option value="Adopted" <?php echo ($_POST['status'] ?? '') === 'Adopted' ? 'selected' : ''; ?>>Adopted</option>
                        <option value="Reintegrated" <?php echo ($_POST['status'] ?? '') === 'Reintegrated' ? 'selected' : ''; ?>>Reintegrated</option>
                    </select>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="address">Address</label>
                    <textarea id="address" name="address" class="form-textarea" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="civilStatus">Civil Status</label>
                    <input type="text" id="civilStatus" name="civilStatus" class="form-input" value="<?php echo htmlspecialchars($_POST['civilStatus'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="emergencyContact">Emergency Contact</label>
                    <input type="text" id="emergencyContact" name="emergencyContact" class="form-input" value="<?php echo htmlspecialchars($_POST['emergencyContact'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="contactPhone">Contact Phone</label>
                    <input type="tel" id="contactPhone" name="contactPhone" class="form-input" value="<?php echo htmlspecialchars($_POST['contactPhone'] ?? ''); ?>">
                </div>
            </div>

            <!-- Health Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="healthStatus">Health Status</label>
                    <input type="text" id="healthStatus" name="healthStatus" class="form-input" value="<?php echo htmlspecialchars($_POST['healthStatus'] ?? 'Good'); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="allergies">Known Allergies</label>
                    <textarea id="allergies" name="allergies" class="form-textarea" rows="2"><?php echo htmlspecialchars($_POST['allergies'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="form-group">
                <label class="form-label" for="problemDescription">Problem Description / Reason for Care</label>
                <textarea id="problemDescription" name="problemDescription" class="form-textarea" rows="4"><?php echo htmlspecialchars($_POST['problemDescription'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" class="form-textarea" rows="3"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- CASE INFORMATION SECTION (Initially visible) -->
        <div id="caseSection" class="form-section">
            <h3 class="section-title">Case Information</h3>
            
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
                    <button type="button" class="priority-btn urgent" onclick="setPriority('urgent', this)">URGENT - Immediate Action Required</button>
                    <button type="button" class="priority-btn mild" onclick="setPriority('mild', this)">MILD - Standard Processing</button>
                    <button type="button" class="priority-btn common" onclick="setPriority('common', this)">COMMON - Routine Processing</button>
                </div>
                <input type="hidden" name="priority" id="priorityInput" required>
            </div>

            <!-- Additional Case Fields -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="birthPlace">Place of Birth</label>
                    <input type="text" id="birthPlace" name="birthPlace" class="form-input" value="<?php echo htmlspecialchars($_POST['birthPlace'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="educationalAttention">Educational Attention</label>
                    <input type="text" id="educationalAttention" name="educationalAttention" class="form-input" value="<?php echo htmlspecialchars($_POST['educationalAttention'] ?? ''); ?>">
                </div>
            </div>

            <!-- Case Details -->
            <div class="form-group">
                <label class="form-label" for="caseDescription">Case Description *</label>
                <textarea id="caseDescription" name="caseDescription" class="form-textarea" rows="6" required 
                          placeholder="Provide detailed description of the case, circumstances, and any relevant information..."><?php echo htmlspecialchars($_POST['caseDescription'] ?? ''); ?></textarea>
            </div>

            <!-- Reporter Information -->
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="reportedBy">Reported By *</label>
                    <input type="text" id="reportedBy" name="reportedBy" class="form-input" required value="<?php echo htmlspecialchars($_POST['reportedBy'] ?? ''); ?>" placeholder="Full name of reporter">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reporterRelation">Relation to Child</label>
                    <input type="text" id="reporterRelation" name="reporterRelation" class="form-input" value="<?php echo htmlspecialchars($_POST['reporterRelation'] ?? ''); ?>" placeholder="e.g., Teacher, Neighbor, Relative">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reporterPhone">Reporter Phone</label>
                    <input type="tel" id="reporterPhone" name="reporterPhone" class="form-input" value="<?php echo htmlspecialchars($_POST['reporterPhone'] ?? ''); ?>" placeholder="+63 XXX XXX XXXX">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reporterEmail">Reporter Email</label>
                    <input type="email" id="reporterEmail" name="reporterEmail" class="form-input" value="<?php echo htmlspecialchars($_POST['reporterEmail'] ?? ''); ?>" placeholder="reporter@email.com">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="expectedDate">Expected Date</label>
                    <input type="date" id="expectedDate" name="expectedDate" class="form-input" value="<?php echo htmlspecialchars($_POST['expectedDate'] ?? date('Y-m-d')); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="investigator">Assigned Investigator</label>
                    <select id="investigator" name="investigator" class="form-select">
                        <option value="">Select Investigator</option>
                        <option value="john-doe">Officer John Doe</option>
                        <option value="jane-smith">Officer Jane Smith</option>
                        <option value="mike-johnson">Officer Mike Johnson</option>
                        <option value="sarah-wilson">Officer Sarah Wilson</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="cancelForm()">Cancel</button>
            <button type="submit" class="btn-submit">Submit</button>
        </div>
    </form>
</main>

<script>
let selectedPriority = null;

function toggleCaseCreation(createCase) {
    const caseSection = document.getElementById('caseSection');
    const toggleYes = document.getElementById('toggleYes');
    const toggleNo = document.getElementById('toggleNo');
    const createCaseInput = document.getElementById('createCaseInput');
    
    if (createCase) {
        caseSection.style.display = 'block';
        toggleYes.classList.add('active');
        toggleNo.classList.remove('active');
        createCaseInput.value = 'yes';
        
        // Make case fields required
        document.querySelectorAll('#caseSection [required]').forEach(field => {
            field.required = true;
        });
    } else {
        caseSection.style.display = 'none';
        toggleYes.classList.remove('active');
        toggleNo.classList.add('active');
        createCaseInput.value = 'no';
        
        // Remove required from case fields
        document.querySelectorAll('#caseSection [required]').forEach(field => {
            field.required = false;
        });
    }
}

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

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const uploadArea = document.querySelector('.photo-upload-area');
            uploadArea.innerHTML = `
                <img src="${e.target.result}" alt="Child Photo Preview" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                <div class="photo-upload-text" style="margin-top: 10px; color: #4a90e2;">Photo uploaded successfully</div>
                <div class="photo-upload-subtext" style="color: #888;">Click to change photo</div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

function cancelForm() {
    if (confirm('Are you sure you want to cancel? All entered data will be lost.')) {
        window.location.href = 'child-management.php';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.combined-form');
    
    form.addEventListener('submit', function(e) {
        const createCase = document.getElementById('createCaseInput').value === 'yes';
        
        if (createCase && !selectedPriority) {
            e.preventDefault();
            alert('Please select a priority level for the case.');
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

<style>
.combined-form {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #3a3a3a;
}

.section-title {
    color: #b8c5ff;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3a3a3a;
}

.toggle-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.toggle-btn {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #3a3a3a;
    border-radius: 8px;
    background: #333;
    color: #ccc;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}

.toggle-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.toggle-btn:hover {
    transform: translateY(-2px);
}

.alert-success {
    background: #d4edda;
    color: #155724;
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
    border: 1px solid #c3e6cb;
}

.intake-form {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 24px;
}

.photo-upload-area {
    border: 2px dashed #3a3a3a;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #333333;
}

.photo-upload-area:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.photo-upload-text {
    color: #3b82f6;
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 8px;
}

.photo-upload-subtext {
    color: #888;
    font-size: 14px;
}
</style>

<?php require_once 'includes/footer.php'; ?>