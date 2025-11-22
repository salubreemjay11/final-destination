<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$pageTitle = 'New Foster - Orphanfare';
require_once 'includes/header.php';

// Load Custom Field Manager for foster module
$fieldManager = null;
$fosterCustomFields = [];
$existingFosterCustomValues = [];

try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
    } elseif (file_exists('includes/CustomFieldManager.php')) {
        require_once 'includes/CustomFieldManager.php';
    } else {
        throw new Exception('CustomFieldManager.php not found');
    }
    
    $fieldManager = new CustomFieldManager($pdo);
    
    // Fix field types if needed
    try {
        $fixStmt = $pdo->prepare("UPDATE custom_fields SET field_type = 'select' WHERE (field_type IS NULL OR field_type = '') AND field_options IS NOT NULL AND field_options != ''");
        $fixStmt->execute();
        error_log("Fixed field types for foster fields with options");
    } catch (Exception $e) {
        error_log("Foster field type fix error: " . $e->getMessage());
    }
    
    // Load custom fields for foster module
    $fosterCustomFields = $fieldManager->getModuleFields('foster');
    error_log("Foster custom fields loaded: " . count($fosterCustomFields));
    
    // Load existing custom field values for edit mode
    if (isset($fosterId) && !empty($fosterId)) {
        $existingFosterCustomValues = $fieldManager->getFieldValues($fosterId, 'foster');
        error_log("Existing foster custom values: " . print_r($existingFosterCustomValues, true));
    }
    
} catch (Exception $e) {
    error_log("Foster Custom Field Manager Error: " . $e->getMessage());
    $customFieldsError = "Custom fields are temporarily unavailable. Please contact administrator.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        error_log("=== FORM SUBMISSION STARTED ===");
        
        // Generate foster ID
        $fosterId = generateId('FT', 'foster_parents', 'foster_id');
        error_log("Generated Foster ID: " . $fosterId);

        // Process form data
        $familyComposition = [];
        if (isset($_POST['family_members'])) {
            foreach ($_POST['family_members'] as $index => $member) {
                if (!empty($member['name'])) {
                    $familyComposition[] = [
                        'name' => $member['name'],
                        'relationship' => $member['relationship'],
                        'age' => $member['age'],
                        'gender' => $member['gender'],
                        'civil_status' => $member['civil_status'],
                        'education' => $member['education'],
                        'occupation_income' => $member['occupation_income']
                    ];
                }
            }
        }

        // Insert basic foster record first
        $simpleStmt = $pdo->prepare("
        INSERT INTO foster_parents (
            foster_id, name, age, gender, civil_status, contact_number, address, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $simpleResult = $simpleStmt->execute([
            $fosterId,
            $_POST['name'] ?? '',
            intval($_POST['age'] ?? 0),
            $_POST['gender'] ?? '',
            $_POST['civil_status'] ?? '',
            $_POST['contact_number'] ?? '',
            $_POST['address'] ?? '',
            $_POST['status'] ?? 'Pending'
        ]);
        
        if ($simpleResult) {
            error_log("SUCCESS: Basic foster record created with ID: " . $fosterId);
            
            // Prepare update data
            $updateData = [
                'birth_date' => !empty($_POST['birth_date']) ? $_POST['birth_date'] : null,
                'birth_place' => !empty($_POST['birth_place']) ? $_POST['birth_place'] : null,
                'educational_attainment' => !empty($_POST['educational_attainment']) ? $_POST['educational_attainment'] : null,
                'religion' => !empty($_POST['religion']) ? $_POST['religion'] : null,
                'email' => !empty($_POST['email']) ? $_POST['email'] : null,
                'occupation' => !empty($_POST['occupation']) ? $_POST['occupation'] : null,
                'salary_multiplier' => !empty($_POST['salary_multiplier']) ? $_POST['salary_multiplier'] : null,
                'monthly_income' => !empty($_POST['monthly_income']) ? $_POST['monthly_income'] : null,
                'income_source' => !empty($_POST['income_source']) ? $_POST['income_source'] : null,
                'family_planning' => !empty($_POST['family_planning']) ? $_POST['family_planning'] : null,
                'adoption_awareness' => !empty($_POST['adoption_awareness']) ? $_POST['adoption_awareness'] : null,
                'parenting_approach' => !empty($_POST['parenting_approach']) ? $_POST['parenting_approach'] : null,
                'age_preference' => !empty($_POST['age_preference']) ? $_POST['age_preference'] : null,
                'gender_preference' => !empty($_POST['gender_preference']) ? $_POST['gender_preference'] : null,
                'interests' => !empty($_POST['interests']) ? $_POST['interests'] : null,
                'personality_traits' => !empty($_POST['personality_traits']) ? $_POST['personality_traits'] : null,
                'experience_level' => !empty($_POST['experience_level']) ? $_POST['experience_level'] : null,
                'problem_presented' => !empty($_POST['problem_presented']) ? $_POST['problem_presented'] : null,
                'assessment_recommendation' => !empty($_POST['assessment_recommendation']) ? $_POST['assessment_recommendation'] : null,
                'family_composition' => !empty($familyComposition) ? json_encode($familyComposition) : null,
                'assessment_date' => !empty($_POST['assessment_date']) ? $_POST['assessment_date'] : null,
                'social_worker_name' => !empty($_POST['social_worker_name']) ? $_POST['social_worker_name'] : null,
                'psychological_evaluation' => !empty($_POST['psychological_evaluation']) ? $_POST['psychological_evaluation'] : null,
                'psychologist_notes' => !empty($_POST['psychologist_notes']) ? $_POST['psychologist_notes'] : null,
                'overall_assessment' => !empty($_POST['overall_assessment']) ? $_POST['overall_assessment'] : null,
                'dswd_referral_date' => !empty($_POST['dswd_referral_date']) ? $_POST['dswd_referral_date'] : null,
                'capacity' => intval($_POST['capacity'] ?? 1),
                'current_children' => intval($_POST['current_children'] ?? 0),
                'notes' => !empty($_POST['notes']) ? $_POST['notes'] : null
            ];
            
            // Build dynamic UPDATE query
            $updateColumns = [];
            $updateValues = [];
            
            foreach ($updateData as $column => $value) {
                if ($value !== null) {
                    $updateColumns[] = "$column = ?";
                    $updateValues[] = $value;
                }
            }
            
            // Add custom fields
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $fieldName = str_replace('custom_field_', '', $key);
                    
                    // Handle checkbox arrays
                    $processedValue = $value;
                    if (is_array($value)) {
                        $processedValue = implode(',', array_filter($value));
                    }
                    
                    // Check if this is a foster field
                    foreach ($fosterCustomFields as $field) {
                        if ($field['field_name'] === $fieldName) {
                            $dbColumn = 'cf_' . $fieldName;
                            $updateColumns[] = "$dbColumn = ?";
                            $updateValues[] = trim($processedValue);
                            break;
                        }
                    }
                }
            }
            
            // Add the WHERE clause value
            $updateValues[] = $fosterId;
            
            if (!empty($updateColumns)) {
                // Build the dynamic SQL
                $updateSql = "UPDATE foster_parents SET " . implode(', ', $updateColumns) . " WHERE foster_id = ?";
                
                error_log("Foster UPDATE SQL: $updateSql");
                error_log("Update values: " . print_r($updateValues, true));
                
                $updateStmt = $pdo->prepare($updateSql);
                $updateResult = $updateStmt->execute($updateValues);
                
                if ($updateResult) {
                    error_log("SUCCESS: Foster details updated");
                    
                    // Save custom fields using field manager
                    if ($fieldManager) {
                        foreach ($_POST as $key => $value) {
                            if (strpos($key, 'custom_field_') === 0) {
                                $fieldName = str_replace('custom_field_', '', $key);
                                $processedValue = is_array($value) ? implode(',', array_filter($value)) : $value;
                                $fieldManager->saveFieldValue($fosterId, 'foster', $fieldName, $processedValue);
                            }
                        }
                    }
                    
                    // Handle file uploads
                    if (!empty($_FILES['documents']['name'][0])) {
                        handleDocumentUploads($fosterId);
                    }
                    
                    logActivity($currentUser['id'], 'Foster Parent Added', 'foster_parents', $fosterId);
                    
                    // Clear output buffer and redirect
                    ob_end_clean();
                    header('Location: foster-info.php?success=foster_added');
                    exit();
                    
                } else {
                    $errorInfo = $updateStmt->errorInfo();
                    throw new Exception("Update failed: " . ($errorInfo[2] ?? 'Unknown error'));
                }
            } else {
                // No additional fields to update, just redirect
                ob_end_clean();
                header('Location: foster-info.php?success=foster_added');
                exit();
            }
            
        } else {
            $errorInfo = $simpleStmt->errorInfo();
            throw new Exception("Basic insert failed: " . ($errorInfo[2] ?? 'Unknown error'));
        }
        
    } catch (Exception $e) {
        error_log("Foster parent addition error: " . $e->getMessage());
        $_SESSION['error_details'] = $e->getMessage();
        
        // Clear buffer and redirect with error
        ob_end_clean();
        header('Location: new-foster.php?error=add_failed');
        exit();
    }
}

function saveCustomFieldAlternative($recordId, $module, $fieldName, $value) {
    global $pdo;
    
    try {
        $tableMap = [
            'children' => 'children',
            'cases' => 'cases',
            'foster' => 'foster_parents',
            'donations' => 'donations',
            'inventory' => 'inventory',
            'users' => 'users'
        ];
        
        $tableName = $tableMap[$module] ?? null;
        $idColumn = [
            'children' => 'child_id',
            'cases' => 'case_id',
            'foster_parents' => 'foster_id',
            'donations' => 'donation_id',
            'inventory' => 'item_id',
            'users' => 'id'
        ][$tableName] ?? 'id';
        
        if (!$tableName || !$idColumn) {
            error_log("Alternative save: No table mapping found for module: $module");
            return false;
        }
        
        $dbColumn = 'cf_' . $fieldName;
        
        // Check if column exists
        $checkColumn = $pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
        $checkColumn->execute([$dbColumn]);
        $columnExists = $checkColumn->fetch();
        
        if (!$columnExists) {
            error_log("Alternative save: Column $dbColumn does not exist in $tableName");
            return false;
        }
        
        // Save the value
        $sql = "UPDATE `$tableName` SET `$dbColumn` = ? WHERE `$idColumn` = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$value, $recordId]);
        
        error_log("Alternative save result for $fieldName: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Alternative save error: " . $e->getMessage());
        return false;
    }
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

function handleDocumentUploads($fosterId) {
    global $pdo, $currentUser;
    
    $uploadDir = 'uploads/foster/' . $fosterId . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedFiles = [];
    
    foreach ($_FILES['documents']['name'] as $key => $name) {
        if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($name);
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);
            $uploadPath = $uploadDir . $newFileName;
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
            if (in_array($fileExtension, $allowedExtensions)) {
                if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $uploadPath)) {
                    // Insert into foster_documents table instead of documents table
                    $stmt = $pdo->prepare("
                        INSERT INTO foster_documents (foster_id, name, type, file_path, date_uploaded, uploaded_by) 
                        VALUES (?, ?, ?, ?, CURDATE(), ?)
                    ");
                    
                    $documentType = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'Photo' : 'Document';
                    $uploadedBy = $currentUser['full_name'] ?? 'System';
                    
                    try {
                        $stmt->execute([
                            $fosterId,
                            $fileName,
                            $documentType,
                            $uploadPath,
                            $uploadedBy
                        ]);
                        $uploadedFiles[] = [
                            'name' => $fileName,
                            'path' => $uploadPath,
                            'type' => $documentType
                        ];
                        error_log("Foster document uploaded successfully: " . $fileName);
                    } catch (Exception $e) {
                        error_log("Error saving foster document to database: " . $e->getMessage());
                    }
                }
            }
        }
    }
    
    return $uploadedFiles;
}

function getFosterDocuments($fosterId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM foster_documents 
            WHERE foster_id = ? 
            ORDER BY date_uploaded DESC
        ");
        $stmt->execute([$fosterId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching foster documents: " . $e->getMessage());
        return [];
    }
}

?>

<main class="main-content">
    <div class="content">
        <h1 class="page-title">New Foster</h1>

        <!-- Success/Error Notifications -->
        <?php if ($success): ?>
            <div class="notification success show">
                <div class="notification-icon">✓</div>
                <div class="notification-content">
                    <div class="notification-title">Success!</div>
                    <div class="notification-message">
                        <?php 
                        switch($success) {
                            case 'foster_added':
                                echo "Foster parent added successfully!";
                                break;
                            default:
                                echo "Operation completed successfully!";
                        }
                        ?>
                    </div>
                </div>
                <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="notification error show">
                <div class="notification-icon">⚠</div>
                <div class="notification-content">
                    <div class="notification-title">Error!</div>
                    <div class="notification-message">
                        <?php 
                        switch($error) {
                            case 'add_failed':
                                echo "Failed to add foster parent. Please try again.";
                                break;
                            default:
                                echo "An error occurred. Please try again.";
                        }
                        ?>
                    </div>
                </div>
                <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="new-foster.php" enctype="multipart/form-data">
                    <h2 class="section-title">Personal Details</h2>
                    <div class="form-grid">
                        <!-- Personal Details -->

                            
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Age *</label>
                                <input type="number" name="age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Birth Date</label>
                                <input type="date" name="birth_date" value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Birth Place</label>
                                <input type="text" name="birth_place" value="<?php echo htmlspecialchars($_POST['birth_place'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Educational Attainment</label>
                                <input type="text" name="educational_attainment" value="<?php echo htmlspecialchars($_POST['educational_attainment'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Religion</label>
                                <input type="text" name="religion" value="<?php echo htmlspecialchars($_POST['religion'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Gender *</label>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo ($_POST['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Civil Status *</label>
                                <select name="civil_status" required>
                                    <option value="">Select Status</option>
                                    <option value="Single" <?php echo ($_POST['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo ($_POST['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="Widowed" <?php echo ($_POST['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                    <option value="Divorced" <?php echo ($_POST['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Contact No. *</label>
                                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Current Address *</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Occupation</label>
                                <input type="text" name="occupation" value="<?php echo htmlspecialchars($_POST['occupation'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Salary Multiplier</label>
                                <input type="text" name="salary_multiplier" value="<?php echo htmlspecialchars($_POST['salary_multiplier'] ?? ''); ?>" placeholder="e.g., X2">
                            </div>

                            <div class="form-group">
                                <label>Monthly Income</label>
                                <input type="text" name="monthly_income" value="<?php echo htmlspecialchars($_POST['monthly_income'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Source of Income</label>
                                <input type="text" name="income_source" value="<?php echo htmlspecialchars($_POST['income_source'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" required>
                                    <option value="Pending" <?php echo ($_POST['status'] ?? 'Pending') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Active" <?php echo ($_POST['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Inactive" <?php echo ($_POST['status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Capacity (Max Children)</label>
                                <input type="number" name="capacity" value="<?php echo htmlspecialchars($_POST['capacity'] ?? 1); ?>" min="1" max="10">
                            </div>

                            <div class="form-group">
                                <label>Family Planning</label>
                                <textarea name="family_planning"><?php echo htmlspecialchars($_POST['family_planning'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Adoption Awareness</label>
                                <textarea name="adoption_awareness"><?php echo htmlspecialchars($_POST['adoption_awareness'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Parenting Approach</label>
                                <textarea name="parenting_approach"><?php echo htmlspecialchars($_POST['parenting_approach'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Age Preference</label>
                                <input type="text" name="age_preference" value="<?php echo htmlspecialchars($_POST['age_preference'] ?? ''); ?>" placeholder="e.g., 0-10 years">
                            </div>

                            <div class="form-group">
                                <label>Gender Preference</label>
                                <select name="gender_preference">
                                    <option value="No Preference" <?php echo ($_POST['gender_preference'] ?? '') === 'No Preference' ? 'selected' : ''; ?>>No preference</option>
                                    <option value="Male" <?php echo ($_POST['gender_preference'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo ($_POST['gender_preference'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Interests</label>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                                    <input type="text" id="interestInput" placeholder="Add interest (e.g., Music, Sports)" style="flex: 1;">
                                    <button type="button" onclick="addInterest()" style="padding: 8px 20px; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer;">Add</button>
                                </div>
                                <div class="tags" id="interestTags">
                                    <!-- Dynamic tags will be added here -->
                                </div>
                                <input type="hidden" name="interests" id="interestsHidden">
                            </div>

                            <div class="form-group">
                                <label>Personality Traits</label>
                                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 10px;">
                                    <input type="text" id="traitInput" placeholder="Add trait (e.g., Playful, Quiet)" style="flex: 1;">
                                    <button type="button" onclick="addTrait()" style="padding: 8px 20px; background-color: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer;">Add</button>
                                </div>
                                <div class="tags" id="traitTags">
                                    <!-- Dynamic tags will be added here -->
                                </div>
                                <input type="hidden" name="personality_traits" id="traitsHidden">
                            </div>

                            <div class="form-group">
                                <label>Experience Level</label>
                                <select name="experience_level">
                                    <option value="First-time">First-time adopters</option>
                                    <option value="Experienced">Experienced adopters</option>
                                    <option value="Fostered-before">Have fostered before</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Problem Presented</label>
                                <textarea name="problem_presented"><?php echo htmlspecialchars($_POST['problem_presented'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Assessment and Recommendation</label>
                                <textarea name="assessment_recommendation"><?php echo htmlspecialchars($_POST['assessment_recommendation'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                    </div>
                        

                    <!-- Right Column -->
                    <div>
                        <!-- Family Composition -->
                        <div class="form-section">
                            <h2 class="section-title">Family Composition</h2>
                            <div class="table-container">
                                <table id="familyTable">
                                    <thead>
                                        <tr>
                                            <th>Members</th>
                                            <th>Relationship</th>
                                            <th>Age</th>
                                            <th>Sex</th>
                                            <th>Civil Status</th>
                                            <th>Educational Attainment</th>
                                            <th>Occupational/ Monthly Income</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="familyMembers">
                                        <tr>
                                            <td><input type="text" name="family_members[0][name]"></td>
                                            <td><input type="text" name="family_members[0][relationship]"></td>
                                            <td><input type="text" name="family_members[0][age]"></td>
                                            <td><input type="text" name="family_members[0][gender]"></td>
                                            <td><input type="text" name="family_members[0][civil_status]"></td>
                                            <td><input type="text" name="family_members[0][education]"></td>
                                            <td><input type="text" name="family_members[0][occupation_income]"></td>
                                            <td><button type="button" onclick="removeFamilyMember(this)" class="btn-delete">Remove</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="button" onclick="addFamilyMember()" style="margin-top: 10px; padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Family Member</button>
                            </div>
                        </div>

                        <!-- Case Assessment -->
                        <h2 class="section-title">Case Assessment</h2>
                        <div class="form-grid">
                                
                                <div class="form-group">
                                    <label>Assessment Date</label>
                                    <input type="date" name="assessment_date" value="<?php echo htmlspecialchars($_POST['assessment_date'] ?? ''); ?>">
                                </div>

                            
                                <div class="form-group">
                                    <label>Social Worker Name</label>
                                    <input type="text" name="social_worker_name" value="<?php echo htmlspecialchars($_POST['social_worker_name'] ?? $currentUser['full_name'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Psychological Evaluation Status</label>
                                    <select name="psychological_evaluation">
                                        <option value="Pending">Pending</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Scheduled">Scheduled</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Psychologist's Notes</label>
                                    <input type="text" name="psychologist_notes" value="<?php echo htmlspecialchars($_POST['psychologist_notes'] ?? ''); ?>">
                                </div>

                                <div class="form-group">
                                    <label>Overall Assessment</label>
                                    <select name="overall_assessment">
                                        <option value="Recommended">Recommended</option>
                                        <option value="Not Recommended">Not Recommended</option>
                                        <option value="Conditional">Conditional</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Date Referred to DSWD</label>
                                    <input type="date" name="dswd_referral_date" value="<?php echo htmlspecialchars($_POST['dswd_referral_date'] ?? ''); ?>">
                                </div>
                        </div>

                        <!-- Supporting Documents -->
                        <div class="form-section" style="margin-top: 20px;">
                            <h2 class="section-title">Supporting Documents</h2>
                            
                            <!-- File Upload Area -->
                            <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                                <div class="upload-icon">⬆</div>
                                <div>Upload Document</div>
                                <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                    Click or drag files here<br>
                                    Supported: JPG, PNG, GIF, PDF, DOC, DOCX, TXT
                                </div>
                                <input type="file" id="fileInput" name="documents[]" multiple style="display: none;" onchange="handleFileUpload(event)">
                            </div>

                            <!-- Current Documents List -->
                            <div class="document-list" id="documentList">
                                <div style="text-align: center; padding: 20px; color: #888;">
                                    Documents can be uploaded after saving the foster profile.
                                </div>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div id="uploadProgress" style="display: none; margin-top: 10px;">
                                <div style="background: #333; border-radius: 10px; height: 6px;">
                                    <div id="progressBar" style="background: #3b82f6; height: 100%; width: 0%; border-radius: 10px; transition: width 0.3s;"></div>
                                </div>
                                <div id="progressText" style="text-align: center; font-size: 12px; color: #888; margin-top: 5px;"></div>
                            </div>
                        </div>
                            <?php if ($fieldManager && !empty($fosterCustomFields)): ?>
                            <!-- Custom Fields Tab -->
                            <div id="customTab" class="tab-content">
                                <div class="form-section">
                                    <h3>Additional Custom Fields</h3>
                                    <p class="help-text">These are additional fields that can be configured by the system administrator.</p>
                                    
                                    <!-- Debug Information (optional) -->
                                    <div class="alert alert-info" style="margin-bottom: 20px; display: none;" id="debugInfo">
                                        <h4>Custom Fields Debug Info</h4>
                                        <p><strong>Total Foster Fields:</strong> <?php echo count($fosterCustomFields); ?></p>
                                        
                                        <?php if (!empty($fosterCustomFields)): ?>
                                        <h5>Foster Fields Details:</h5>
                                        <ul>
                                            <?php foreach ($fosterCustomFields as $field): ?>
                                            <li>
                                                <strong><?php echo $field['field_name']; ?></strong> 
                                                (Type: <?php echo $field['field_type']; ?>) 
                                                - Required: <?php echo $field['is_required'] ? 'Yes' : 'No'; ?>
                                                - Options: <?php echo !empty($field['field_options']) ? json_encode($field['field_options']) : 'None'; ?>
                                                - Current Value: "<?php echo $existingFosterCustomValues[$field['field_name']] ?? 'Not set'; ?>"
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Foster Module Custom Fields -->
                                    <div class="custom-fields-section">
                                        <h4>Additional Foster Information</h4>
                                        <div class="form-grid">
                                            <?php foreach ($fosterCustomFields as $field): 
                                                $existingValue = $existingFosterCustomValues[$field['field_name']] ?? '';
                                                // Use underscore format for field names to match the processing logic
                                                echo str_replace(
                                                    'name="custom_field[' . $field['field_name'] . ']"',
                                                    'name="custom_field_' . $field['field_name'] . '"',
                                                    $fieldManager->renderField($field, $existingValue)
                                                );
                                            endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                <!-- Action Buttons -->
                <div class="button-group">
                    <button type="submit" class="btn-primary">Save Foster Profile</button>
                    <button type="button" class="btn-secondary" onclick="window.location.href='foster-info.php'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.document-item.uploading {
    opacity: 0.7;
    border-left: 4px solid #ffc107;
}

.document-item.uploaded {
    border-left: 4px solid #28a745;
}

.upload-progress {
    color: #3b82f6;
    font-weight: bold;
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

.custom-fields-section .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}

.custom-fields-section .form-group {
    margin-bottom: 15px;
}

.custom-fields-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #3a3a3a;
}

.custom-fields-section h4 {
    color: #3b82f6;
    margin-bottom: 20px;
    font-size: 18px;
}

.custom-field .form-label {
    color: #b8c5ff;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.custom-field .form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #3a3a3a;
    border-radius: 4px;
    background: #1a1a1a;
    color: white;
    font-size: 14px;
}

.custom-field .help-text {
    color: #888;
    font-size: 12px;
    font-style: italic;
    margin-top: 4px;
}

.custom-field .radio-option,
.custom-field .checkbox-option {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 8px;
    background: #2a2a2a;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
}

.custom-field .radio-option input,
.custom-field .checkbox-option input {
    margin-right: 8px;
    transform: scale(1.2);
}

.custom-field .radio-option label,
.custom-field .checkbox-option label {
    color: #cccccc;
    font-weight: 500;
    margin-bottom: 0;
    cursor: pointer;
}

.custom-field .radio-option:hover,
.custom-field .checkbox-option:hover {
    background: #333333;
    border-color: #4a4a4a;
}

.custom-field input[type="radio"]:checked + label,
.custom-field input[type="checkbox"]:checked + label {
    color: #3b82f6;
    font-weight: 600;
}

.foster-form-wrapper {
    max-width: 1400px;
    margin: 0 auto;
}

.form-progress {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px;
    padding: 0 20px;
}

.progress-step {
    flex: 1;
    text-align: center;
    position: relative;
}

.progress-step::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    right: -50%;
    height: 2px;
    background: var(--progress-line, #3a3a3a);
    z-index: 0;
}

.progress-step:last-child::before {
    display: none;
}

.progress-step.active .step-number {
    background: #3b82f6;
    color: white;
}

.progress-step.completed .step-number {
    background: #28a745;
    color: white;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--step-bg, #2a2a2a);
    border: 2px solid var(--step-border, #3a3a3a);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}

.step-label {
    font-size: 13px;
    color: var(--step-label, #888);
    font-weight: 500;
}

.progress-step.active .step-label {
    color: #3b82f6;
}

.form-container {
    background: var(--form-bg, #2a2a2a);
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.light-theme .form-container {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;

}

.form-section {
    background: var(--section-bg, #1e1e1e);
    border-radius: 12px;
    padding: 24px;
    border: 1px solid var(--section-border, #3a3a3a);
}

.light-theme .form-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}

.section-title {
    color: var(--section-title, #ffffff);
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 24px;
    padding-bottom: 12px;
    border-bottom: 2px solid #3b82f6;
    display: flex;
    align-items: center;
    gap: 10px;
}

.light-theme .section-title {
    color: #1e293b;
    border-color: #2563eb;
}

.section-title::before {
    content: '';
    width: 4px;
    height: 20px;
    background: #3b82f6;
    border-radius: 2px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    color: var(--label-color, #b8c5ff);
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.light-theme .form-group label {
    color: #475569;
}

.form-group label .required {
    color: #dc3545;
    margin-left: 4px;
}

/* Unified input styling for both themes */
.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
    border: 1px solid var(--input-border, #3a3a3a);
    background-color: var(--input-bg, #2a2a2a);
    color: var(--input-color, #ffffff);
}

.light-theme .form-group input,
.light-theme .form-group select,
.light-theme .form-group textarea {
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    color: #334155;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.tag-input-wrapper {
    display: flex;
    gap: 10px;
    margin-bottom: 12px;
}

.tag-input-wrapper input {
    flex: 1;
}

.tag-input-wrapper button {
    padding: 12px 24px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    white-space: nowrap;
}

.tag-input-wrapper button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 40px;
    padding: 8px;
    background: var(--tags-bg, #1a1a1a);
    border-radius: 8px;
}

.light-theme .tags {
    background: #f1f5f9;
}

.tag {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    animation: tagAppear 0.3s ease;
}

@keyframes tagAppear {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.tag span {
    cursor: pointer;
    font-weight: 700;
    font-size: 16px;
    transition: transform 0.2s;
}

.tag span:hover {
    transform: scale(1.3);
}

.table-container {
    overflow-x: auto;
    border-radius: 8px;
}

#familyTable {
    width: 100%;
    border-collapse: collapse;
    background: var(--table-bg, #1a1a1a);
    border-radius: 8px;
    overflow: hidden;
}

.light-theme #familyTable {
    background: #ffffff;
}

#familyTable th {
    background: var(--table-header-bg, #333333);
    color: var(--table-header-color, #b8c5ff);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 12px 8px;
    text-align: left;
}

.light-theme #familyTable th {
    background: #f8fafc;
    color: #475569;
}

#familyTable td {
    position: relative;
}

table td {
    
    border: 1px solid #e5e7eb;
    background-color: white;
}

#familyTable input {
    width: 100%;
    border: none;
    padding: 10px;
    height: 50px;
    font-size: 13px;
}


.light-theme #familyTable td input {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    color: #334155;
}

#familyTable td input:focus {
    outline: none;
    border-color: #3b82f6;
    position: absolute;
    z-index: 10;
    background-color: var(--table-input-focus-bg, #2a2a2a);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    white-space: normal;
    min-width: 200px;
    height: auto;
    min-height: 36px;
    color: white;
}

.light-theme #familyTable td input:focus {
    background-color: #ffffff;
    color: #334155;
}

.btn-add-member {
    margin-top: 12px;
    padding: 10px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-add-member:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
}

.btn-delete {
    padding: 6px 12px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s;
}

.btn-delete:hover {
    background: #c82333;
}

.upload-area {
    border: 2px dashed var(--upload-border, #3a3a3a);
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--upload-bg, #1a1a1a);
}

.light-theme .upload-area {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.upload-area:hover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.05);
}

.upload-icon {
    font-size: 48px;
    color: #3b82f6;
    margin-bottom: 12px;
}

.document-list {
    margin-top: 20px;
}

.document-item {
    background: var(--document-bg, #1a1a1a);
    border: 1px solid var(--document-border, #3a3a3a);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.light-theme .document-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.document-item:hover {
    border-color: #3b82f6;
    transform: translateX(4px);
}

.document-item strong {
    color: var(--document-label, #b8c5ff);
    font-size: 12px;
    text-transform: uppercase;
    display: block;
    margin-bottom: 4px;
}

.light-theme .document-item strong {
    color: #64748b;
}

.view-link {
    color: #3b82f6;
    text-decoration: none;
    font-size: 13px;
    margin-left: 8px;
}

.view-link:hover {
    text-decoration: underline;
}

.button-group {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid var(--button-group-border, #3a3a3a);
}

.light-theme .button-group {
    border-top-color: #e2e8f0;
}

.btn-primary {
    padding: 14px 32px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-secondary {
    padding: 14px 32px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.alert {
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid #28a745;
    color: #28a745;
}

.alert-error {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid #dc3545;
    color: #dc3545;
}

.alert::before {
    content: '✓';
    width: 24px;
    height: 24px;
    background: currentColor;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
}

.alert-error::before {
    content: '✕';
}


/* CSS Custom Properties for Theme Switching */
:root {
    --form-bg: #2a2a2a;
    --section-bg: #1e1e1e;
    --section-border: #3a3a3a;
    --section-title: #ffffff;
    --label-color: #b8c5ff;
    --input-bg: #2a2a2a;
    --input-border: #3a3a3a;
    --input-color: #ffffff;
    --tags-bg: #1a1a1a;
    --table-bg: #1a1a1a;
    --table-header-bg: #333333;
    --table-header-color: #b8c5ff;
    --table-input-bg: #2a2a2a;
    --table-input-border: #3a3a3a;
    --table-input-color: #ffffff;
    --table-input-focus-bg: #2a2a2a;
    --upload-bg: #1a1a1a;
    --upload-border: #3a3a3a;
    --document-bg: #1a1a1a;
    --document-border: #3a3a3a;
    --document-label: #b8c5ff;
    --button-group-border: #3a3a3a;
    --step-bg: #2a2a2a;
    --step-border: #3a3a3a;
    --step-label: #888;
    --progress-line: #3a3a3a;
}

.light-theme {
    --form-bg: #ffffff;
    --section-bg: #f8fafc;
    --section-border: #e2e8f0;
    --section-title: #1e293b;
    --label-color: #475569;
    --input-bg: #ffffff;
    --input-border: #cbd5e1;
    --input-color:rgb(241, 247, 255);
    --tags-bg: #f1f5f9;
    --table-bg: #ffffff;
    --table-header-bg: #f8fafc;
    --table-header-color: #475569;
    --table-input-bg: #ffffff;
    --table-input-border: #e2e8f0;
    --table-input-color: #334155;
    --table-input-focus-bg: #ffffff;
    --upload-bg: #f8fafc;
    --upload-border: #cbd5e1;
    --document-bg: #ffffff;
    --document-border: #e2e8f0;
    --document-label: #64748b;
    --button-group-border: #e2e8f0;
    --step-bg: #ffffff;
    --step-border: #cbd5e1;
    --step-label: #64748b;
    --progress-line: #cbd5e1;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .form-progress {
        flex-wrap: wrap;
        gap: 20px;
    }

    .progress-step::before {
        display: none;
    }

    .form-container {
        padding: 20px;
    }

    .button-group {
        flex-direction: column;
    }

    .button-group button {
        width: 100%;
    }

    .table-container {
        font-size: 12px;
    }

    #familyTable th,
    #familyTable td {
        padding: 6px 4px;
    }
}
</style>

<script>
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

// Auto-dismiss notifications after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    });
});
    
let familyMemberCount = 1;

// Auto-focus on first input and make form more user-friendly
document.addEventListener('DOMContentLoaded', function() {
    // Focus on the first input field
    const firstInput = document.querySelector('input[type="text"]');
    if (firstInput) {
        firstInput.focus();
    }
    
    // Add better visual feedback for inputs
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#3b82f6';
            this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.boxShadow = 'none';
        });
    });
});


// Enhanced document loading for foster documents
function loadExistingDocuments(fosterId) {
    if (!fosterId) {
        const documentList = document.getElementById('documentList');
        documentList.innerHTML = '<div style="text-align: center; padding: 20px; color: #888;">No foster ID specified.</div>';
        return;
    }
    
    // Show loading state
    const documentList = document.getElementById('documentList');
    documentList.innerHTML = '<div style="text-align: center; padding: 20px; color: #888;">Loading documents...</div>';
    
    fetch(`get-foster-documents.php?foster_id=${fosterId}`)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('Raw response from get-foster-documents.php:', text);
        
        // Check if it's HTML error page
        if (text.includes('<br />') || text.includes('<b>') || text.trim().startsWith('<')) {
            throw new Error('Server returned HTML instead of JSON. Check PHP errors.');
        }
        
        // Try to parse as JSON
        try {
            const data = JSON.parse(text);
            return data;
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
        }
    })
    .then(data => {
        const documentList = document.getElementById('documentList');
        
        if (data.success && data.documents && data.documents.length > 0) {
            documentList.innerHTML = ''; // Clear loading message
            
            data.documents.forEach(doc => {
                const docItem = document.createElement('div');
                docItem.className = 'document-item';
                docItem.innerHTML = `
                    <div>
                        <div><strong>${doc.type}</strong></div>
                        <div>${doc.name}</div>
                        <div style="font-size: 12px; color: #888; margin-top: 4px;">
                            Uploaded by ${doc.uploaded_by} on ${doc.date_uploaded}
                        </div>
                    </div>
                    <div>
                        <div><strong>Actions</strong></div>
                        <div>
                            <a href="${doc.file_path}" target="_blank" class="view-link">View</a>
                            <a href="#" class="view-link" onclick="deleteFosterDocument(${doc.id}, this)">Delete</a>
                        </div>
                    </div>
                `;
                documentList.appendChild(docItem);
            });
        } else {
            documentList.innerHTML = '<div style="text-align: center; padding: 20px; color: #888;">No documents uploaded yet.</div>';
        }
    })
    .catch(error => {
        console.error('Error loading documents:', error);
        const documentList = document.getElementById('documentList');
        documentList.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #888;">
                Unable to load documents at this time.
                <br><small>Error: ${error.message}</small>
                <br><small>You can still upload new documents below.</small>
            </div>
        `;
    });
}

// File upload handler for foster documents
function handleFileUpload(event) {
    const files = event.target.files;
    const documentList = document.getElementById('documentList');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    if (files.length === 0) return;
    
    // Get foster ID from URL (for existing fosters) - for new fosters, we'll handle after form submission
    const urlParams = new URLSearchParams(window.location.search);
    let fosterId = urlParams.get('foster_id');
    
    if (!fosterId) {
        // For new fosters, we can't upload documents until the form is submitted
        showNotification('Please save the foster profile first before uploading documents.', 'warning');
        event.target.value = '';
        return;
    }
    
    // Show progress bar
    uploadProgress.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Preparing upload...';
    
    const formData = new FormData();
    formData.append('action', 'upload_foster_documents');
    formData.append('foster_id', fosterId);
    
    // Add all files
    for (let i = 0; i < files.length; i++) {
        formData.append('documents[]', files[i]);
    }
    
    // Create temporary upload indicators
    const tempItems = [];
    Array.from(files).forEach((file, index) => {
        const docItem = document.createElement('div');
        docItem.className = 'document-item uploading';
        docItem.innerHTML = `
            <div>
                <div><strong>Uploading...</strong></div>
                <div>${file.name}</div>
            </div>
            <div>
                <div><strong>Status</strong></div>
                <div>Uploading... <span class="upload-progress">0%</span></div>
            </div>
        `;
        documentList.appendChild(docItem);
        tempItems.push(docItem);
    });
    
    // AJAX upload to the new foster documents processor
    fetch('process-foster-documents.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(text => {
            console.log('Raw response from process-foster-documents.php:', text);
            
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('Failed to parse JSON:', e);
                throw new Error('Server returned invalid JSON. Response: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            // Remove temporary items
            tempItems.forEach(item => item.remove());
            
            // Add the actual uploaded documents
            if (data.files && data.files.length > 0) {
                data.files.forEach(file => {
                    const docItem = document.createElement('div');
                    docItem.className = 'document-item';
                    docItem.innerHTML = `
                        <div>
                            <div><strong>${file.type}</strong></div>
                            <div>${file.name}</div>
                        </div>
                        <div>
                            <div><strong>Uploaded</strong></div>
                            <div>
                                <a href="${file.path}" target="_blank" class="view-link">view</a>
                                <a href="#" class="view-link" onclick="deleteFosterDocument(${file.id}, this)">delete</a>
                            </div>
                        </div>
                    `;
                    documentList.appendChild(docItem);
                });
            }
            
            progressBar.style.width = '100%';
            progressText.textContent = `Successfully uploaded ${data.count || 0} file(s)`;
            
            setTimeout(() => {
                uploadProgress.style.display = 'none';
                showNotification(`Successfully uploaded ${data.count || 0} file(s)`, 'success');
            }, 2000);
            
        } else {
            // Remove temporary items on error
            tempItems.forEach(item => item.remove());
            uploadProgress.style.display = 'none';
            showNotification('Upload failed: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        tempItems.forEach(item => item.remove());
        uploadProgress.style.display = 'none';
        showNotification('Upload failed: ' + error.message, 'error');
    });
    
    // Reset file input
    event.target.value = '';
}

// Document deletion function for foster documents
function deleteFosterDocument(docId, element) {
    if (!confirm('Are you sure you want to delete this document?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('doc_id', docId);
    formData.append('action', 'delete_foster_document');
    
    fetch('process-foster-documents.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.closest('.document-item').remove();
            showNotification('Document deleted successfully', 'success');
            
            // If no documents left, show message
            const documentList = document.getElementById('documentList');
            const remainingItems = documentList.querySelectorAll('.document-item');
            if (remainingItems.length === 0) {
                documentList.innerHTML = '<div style="text-align: center; padding: 20px; color: #888;">No documents uploaded yet.</div>';
            }
        } else {
            showNotification('Error deleting document: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting document: ' + error.message, 'error');
    });
}

// Add family member
function addFamilyMember() {
    const tbody = document.getElementById('familyMembers');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td><input type="text" name="family_members[${familyMemberCount}][name]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][relationship]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][age]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][gender]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][civil_status]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][education]"></td>
        <td><input type="text" name="family_members[${familyMemberCount}][occupation_income]"></td>
        <td><button type="button" onclick="removeFamilyMember(this)" class="btn-delete">Remove</button></td>
    `;
    tbody.appendChild(newRow);
    familyMemberCount++;
}

// Remove family member
function removeFamilyMember(button) {
    if (document.getElementById('familyMembers').children.length > 1) {
        button.closest('tr').remove();
    } else {
        alert('At least one family member is required.');
    }
}

// Tag management
function addInterest() {
    const input = document.getElementById('interestInput');
    const value = input.value.trim();
    if (value) {
        const container = document.getElementById('interestTags');
        const tag = document.createElement('span');
        tag.className = 'tag';
        tag.innerHTML = `${value} <span onclick="removeTag(this)" style="cursor: pointer; margin-left: 5px;">×</span>`;
        container.appendChild(tag);
        updateHiddenFields();
        input.value = '';
    }
}

function addTrait() {
    const input = document.getElementById('traitInput');
    const value = input.value.trim();
    if (value) {
        const container = document.getElementById('traitTags');
        const tag = document.createElement('span');
        tag.className = 'tag';
        tag.innerHTML = `${value} <span onclick="removeTag(this)" style="cursor: pointer; margin-left: 5px;">×</span>`;
        container.appendChild(tag);
        updateHiddenFields();
        input.value = '';
    }
}

function removeTag(element) {
    element.parentElement.remove();
    updateHiddenFields();
}

function updateHiddenFields() {
    const interests = Array.from(document.getElementById('interestTags').children)
        .map(tag => tag.textContent.replace('×', '').trim())
        .filter(text => text);
    
    const traits = Array.from(document.getElementById('traitTags').children)
        .map(tag => tag.textContent.replace('×', '').trim())
        .filter(text => text);
    
    document.getElementById('interestsHidden').value = interests.join(', ');
    document.getElementById('traitsHidden').value = traits.join(', ');
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
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
            showNotification('Please fill in all required fields.', 'error');
        }
    });

    // Enter key for tags
    const interestInput = document.getElementById('interestInput');
    const traitInput = document.getElementById('traitInput');
    
    if (interestInput) {
        interestInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addInterest();
            }
        });
    }
    
    if (traitInput) {
        traitInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTrait();
            }
        });
    }
});

function previewDocument(filename) {
    alert('Preview document: ' + filename);
    // In a real application, this would open the document in a modal or new tab
}

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabName + 'Tab');
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Activate selected button
    event.target.classList.add('active');
}

// Load existing documents when page loads (for existing fosters)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const fosterId = urlParams.get('foster_id');
    if (fosterId) {
        loadExistingDocuments(fosterId);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>