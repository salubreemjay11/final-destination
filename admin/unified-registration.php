<?php
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Enable error logging at the VERY TOP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$pageTitle = 'Unified Child & Case Registration - Orphanfare';
require_once 'includes/header.php';

// DEBUG: Check what we have
error_log("=== UNIFIED REGISTRATION DEBUG START ===");
error_log("PDO available: " . (isset($pdo) ? 'YES' : 'NO'));
error_log("Session user: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Load Custom Field Manager with PDO connection
$fieldManager = null;
$childCustomFields = [];
$caseCustomFields = [];
$existingChildCustomValues = [];
$existingCaseCustomValues = [];

try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
    } elseif (file_exists('includes/CustomFieldManager.php')) {
        require_once 'includes/CustomFieldManager.php';
    } else {
        throw new Exception('CustomFieldManager.php not found');
    }
    
    // USE PDO CONNECTION
    $fieldManager = new CustomFieldManager($pdo);

    // TEMPORARY FIX: Update fields with options but no type to 'select'
    try {
        $fixStmt = $pdo->prepare("UPDATE custom_fields SET field_type = 'select' WHERE (field_type IS NULL OR field_type = '') AND field_options IS NOT NULL AND field_options != ''");
        $fixStmt->execute();
        error_log("Fixed field types for fields with options");
    } catch (Exception $e) {
        error_log("Field type fix error: " . $e->getMessage());
    }
    
    // Load custom fields for both modules
    $childCustomFields = $fieldManager->getModuleFields('children');
    $caseCustomFields = $fieldManager->getModuleFields('cases');
    
    error_log("Child custom fields loaded: " . count($childCustomFields));
    error_log("Case custom fields loaded: " . count($caseCustomFields));
    
} catch (Exception $e) {
    error_log("Custom Field Manager Error: " . $e->getMessage());
    $customFieldsError = "Custom fields are temporarily unavailable. Please contact administrator.";
}

// Handle viewing existing unified record
$childId = $_GET['child_id'] ?? null;
$editMode = false;
$existingCaseData = null;
$existingChildData = null;
$unifiedId = null;
$isAddCaseMode = false; // NEW: Flag to indicate we're adding a case to existing child

// Check if we're adding a case to an existing child
if ($childId && !isset($_GET['mode'])) {
    $isAddCaseMode = true;
    error_log("ADD CASE MODE: Adding case to existing child: " . $childId);
    
    try {
        // Load existing child data
        $stmt = $pdo->prepare("SELECT * FROM children WHERE child_id = ?");
        $stmt->execute([$childId]);
        $existingChildData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingChildData) {
            error_log("Found existing child: " . $childId);
            // Check if child already has a case
            $stmt = $pdo->prepare("SELECT * FROM cases WHERE linked_child_id = ? OR case_id = ?");
            $stmt->execute([$childId, $childId]);
            $existingCaseData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingCaseData) {
                error_log("Child already has case: " . $existingCaseData['case_id']);
                // Redirect to view the existing case
                header('Location: case-details.php?case_id=' . $existingCaseData['case_id']);
                exit();
            }
        } else {
            error_log("Child not found: " . $childId);
            $error = "Child not found with ID: " . $childId;
        }
    } catch (Exception $e) {
        error_log("Error loading child data: " . $e->getMessage());
        $error = "Error loading child data: " . $e->getMessage();
    }
}

// Handle viewing existing record (different from add case mode)
if (isset($_GET['case_id']) && isset($_GET['mode']) && $_GET['mode'] === 'view') {
    $caseId = $_GET['case_id'];
    $editMode = true;
    
    try {
        // Get case data - USE $pdo
        $stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $existingCaseData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingCaseData) {
            // Get the linked child data
            $childId = $existingCaseData['linked_child_id'] ?? $caseId;
            $stmt = $pdo->prepare("SELECT * FROM children WHERE child_id = ?");
            $stmt->execute([$childId]);
            $existingChildData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $unifiedId = $caseId;
            
            // Load existing custom field values
            if ($fieldManager) {
                $existingChildCustomValues = $fieldManager->getFieldValues($unifiedId, 'children');
                $existingCaseCustomValues = $fieldManager->getFieldValues($unifiedId, 'cases');
            }
        }
    } catch (Exception $e) {
        error_log("Error loading unified record: " . $e->getMessage());
        $error = "Error loading record: " . $e->getMessage();
    }
}

function generateUnifiedId() {
    global $pdo;
    
    try {
        $currentYear = date('Y');
        $query = "SELECT child_id FROM children WHERE child_id LIKE ? ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $searchPattern = "UC-{$currentYear}-%";
        $stmt->execute([$searchPattern]);
        $lastId = $stmt->fetchColumn();
        
        if ($lastId) {
            $number = intval(substr($lastId, -3)) + 1;
        } else {
            $number = 1;
        }
        
        return "UC-{$currentYear}-" . str_pad($number, 3, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        return "UC-" . date('Y') . "-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

function generateCaseId() {
    global $pdo;
    
    try {
        $currentYear = date('Y');
        $query = "SELECT case_id FROM cases WHERE case_id LIKE ? ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $searchPattern = "CASE-{$currentYear}-%";
        $stmt->execute([$searchPattern]);
        $lastId = $stmt->fetchColumn();
        
        if ($lastId) {
            $number = intval(substr($lastId, -3)) + 1;
        } else {
            $number = 1;
        }
        
        return "CASE-{$currentYear}-" . str_pad($number, 3, '0', STR_PAD_LEFT);
    } catch (Exception $e) {
        return "CASE-" . date('Y') . "-" . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
}

function handlePhotoUpload($file, $childId) {
    $uploadDir = 'uploads/children/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create directory: " . $uploadDir);
            return 'public/placeholder.jpg';
        }
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        error_log("Invalid file extension: " . $fileExtension);
        return 'public/placeholder.jpg';
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log("File too large: " . $file['size']);
        return 'public/placeholder.jpg';
    }
    
    // Generate unique filename
    $fileName = $childId . '_' . time() . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fileName;
    
    // Check if upload directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload directory not writable: " . $uploadDir);
        return 'public/placeholder.jpg';
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("File uploaded successfully: " . $uploadPath);
        
        // Verify the file was actually moved
        if (file_exists($uploadPath)) {
            return $uploadPath;
        } else {
            error_log("File move verification failed");
            return 'public/placeholder.jpg';
        }
    }
    
    error_log("File upload failed. Error: " . $file['error']);
    return 'public/placeholder.jpg';
}

// Handle NEW unified record creation OR adding case to existing child
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_existing'])) {
    error_log("=== FORM SUBMISSION STARTED ===");
    error_log("POST data: " . print_r($_POST, true));
    
    try {
        // Determine if we're creating new unified record or adding case to existing child
        $isAddingCaseToExistingChild = !empty($_POST['existing_child_id']);
        
        if ($isAddingCaseToExistingChild) {
            // ADDING CASE TO EXISTING CHILD
            $childId = $_POST['existing_child_id'];
            error_log("ADDING CASE TO EXISTING CHILD: " . $childId);
            
            // Load existing child data
            $stmt = $pdo->prepare("SELECT * FROM children WHERE child_id = ?");
            $stmt->execute([$childId]);
            $existingChildData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existingChildData) {
                throw new Exception("Child not found with ID: " . $childId);
            }
            
            // Check if case already exists
            $stmt = $pdo->prepare("SELECT * FROM cases WHERE linked_child_id = ? OR case_id = ?");
            $stmt->execute([$childId, $childId]);
            $existingCase = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingCase) {
                throw new Exception("This child already has a case: " . $existingCase['case_id']);
            }
            
            // Generate case ID
            $caseId = generateCaseId();
            error_log("Generated case ID: " . $caseId);
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Insert Case Record
            $caseInsertSql = "INSERT INTO cases (
                case_id, linked_child_id, case_type, child_age, child_gender, 
                current_location, birth_date, contact_number, reported_by,
                reporter_relation, reporter_phone, reporter_email, 
                expected_date, description, priority, social_worker,
                status, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $caseInsertValues = [
                $caseId,
                $childId, // Link to existing child
                $_POST['case_type'] ?? '',
                intval($existingChildData['age'] ?? 0), // Use child's age
                $existingChildData['gender'] ?? '', // Use child's gender
                trim($existingChildData['address'] ?? ''), // Use child's address
                $existingChildData['date_of_birth'] ?: null, // Use child's birth date
                trim($existingChildData['contact_phone'] ?? ''), // Use child's contact
                trim($_POST['reported_by'] ?? ''),
                trim($_POST['reporter_relation'] ?? ''),
                trim($_POST['reporter_phone'] ?? ''),
                trim($_POST['reporter_email'] ?? ''),
                $_POST['expected_date'] ?: date('Y-m-d'),
                trim($_POST['case_description'] ?? ''),
                $_POST['priority'] ?? 'common',
                $_POST['social_worker'] ?? '',
                $_POST['status'] ?? 'Open',
                $currentUser['id']
            ];

            error_log("Case insert values: " . print_r($caseInsertValues, true));

            $caseStmt = $pdo->prepare($caseInsertSql);
            $caseResult = $caseStmt->execute($caseInsertValues);
            
            if (!$caseResult) {
                $caseError = $caseStmt->errorInfo();
                throw new Exception('Failed to insert case record: ' . ($caseError[2] ?? 'Unknown error'));
            }
            
            // ========== PROCESS CUSTOM FIELDS FOR CASE ==========
            if ($fieldManager && !empty($caseCustomFields)) {
                error_log("Processing case custom fields for new case");
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'custom_field_') === 0) {
                        $fieldName = str_replace('custom_field_', '', $key);
                        
                        // Check if this field belongs to cases module
                        $isCaseField = false;
                        foreach ($caseCustomFields as $field) {
                            if ($field['field_name'] === $fieldName) {
                                $isCaseField = true;
                                break;
                            }
                        }
                        
                        if ($isCaseField && !empty(trim($value))) {
                            $saveResult = $fieldManager->saveFieldValue($caseId, 'cases', $fieldName, $value);
                            error_log("Saved case custom field - Field: $fieldName, Value: '$value', Result: " . ($saveResult ? 'SUCCESS' : 'FAILED'));
                        }
                    }
                }
            }
            // ========== END CUSTOM FIELD PROCESSING ==========
            
            // Update child record with linked_case_id
            $updateChildStmt = $pdo->prepare("UPDATE children SET linked_case_id = ? WHERE child_id = ?");
            $updateChildStmt->execute([$caseId, $childId]);
            
            if ($caseResult) {
                $pdo->commit();
                logActivity($currentUser['id'], 'Case Added to Child', 'cases', $caseId);
                
                error_log("=== CASE ADDITION COMPLETED SUCCESSFULLY ===");
                
                header('Location: child-management.php?success=case_added&case_id=' . $caseId);
                exit();
            } else {
                $pdo->rollBack();
                throw new Exception('Failed to create case');
            }
            
        } else {
            // CREATING NEW UNIFIED RECORD (BOTH CHILD AND CASE)
            error_log("CREATING NEW UNIFIED RECORD");
            
            // Generate unified ID
            $unifiedId = generateUnifiedId();
            error_log("Generated unified ID: " . $unifiedId);
            
            // Start transaction
            $pdo->beginTransaction();
            
            // Handle photo upload
            $photoPath = 'public/placeholder.jpg';
            if (isset($_FILES['child_photo']) && $_FILES['child_photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = handlePhotoUpload($_FILES['child_photo'], $unifiedId);
                error_log("Photo uploaded: " . $photoPath);
            }
            
            // Process family composition
            $familyComposition = [];
            if (isset($_POST['family_members'])) {
                foreach ($_POST['family_members'] as $index => $member) {
                    if (!empty($member['name'])) {
                        $familyComposition[] = [
                            'name' => $member['name'] ?? '',
                            'relationship' => $member['relationship'] ?? '',
                            'age' => $member['age'] ?? '',
                            'sex' => $member['sex'] ?? '',
                            'civil_status' => $member['civil_status'] ?? '',
                            'educational_attainment' => $member['educational_attainment'] ?? '',
                            'occupation_income' => $member['occupation_income'] ?? ''
                        ];
                    }
                }
            }
            
            // Insert Child Record - REMOVED NAME FIELD
            $childInsertSql = "INSERT INTO children (
                child_id, age, gender, date_of_birth, entry_date, address, 
                health_status, allergies, emergency_contact, contact_phone, 
                problem_description, notes, photo_path, created_by,
                civil_status, birth_place, educational_attainment, occupation,
                monthly_income, religion, family_composition, problem_presented,
                assessment_recommendation, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $childInsertValues = [
                $unifiedId,
                intval($_POST['child_age'] ?? 0),
                $_POST['child_gender'] ?? '',
                !empty($_POST['child_birth_date']) ? $_POST['child_birth_date'] : null,
                $_POST['entry_date'] ?? date('Y-m-d'),
                trim($_POST['child_address'] ?? ''),
                trim($_POST['health_status'] ?? 'Good'),
                trim($_POST['allergies'] ?? ''),
                trim($_POST['emergency_contact'] ?? ''),
                trim($_POST['contact_phone'] ?? ''),
                trim($_POST['problem_description'] ?? ''),
                trim($_POST['child_notes'] ?? ''),
                $photoPath,
                $currentUser['id'] ?? 1, // Fallback user ID
                trim($_POST['civil_status'] ?? ''),
                trim($_POST['birth_place'] ?? ''),
                trim($_POST['educational_attainment'] ?? ''),
                trim($_POST['occupation'] ?? ''),
                trim($_POST['monthly_income'] ?? ''),
                trim($_POST['religion'] ?? ''),
                !empty($familyComposition) ? json_encode($familyComposition) : null,
                trim($_POST['problem_presented'] ?? ''),
                trim($_POST['assessment_recommendation'] ?? ''),
                $_POST['child_status'] ?? 'In Care'  // Add status here
            ];

            error_log("Child insert values: " . print_r($childInsertValues, true));

            try {
                $childStmt = $pdo->prepare($childInsertSql);
                $childResult = $childStmt->execute($childInsertValues);
                
                if (!$childResult) {
                    $childError = $childStmt->errorInfo();
                    error_log("Child insert error: " . print_r($childError, true));
                    throw new Exception('Failed to insert child record: ' . ($childError[2] ?? 'Unknown error'));
                }
                
                error_log("Child record inserted successfully with ID: " . $unifiedId);
                
                // ========== PROCESS CUSTOM FIELDS FOR CHILD ==========
                if ($fieldManager && !empty($childCustomFields)) {
                    error_log("Processing child custom fields for new child");
                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'custom_field_') === 0) {
                            $fieldName = str_replace('custom_field_', '', $key);
                            
                            // Check if this field belongs to children module
                            $isChildField = false;
                            foreach ($childCustomFields as $field) {
                                if ($field['field_name'] === $fieldName) {
                                    $isChildField = true;
                                    break;
                                }
                            }
                            
                            if ($isChildField && !empty(trim($value))) {
                                $saveResult = $fieldManager->saveFieldValue($unifiedId, 'children', $fieldName, $value);
                                error_log("Saved child custom field - Field: $fieldName, Value: '$value', Result: " . ($saveResult ? 'SUCCESS' : 'FAILED'));
                            }
                        }
                    }
                }
                // ========== END CUSTOM FIELD PROCESSING ==========
                
            } catch (Exception $e) {
                error_log("Exception in child insert: " . $e->getMessage());
                throw $e;
            }

            // Check if case information is provided (optional)
            $hasCaseInfo = !empty(trim($_POST['case_type'] ?? '')) || 
                          !empty(trim($_POST['case_description'] ?? '')) || 
                          !empty(trim($_POST['reported_by'] ?? ''));
            
            if ($hasCaseInfo) {
                // Insert Case Record only if case information is provided
                $caseInsertSql = "INSERT INTO cases (
                    case_id, linked_child_id, case_type, child_age, child_gender, 
                    current_location, birth_date, contact_number, reported_by,
                    reporter_relation, reporter_phone, reporter_email, 
                    expected_date, description, priority, social_worker,
                    status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $caseInsertValues = [
                    $unifiedId,
                    $unifiedId, // Link to the new child
                    $_POST['case_type'] ?? '',
                    intval($_POST['child_age'] ?? 0),
                    $_POST['child_gender'] ?? '',
                    trim($_POST['child_address'] ?? ''),
                    $_POST['child_birth_date'] ?: null,
                    trim($_POST['contact_phone'] ?? ''),
                    trim($_POST['reported_by'] ?? ''),
                    trim($_POST['reporter_relation'] ?? ''),
                    trim($_POST['reporter_phone'] ?? ''),
                    trim($_POST['reporter_email'] ?? ''),
                    $_POST['expected_date'] ?: date('Y-m-d'),
                    trim($_POST['case_description'] ?? ''),
                    $_POST['priority'] ?? 'common',
                    $_POST['social_worker'] ?? '',
                    $_POST['status'] ?? 'Open',
                    $currentUser['id']
                ];

                $caseStmt = $pdo->prepare($caseInsertSql);
                $caseResult = $caseStmt->execute($caseInsertValues);
                
                if (!$caseResult) {
                    $caseError = $caseStmt->errorInfo();
                    throw new Exception('Failed to insert case record: ' . ($caseError[2] ?? 'Unknown error'));
                }
                
                // ========== PROCESS CUSTOM FIELDS FOR CASE ==========
                if ($fieldManager && !empty($caseCustomFields)) {
                    error_log("Processing case custom fields for new case");
                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'custom_field_') === 0) {
                            $fieldName = str_replace('custom_field_', '', $key);
                            
                            // Check if this field belongs to cases module
                            $isCaseField = false;
                            foreach ($caseCustomFields as $field) {
                                if ($field['field_name'] === $fieldName) {
                                    $isCaseField = true;
                                    break;
                                }
                            }
                            
                            if ($isCaseField && !empty(trim($value))) {
                                $saveResult = $fieldManager->saveFieldValue($unifiedId, 'cases', $fieldName, $value);
                                error_log("Saved case custom field - Field: $fieldName, Value: '$value', Result: " . ($saveResult ? 'SUCCESS' : 'FAILED'));
                            }
                        }
                    }
                }
                // ========== END CUSTOM FIELD PROCESSING ==========
                
                // Update child record with linked_case_id only if case was created
                $updateChildStmt = $pdo->prepare("UPDATE children SET linked_case_id = ? WHERE child_id = ?");
                $updateChildStmt->execute([$unifiedId, $unifiedId]);
            }
            
            if ($childResult) {
                $pdo->commit();
                $action = $hasCaseInfo ? 'Unified Record Created' : 'Child Record Created';
                logActivity($currentUser['id'], $action, 'children_cases', $unifiedId);
                
                error_log("=== NEW RECORD CREATION COMPLETED SUCCESSFULLY ===");
                
                header('Location: child-management.php?success=' . ($hasCaseInfo ? 'unified_created' : 'child_created') . '&case_id=' . $unifiedId);
                exit();
            } else {
                $pdo->rollBack();
                throw new Exception('Failed to create record');
            }
        }
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Form submission error: " . $e->getMessage());
        $error = "Failed to create record: " . $e->getMessage();
    }
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_existing'])) {
    error_log("=== UPDATE PROCESS STARTED ===");
    
    try {
        if (!isset($_POST['case_id']) || empty($_POST['case_id'])) {
            throw new Exception("No case ID provided");
        }
        
        $caseId = $_POST['case_id'];
        $childId = $existingCaseData['linked_child_id'] ?? $caseId;
        
        error_log("Updating record - Case ID: " . $caseId . ", Child ID: " . $childId);
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Handle photo upload for updates
        $photoPath = null;
        if (isset($_FILES['child_photo']) && $_FILES['child_photo']['error'] === UPLOAD_ERR_OK) {
            $photoPath = handlePhotoUpload($_FILES['child_photo'], $childId);
            error_log("New photo uploaded: " . $photoPath);
        } else {
            // Keep existing photo if no new upload
            if ($existingChildData && !empty($existingChildData['photo_path'])) {
                $photoPath = $existingChildData['photo_path'];
                error_log("Keeping existing photo: " . $photoPath);
            } else {
                $photoPath = 'public/placeholder.jpg';
            }
        }
        
        // Process family composition
        $familyComposition = [];
        if (isset($_POST['family_members'])) {
            foreach ($_POST['family_members'] as $index => $member) {
                if (!empty($member['name'])) {
                    $familyComposition[] = [
                        'name' => $member['name'] ?? '',
                        'relationship' => $member['relationship'] ?? '',
                        'age' => $member['age'] ?? '',
                        'sex' => $member['sex'] ?? '',
                        'civil_status' => $member['civil_status'] ?? '',
                        'educational_attainment' => $member['educational_attainment'] ?? '',
                        'occupation_income' => $member['occupation_income'] ?? ''
                    ];
                }
            }
        }
        
        // Update Child Record - Use CHILD ID
        $childUpdateColumns = [
            'age = ?', 'gender = ?', 'date_of_birth = ?', 'entry_date = ?', 
            'address = ?', 'health_status = ?', 'allergies = ?', 'emergency_contact = ?', 
            'contact_phone = ?', 'problem_description = ?', 'notes = ?', 'updated_at = NOW()',
            'civil_status = ?', 'birth_place = ?', 'educational_attainment = ?', 'occupation = ?',
            'monthly_income = ?', 'religion = ?', 'family_composition = ?', 'problem_presented = ?',
            'assessment_recommendation = ?', 'status = ?'
        ];

        $childUpdateValues = [
            intval($_POST['child_age'] ?? 0),
            $_POST['child_gender'] ?? '',
            $_POST['child_birth_date'] ?: null,
            $_POST['entry_date'] ?: date('Y-m-d'),
            trim($_POST['child_address'] ?? ''),
            trim($_POST['health_status'] ?? 'Good'),
            trim($_POST['allergies'] ?? ''),
            trim($_POST['emergency_contact'] ?? ''),
            trim($_POST['contact_phone'] ?? ''),
            trim($_POST['problem_description'] ?? ''),
            trim($_POST['child_notes'] ?? ''),
            trim($_POST['civil_status'] ?? ''),
            trim($_POST['birth_place'] ?? ''),
            trim($_POST['educational_attainment'] ?? ''),
            trim($_POST['occupation'] ?? ''),
            trim($_POST['monthly_income'] ?? ''),
            trim($_POST['religion'] ?? ''),
            !empty($familyComposition) ? json_encode($familyComposition) : null,
            trim($_POST['problem_presented'] ?? ''),
            trim($_POST['assessment_recommendation'] ?? ''),
            $_POST['child_status'] ?? 'In Care'
        ];

        // ADD PHOTO PATH TO UPDATE
        if ($photoPath) {
            $childUpdateColumns[] = 'photo_path = ?';
            $childUpdateValues[] = $photoPath;
        }

        // Add the WHERE clause value - USE CHILD ID
        $childUpdateValues[] = $childId;

        // Build the dynamic SQL
        $childUpdateSql = "UPDATE children SET " . implode(', ', $childUpdateColumns) . " WHERE child_id = ?";

        $childStmt = $pdo->prepare($childUpdateSql);
        $childResult = $childStmt->execute($childUpdateValues);
        
        error_log("Child update result: " . ($childResult ? 'SUCCESS' : 'FAILED'));
        
        // ========== PROCESS CUSTOM FIELDS FOR CHILD UPDATE ==========
        if ($fieldManager && !empty($childCustomFields)) {
            error_log("Processing child custom fields for update - Child ID: " . $childId);
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'custom_field_') === 0) {
                    $fieldName = str_replace('custom_field_', '', $key);
                    
                    // Check if this field belongs to children module
                    $isChildField = false;
                    foreach ($childCustomFields as $field) {
                        if ($field['field_name'] === $fieldName) {
                            $isChildField = true;
                            break;
                        }
                    }
                    
                    if ($isChildField) {
                        $saveResult = $fieldManager->saveFieldValue($childId, 'children', $fieldName, $value);
                        error_log("Updated child custom field - Child ID: $childId, Field: $fieldName, Value: '$value', Result: " . ($saveResult ? 'SUCCESS' : 'FAILED'));
                    }
                }
            }
        }
        // ========== END CUSTOM FIELD PROCESSING ==========
        
        // Check if case exists and update it if needed
        $caseExists = false;
        if ($existingCaseData) {
            $caseExists = true;
            
            // Update Case Record - Use CASE ID
            $caseUpdateColumns = [
                'case_type = ?', 'child_age = ?', 'child_gender = ?', 
                'current_location = ?', 'birth_date = ?', 'contact_number = ?', 'reported_by = ?',
                'reporter_relation = ?', 'reporter_phone = ?', 'reporter_email = ?', 
                'expected_date = ?', 'description = ?', 'priority = ?', 'social_worker = ?',
                'status = ?', 'updated_at = NOW()'
            ];

            $caseUpdateValues = [
                $_POST['case_type'] ?? '',
                intval($_POST['child_age'] ?? 0),
                $_POST['child_gender'] ?? '',
                trim($_POST['child_address'] ?? ''),
                $_POST['child_birth_date'] ?: null,
                trim($_POST['contact_phone'] ?? ''),
                trim($_POST['reported_by'] ?? ''),
                trim($_POST['reporter_relation'] ?? ''),
                trim($_POST['reporter_phone'] ?? ''),
                trim($_POST['reporter_email'] ?? ''),
                $_POST['expected_date'] ?: date('Y-m-d'),
                trim($_POST['case_description'] ?? ''),
                $_POST['priority'] ?? 'common',
                $_POST['social_worker'] ?? '',
                $_POST['status'] ?? 'Open'
            ];

            // Add the WHERE clause value - USE CASE ID
            $caseUpdateValues[] = $caseId;

            // Build the dynamic SQL
            $caseUpdateSql = "UPDATE cases SET " . implode(', ', $caseUpdateColumns) . " WHERE case_id = ?";

            $caseStmt = $pdo->prepare($caseUpdateSql);
            $caseResult = $caseStmt->execute($caseUpdateValues);
            
            error_log("Case update result: " . ($caseResult ? 'SUCCESS' : 'FAILED'));
            
            // ========== PROCESS CUSTOM FIELDS FOR CASE UPDATE ==========
            if ($fieldManager && !empty($caseCustomFields)) {
                error_log("Processing case custom fields for update - Case ID: " . $caseId);
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'custom_field_') === 0) {
                        $fieldName = str_replace('custom_field_', '', $key);
                        
                        // Check if this field belongs to cases module
                        $isCaseField = false;
                        foreach ($caseCustomFields as $field) {
                            if ($field['field_name'] === $fieldName) {
                                $isCaseField = true;
                                break;
                            }
                        }
                        
                        if ($isCaseField) {
                            $saveResult = $fieldManager->saveFieldValue($caseId, 'cases', $fieldName, $value);
                            error_log("Updated case custom field - Case ID: $caseId, Field: $fieldName, Value: '$value', Result: " . ($saveResult ? 'SUCCESS' : 'FAILED'));
                        }
                    }
                }
            }
            // ========== END CUSTOM FIELD PROCESSING ==========
        }
        
        if ($childResult && (!$caseExists || $caseResult)) {
            $pdo->commit();
            $action = $caseExists ? 'Unified Record Updated' : 'Child Record Updated';
            logActivity($currentUser['id'], $action, 'children_cases', $caseExists ? $caseId : $childId);
            
            header('Location: child-management.php?success=' . ($caseExists ? 'unified_updated' : 'child_updated'));
            exit();
        } else {
            $pdo->rollBack();
            throw new Exception('Failed to update record');
        }
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Unified update error: " . $e->getMessage());
        $error = "Failed to update record: " . $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <?php 
            if ($isAddCaseMode) {
                echo 'Add Case to Existing Child';
            } elseif ($editMode) {
                echo 'View/Edit Unified Record';
            } else {
                echo 'Unified Child & Case Registration';
            }
            ?>
        </h1>
        <button class="btn btn-secondary" onclick="window.location.href='child-management.php'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="10"  fill="currentColor" class="bi bi-arrow-left"  viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
        </svg>Back to Children</button>
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
                        case 'unified_created':
                            echo 'Child and case record created successfully!';
                            if (isset($_GET['case_id'])) {
                                echo '<br><small>Case ID: ' . htmlspecialchars($_GET['case_id']) . '</small>';
                            }
                            break;
                        case 'case_added':
                            echo 'Case added to child successfully!';
                            if (isset($_GET['case_id'])) {
                                echo '<br><small>Case ID: ' . htmlspecialchars($_GET['case_id']) . '</small>';
                            }
                            break;
                        case 'unified_updated':
                            echo 'Child and case record updated successfully!';
                            break;
                        case 'child_updated':
                            echo 'Child information updated successfully!';
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

    <?php if (isset($error)): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message"><?php echo htmlspecialchars($error); ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <?php if (isset($customFieldsError)): ?>
        <div class="notification warning show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Warning</div>
                <div class="notification-message"><?php echo htmlspecialchars($customFieldsError); ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <!-- FIXED: Single form tag with novalidate -->
    <form method="POST" enctype="multipart/form-data" class="unified-form" id="unifiedForm" novalidate>
        <?php if ($editMode): ?>
            <input type="hidden" name="update_existing" value="1">
            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($unifiedId); ?>">
        <?php endif; ?>
        
        <?php if ($isAddCaseMode && $existingChildData): ?>
            <input type="hidden" name="existing_child_id" value="<?php echo htmlspecialchars($existingChildData['child_id']); ?>">
            
            <div class="existing-child-info" style="background: #e7f3ff; border: 1px solid #b8daff; color: #004085; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #004085;">Adding Case to Existing Child</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div><strong>Child ID:</strong> <?php echo htmlspecialchars($existingChildData['child_id']); ?></div>
                    <div><strong>Age:</strong> <?php echo htmlspecialchars($existingChildData['age']); ?></div>
                    <div><strong>Gender:</strong> <?php echo htmlspecialchars($existingChildData['gender']); ?></div>
                    <div><strong>Status:</strong> <?php echo htmlspecialchars($existingChildData['status'] ?? 'In Care'); ?></div>
                </div>
                <p style="margin: 10px 0 0 0; font-style: italic;">You are adding a case record to this existing child. The child information is pre-filled and cannot be modified here.</p>
            </div>
        <?php endif; ?>
        
        <div class="form-tabs">
            <?php if (!$isAddCaseMode): ?>
                <button type="button" class="tab-btn active" onclick="switchTab('child')">Child Information</button>
            <?php endif; ?>
            <button type="button" class="tab-btn <?php echo $isAddCaseMode ? 'active' : ''; ?>" onclick="switchTab('case')">
                <?php echo $isAddCaseMode ? 'Case Information' : 'Case Information (Optional)'; ?>
            </button>
            <?php if ($fieldManager && (!empty($childCustomFields) || !empty($caseCustomFields))): ?>
                <button type="button" class="tab-btn" onclick="switchTab('custom')">Additional Fields</button>
            <?php endif; ?>
        </div>

        <!-- Child Information Tab - Only show if not in add case mode -->
        <?php if (!$isAddCaseMode): ?>
        <div id="childTab" class="tab-content active">
            <div class="form-section">
                <h3>Child Details <?php echo $editMode ? '(Existing Record)' : ''; ?></h3>
                
                <?php if ($editMode && $existingChildData): ?>
                <div class="existing-record-notice">
                    
                    <strong><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-list" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                <path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8m0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0M4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0m0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                </svg>Viewing existing child record:</strong> This child is already in the system. You can update their information below.
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Child Photo</label>
                    <div class="photo-upload-area" onclick="document.getElementById('childPhoto').click()">
                        <div class="photo-upload-text">
                            <?php if (($editMode || $isAddCaseMode) && $existingChildData): ?>
                                <?php 
                                $currentPhoto = $existingChildData['photo_path'] ?? 'public/placeholder.jpg';
                                if ($currentPhoto && $currentPhoto !== 'public/placeholder.jpg' && file_exists('../' . $currentPhoto)): 
                                ?>
                                    <img src="../<?php echo htmlspecialchars($currentPhoto); ?>" style="max-width: 100px; max-height: 100px;">
                                <?php else: ?>
                                    <img src="../public/placeholder.jpg" style="max-width: 100px; max-height: 100px;">
                                <?php endif; ?>
                                <div class="current-file">Current: <?php echo basename($currentPhoto); ?></div>
                                <small>Click to change photo</small>
                            <?php else: ?>
                                Click to upload photo
                            <?php endif; ?>
                        </div>
                        <input type="file" id="childPhoto" name="child_photo" accept="image/*" style="display: none;" 
                            onchange="previewImage(this)" <?php echo $isAddCaseMode ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Age *</label>
                        <input type="number" name="child_age" min="0" max="18" class="form-input" 
                               value="<?php echo htmlspecialchars($existingChildData['age'] ?? ''); ?>"
                               required <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gender *</label>
                        <select name="child_gender" class="form-input" required <?php echo $isAddCaseMode ? 'disabled' : ''; ?>>
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($existingChildData['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($existingChildData['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                        <?php if ($isAddCaseMode): ?>
                            <input type="hidden" name="child_gender" value="<?php echo htmlspecialchars($existingChildData['gender'] ?? ''); ?>">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Child Status *</label>
                        <select name="child_status" class="form-input" required <?php echo $isAddCaseMode ? 'disabled' : ''; ?>>
                            <option value="">Select Status</option>
                            <option value="In Care" <?php echo ($existingChildData['status'] ?? 'In Care') === 'In Care' ? 'selected' : ''; ?>>In Care</option>
                            <option value="Adoptable" <?php echo ($existingChildData['status'] ?? '') === 'Adoptable' ? 'selected' : ''; ?>>Adoptable</option>
                            <option value="Adopted" <?php echo ($existingChildData['status'] ?? '') === 'Adopted' ? 'selected' : ''; ?>>Adopted</option>
                            <option value="Reintegrated" <?php echo ($existingChildData['status'] ?? '') === 'Reintegrated' ? 'selected' : ''; ?>>Reintegrated</option>
                        </select>
                        <?php if ($isAddCaseMode): ?>
                            <input type="hidden" name="child_status" value="<?php echo htmlspecialchars($existingChildData['status'] ?? 'In Care'); ?>">
                        <?php endif; ?>
                    </div>
    
                    
                    <div class="form-group">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="child_birth_date" class="form-input"
                               value="<?php echo htmlspecialchars($existingChildData['date_of_birth'] ?? ''); ?>"
                               <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Entry Date *</label>
                        <input type="date" class="form-input" name="entry_date" value="<?php echo htmlspecialchars($existingChildData['entry_date'] ?? date('Y-m-d')); ?>"
                               required <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Address *</label>
                        <input type="text" name="child_address" class="form-input" 
                               value="<?php echo htmlspecialchars($existingChildData['address'] ?? ''); ?>"
                               required <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contact Phone</label>
                        <input type="tel" class="form-input" name="contact_phone" 
                               value="<?php echo htmlspecialchars($existingChildData['contact_phone'] ?? ''); ?>"
                               <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                    </div>
                </div>

                <!-- Health Status Dropdown -->
                <div class="form-group">
                    <label class="form-label">Health Status</label>
                    <select name="health_status" class="form-input" <?php echo $isAddCaseMode ? 'disabled' : ''; ?>>
                        <option value="">Select Health Status</option>
                        <option value="Excellent" <?php echo ($existingChildData['health_status'] ?? '') === 'Excellent' ? 'selected' : ''; ?>>Excellent</option>
                        <option value="Good" <?php echo ($existingChildData['health_status'] ?? 'Good') === 'Good' ? 'selected' : ''; ?>>Good</option>
                        <option value="Fair" <?php echo ($existingChildData['health_status'] ?? '') === 'Fair' ? 'selected' : ''; ?>>Fair</option>
                        <option value="Poor" <?php echo ($existingChildData['health_status'] ?? '') === 'Poor' ? 'selected' : ''; ?>>Poor</option>
                        <option value="Critical" <?php echo ($existingChildData['health_status'] ?? '') === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                        <option value="Under Observation" <?php echo ($existingChildData['health_status'] ?? '') === 'Under Observation' ? 'selected' : ''; ?>>Under Observation</option>
                        <option value="Chronic Condition" <?php echo ($existingChildData['health_status'] ?? '') === 'Chronic Condition' ? 'selected' : ''; ?>>Chronic Condition</option>
                        <option value="Recovering" <?php echo ($existingChildData['health_status'] ?? '') === 'Recovering' ? 'selected' : ''; ?>>Recovering</option>
                        <option value="Stable" <?php echo ($existingChildData['health_status'] ?? '') === 'Stable' ? 'selected' : ''; ?>>Stable</option>
                        <option value="Improving" <?php echo ($existingChildData['health_status'] ?? '') === 'Improving' ? 'selected' : ''; ?>>Improving</option>
                        <option value="Requires Medical Attention" <?php echo ($existingChildData['health_status'] ?? '') === 'Requires Medical Attention' ? 'selected' : ''; ?>>Requires Medical Attention</option>
                    </select>
                    <?php if ($isAddCaseMode): ?>
                        <input type="hidden" name="health_status" value="<?php echo htmlspecialchars($existingChildData['health_status'] ?? ''); ?>">
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Known Allergies</label>
                    <textarea name="allergies" class="form-input" rows="2" <?php echo $isAddCaseMode ? 'readonly' : ''; ?>><?php echo htmlspecialchars($existingChildData['allergies'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Emergency Contact</label>
                    <input type="text" class="form-input" name="emergency_contact" 
                           value="<?php echo htmlspecialchars($existingChildData['emergency_contact'] ?? ''); ?>"
                           <?php echo $isAddCaseMode ? 'readonly' : ''; ?>>
                </div>

                <div class="form-group">
                    <label class="form-label">Problem Description / Reason for Care</label>
                    <textarea name="problem_description" class="form-input" rows="4" <?php echo $isAddCaseMode ? 'readonly' : ''; ?>><?php echo htmlspecialchars($existingChildData['problem_description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="child_notes" class="form-input" rows="3" <?php echo $isAddCaseMode ? 'readonly' : ''; ?>><?php echo htmlspecialchars($existingChildData['notes'] ?? ''); ?></textarea>
                </div>
                <div class="form-section">
                    <h4 class="section-title">IDENTIFYING INFORMATION</h4>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" name="intake_date" class="form-input" 
                                   value="<?php echo htmlspecialchars($existingChildData['intake_date'] ?? date('Y-m-d')); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Civil Status</label>
                            <select name="civil_status" class="form-input">
                                <option value="">Select Civil Status</option>
                                <option value="Single" <?php echo ($existingChildData['civil_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                                <option value="Married" <?php echo ($existingChildData['civil_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                                <option value="Widowed" <?php echo ($existingChildData['civil_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                <option value="Divorced" <?php echo ($existingChildData['civil_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                <option value="Separated" <?php echo ($existingChildData['civil_status'] ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Birth Place</label>
                            <input type="text" name="birth_place" class="form-input" 
                                   value="<?php echo htmlspecialchars($existingChildData['birth_place'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Educational Attainment</label>
                            <select name="educational_attainment" class="form-input">
                                <option value="">Select Education</option>
                                <option value="No Formal Education" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'No Formal Education' ? 'selected' : ''; ?>>No Formal Education</option>
                                <option value="Elementary Level" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'Elementary Level' ? 'selected' : ''; ?>>Elementary Level</option>
                                <option value="Elementary Graduate" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'Elementary Graduate' ? 'selected' : ''; ?>>Elementary Graduate</option>
                                <option value="High School Level" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'High School Level' ? 'selected' : ''; ?>>High School Level</option>
                                <option value="High School Graduate" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'High School Graduate' ? 'selected' : ''; ?>>High School Graduate</option>
                                <option value="College Level" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'College Level' ? 'selected' : ''; ?>>College Level</option>
                                <option value="College Graduate" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'College Graduate' ? 'selected' : ''; ?>>College Graduate</option>
                                <option value="Vocational" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'Vocational' ? 'selected' : ''; ?>>Vocational</option>
                                <option value="Post Graduate" <?php echo ($existingChildData['educational_attainment'] ?? '') === 'Post Graduate' ? 'selected' : ''; ?>>Post Graduate</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Occupation</label>
                            <input type="text" name="occupation" class="form-input" 
                                   value="<?php echo htmlspecialchars($existingChildData['occupation'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Monthly Income</label>
                            <input type="text" name="monthly_income" class="form-input" 
                                   value="<?php echo htmlspecialchars($existingChildData['monthly_income'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Religion</label>
                            <input type="text" name="religion" class="form-input" 
                                   value="<?php echo htmlspecialchars($existingChildData['religion'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- Family Composition -->
                <div class="form-section">
                    <h4 class="section-title">FAMILY COMPOSITION</h4>
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
                                    <th>Occupation/Monthly Income</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="familyMembers">
                                <?php
                                $familyComposition = [];
                                if ($existingChildData && !empty($existingChildData['family_composition'])) {
                                    $familyComposition = json_decode($existingChildData['family_composition'], true);
                                }
                                
                                if (empty($familyComposition)) {
                                    // Default empty row
                                    echo '<tr>
                                        <td><input type="text" name="family_members[0][name]"></td>
                                        <td><input type="text" name="family_members[0][relationship]"></td>
                                        <td><input type="text" name="family_members[0][age]"></td>
                                        <td><input type="text" name="family_members[0][sex]"></td>
                                        <td><input type="text" name="family_members[0][civil_status]"></td>
                                        <td><input type="text" name="family_members[0][educational_attainment]"></td>
                                        <td><input type="text" name="family_members[0][occupation_income]"></td>
                                        <td><button type="button" onclick="removeFamilyMember(this)" class="btn-delete">Remove</button></td>
                                    </tr>';
                                } else {
                                    foreach ($familyComposition as $index => $member) {
                                        echo '<tr>
                                            <td><input type="text" name="family_members[' . $index . '][name]" value="' . htmlspecialchars($member['name'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][relationship]" value="' . htmlspecialchars($member['relationship'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][age]" value="' . htmlspecialchars($member['age'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][sex]" value="' . htmlspecialchars($member['sex'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][civil_status]" value="' . htmlspecialchars($member['civil_status'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][educational_attainment]" value="' . htmlspecialchars($member['educational_attainment'] ?? '') . '"></td>
                                            <td><input type="text" name="family_members[' . $index . '][occupation_income]" value="' . htmlspecialchars($member['occupation_income'] ?? '') . '"></td>
                                            <td><button type="button" onclick="removeFamilyMember(this)" class="btn-delete">Remove</button></td>
                                        </tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <button type="button" onclick="addFamilyMember()" style="margin-top: 10px; padding: 8px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Family Member</button>
                    </div>
                </div>

                <!-- Problem Presented -->
                <div class="form-section">
                    <h4 class="section-title">PROBLEM PRESENTED</h4>
                    <div class="form-group">
                        <textarea name="problem_presented" class="form-input" rows="6" placeholder="Describe the problem presented by the family..."><?php echo htmlspecialchars($existingChildData['problem_presented'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Assessment and Recommendation -->
                <div class="form-section">
                    <h4 class="section-title">ASSESSMENT AND RECOMMENDATION</h4>
                    <div class="form-group">
                        <textarea name="assessment_recommendation" class="form-input" rows="6" placeholder="Provide assessment and recommendations..."><?php echo htmlspecialchars($existingChildData['assessment_recommendation'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Case Information Tab -->
        <div id="caseTab" class="tab-content <?php echo $isAddCaseMode ? 'active' : ''; ?>">
            <div class="form-section">
                <h3>Case Details <?php echo ($editMode || $isAddCaseMode) ? '(Existing Record)' : ''; ?></h3>
                
                <?php if ($editMode && $existingCaseData): ?>
                <div class="existing-record-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"      class="bi bi-folder-check" viewBox="0 0 16 16">
                        <path d="m.5 3 .04.87a2 2 0 0 0-.342 1.311l.637 7A2 2 0 0 0 2.826 14H9v-1H2.826a1 1 0 0 1-.995-.91l-.637-7A1 1 0 0 1 2.19 4h11.62a1 1 0 0 1 .996 1.09L14.54 8h1.005l.256-2.819A2 2 0 0 0 13.81 3H9.828a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 6.172 1H2.5a2 2 0 0 0-2 2m5.672-1a1 1 0 0 1 .707.293L7.586 3H2.19q-.362.002-.683.12L1.5 2.98a1 1 0 0 1 1-.98z"/>
                        <path d="M15.854 10.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.707 0l-1.5-1.5a.5.5 0 0 1 .707-.708l1.146 1.147 2.646-2.647a.5.5 0 0 1 .708 0"/>
                    </svg>
                    <strong>Viewing existing case record:</strong> This case is linked to the child above.
                </div>
                <?php elseif ($isAddCaseMode): ?>
                <div class="existing-record-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                    <strong>Adding New Case:</strong> You are creating a new case record for the existing child shown above.
                </div>
                <?php elseif (!$editMode && !$isAddCaseMode): ?>
                <div class="existing-record-notice">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-diff" viewBox="0 0 16 16">
                <path d="M8 5a.5.5 0 0 1 .5.5V7H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V8H6a.5.5 0 0 1 0-1h1.5V5.5A.5.5 0 0 1 8 5m-2.5 6.5A.5.5 0 0 1 6 11h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                </svg>
                    <strong>Optional Information:</strong> Case information is optional. You can create a child record without creating a case.
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">Case Type</label>
                    <select name="case_type" class="form-select">
                        <option value="">Select Case Type (Optional)</option>
                        <option value="Physical Abuse" <?php echo ($existingCaseData['case_type'] ?? '') === 'Physical Abuse' ? 'selected' : ''; ?>>Physical Abuse</option>
                        <option value="Sexual Abuse" <?php echo ($existingCaseData['case_type'] ?? '') === 'Sexual Abuse' ? 'selected' : ''; ?>>Sexual Abuse</option>
                        <option value="Neglect" <?php echo ($existingCaseData['case_type'] ?? '') === 'Neglect' ? 'selected' : ''; ?>>Neglect</option>
                        <option value="Abandonment" <?php echo ($existingCaseData['case_type'] ?? '') === 'Abandonment' ? 'selected' : ''; ?>>Abandonment</option>
                        <option value="Exploitation" <?php echo ($existingCaseData['case_type'] ?? '') === 'Exploitation' ? 'selected' : ''; ?>>Exploitation</option>
                        <option value="Special Laws" <?php echo ($existingCaseData['case_type'] ?? '') === 'Special Laws' ? 'selected' : ''; ?>>Special Laws</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority Level</label>
                    <div class="priority-buttons">
                        <button type="button" class="priority-btn urgent <?php echo ($existingCaseData['priority'] ?? '') === 'urgent' ? 'active' : ''; ?>" onclick="setPriority('urgent')">URGENT</button>
                        <button type="button" class="priority-btn mild <?php echo ($existingCaseData['priority'] ?? '') === 'mild' ? 'active' : ''; ?>" onclick="setPriority('mild')">MILD</button>
                        <button type="button" class="priority-btn common <?php echo ($existingCaseData['priority'] ?? 'common') === 'common' ? 'active' : ''; ?>" onclick="setPriority('common')">COMMON</button>
                    </div>
                    <input type="hidden" name="priority" id="priorityInput" value="<?php echo htmlspecialchars($existingCaseData['priority'] ?? 'common'); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Case Status</label>
                    <select name="status" class="form-select">
                        <option value="Open" <?php echo ($existingCaseData['status'] ?? 'Open') === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="Under Investigation" <?php echo ($existingCaseData['status'] ?? '') === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                        <option value="Court Action Pending" <?php echo ($existingCaseData['status'] ?? '') === 'Court Action Pending' ? 'selected' : ''; ?>>Court Action Pending</option>
                        <option value="Closed" <?php echo ($existingCaseData['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Case Description</label>
                    <textarea name="case_description" class="form-textarea" rows="6" placeholder="Provide detailed description of the case, circumstances, and any relevant information... (Optional)"><?php echo htmlspecialchars($existingCaseData['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Reported By</label>
                        <input type="text" class="form-input" name="reported_by" 
                               value="<?php echo htmlspecialchars($existingCaseData['reported_by'] ?? ''); ?>"
                               placeholder="Name of person reporting (Optional)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Relation to Child</label>
                        <input type="text" class="form-input" name="reporter_relation" 
                               value="<?php echo htmlspecialchars($existingCaseData['reporter_relation'] ?? ''); ?>"
                               placeholder="Relationship to child (Optional)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reporter Phone</label>
                        <input type="tel" class="form-input" name="reporter_phone" 
                               value="<?php echo htmlspecialchars($existingCaseData['reporter_phone'] ?? ''); ?>"
                               placeholder="Phone number (Optional)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reporter Email</label>
                        <input type="email" class="form-input" name="reporter_email" 
                               value="<?php echo htmlspecialchars($existingCaseData['reporter_email'] ?? ''); ?>"
                               placeholder="Email address (Optional)">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Expected Date</label>
                        <input type="date" class="form-input" name="expected_date" 
                               value="<?php echo htmlspecialchars($existingCaseData['expected_date'] ?? date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Assigned Social Worker</label>
                        <select name="social_worker" class="form-select">
                            <option value="">Select Social Worker (Optional)</option>
                            <option value="maria-santos" <?php echo ($existingCaseData['social_worker'] ?? '') === 'maria-santos' ? 'selected' : ''; ?>>Maria Santos</option>
                            <option value="juan-cruz" <?php echo ($existingCaseData['social_worker'] ?? '') === 'juan-cruz' ? 'selected' : ''; ?>>Juan Cruz</option>
                            <option value="lisa-gonzalez" <?php echo ($existingCaseData['social_worker'] ?? '') === 'lisa-gonzalez' ? 'selected' : ''; ?>>Lisa Gonzalez</option>
                            <option value="carlos-reyes" <?php echo ($existingCaseData['social_worker'] ?? '') === 'carlos-reyes' ? 'selected' : ''; ?>>Carlos Reyes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($fieldManager && (!empty($childCustomFields) || !empty($caseCustomFields))): ?>
            <div id="customTab" class="tab-content">
                <div class="form-section">
                    <h3>Additional Custom Fields</h3>
                    <p class="help-text">These are additional fields that can be configured by the system administrator.</p>
                    
                    <!-- Children Module Custom Fields -->
                    <?php if (!empty($childCustomFields) && !$isAddCaseMode): ?>
                    <div class="custom-fields-section">
                        <h4>Child Additional Information</h4>
                        <div class="form-grid">
                            <?php foreach ($childCustomFields as $field): 
                                $existingValue = $existingChildCustomValues[$field['field_name']] ?? '';
                                // Use underscore format for field names to match the processing logic
                                echo str_replace(
                                    'name="custom_field[' . $field['field_name'] . ']"',
                                    'name="custom_field_' . $field['field_name'] . '"',
                                    $fieldManager->renderField($field, $existingValue)
                                );
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Cases Module Custom Fields -->
                    <?php if (!empty($caseCustomFields)): ?>
                    <div class="custom-fields-section">
                        <h4>Case Additional Information</h4>
                        <div class="form-grid">
                            <?php foreach ($caseCustomFields as $field): 
                                $existingValue = $existingCaseCustomValues[$field['field_name']] ?? '';
                                // Use underscore format for field names to match the processing logic
                                echo str_replace(
                                    'name="custom_field[' . $field['field_name'] . ']"',
                                    'name="custom_field_' . $field['field_name'] . '"',
                                    $fieldManager->renderField($field, $existingValue)
                                );
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="window.location.href='child-management.php'">Cancel</button>
            <?php if (!$editMode && !$isAddCaseMode): ?>
                <button type="submit" class="btn-submit" id="submitBtn">Create Record</button>
            <?php elseif ($isAddCaseMode): ?>
                <button type="submit" class="btn-submit" id="submitBtn">Add Case</button>
            <?php else: ?>
                <button type="submit" class="btn-submit" id="submitBtn">Update Record</button>
            <?php endif; ?>
        </div>
    </form>
</main>

<style>

/* ... your existing CSS styles remain exactly the same ... */
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

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

.loading-text {
    font-size: 16px;
    font-weight: 500;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Family Intake Specific Styles */
.family-intake-section {
    margin-bottom: 30px;
}

.family-intake-section h4 {
    color: #3b82f6;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3b82f6;
    font-size: 18px;
}

.light-theme .section-title {
    color: #1a2744;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
    letter-spacing: -0.3px;
}

.dark-theme .table-container {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
}

.light-theme .table-container {
    overflow-x: auto;
    border-radius: 8px;

}

.dark-theme #familyTable {
    width: 100%;
    border-collapse: collapse;
    background: var(--table-bg, #1a1a1a);
}

.light-theme #familyTable {
    width: 100%;
    border-collapse: collapse;
}

.dark-theme #familyTable th {
    background: var(--table-header-bg, #333333);
    color: var(--table-header-color, #b8c5ff);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #3a3a3a;
}

.light-theme #familyTable th {
    color: rgb(255, 255, 255);
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    padding: 12px 8px;
    text-align: left;
    border: 1px solid #3a3a3a;
}

.light-theme .photo-upload-text {
    color: #2d5f8d;
    font-weight: 600;
    font-size: 14px;
}



#familyTable td {
    padding: 8px;
    border: 1px solid #3a3a3a;
    position: relative;
}

.dark-theme #familyTable input {
    width: 100%;
    border: none;
    padding: 8px;
    background: transparent;
    color: white;
    font-size: 13px;
}

.light-theme #familyTable input {
    width: 100%;
    border: none;
    padding: 8px;
    color: black;
    font-size: 13px;
}

.dark-theme #familyTable input:focus {
    outline: none;
    background: #2a2a2a;
}

.light-theme #familyTable input:focus {
    outline: none;
    
}

.btn-delete {
    padding: 6px 12px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.btn-delete:hover {
    background: #c82333;
}

/* Your existing CSS styles remain the same */
.checkbox-option {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    padding: 8px;
    background: #2a2a2a;
    border-radius: 6px;
}

.checkbox-option input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
}

.checkbox-option label {
    color: #cccccc;
    font-weight: 500;
    margin-bottom: 0;
    cursor: pointer;
}

.checkbox-option:hover {
}

.checkbox-option input[type="checkbox"]:checked + label {
    color: #3b82f6;
    font-weight: 600;
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
}

.custom-field .radio-option input,
.custom-field .checkbox-option input {
    margin-right: 8px;
}

.light-theme .photo-upload-area {
    border: 2px dashed #2d5f8d;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #f0f6fb;
    margin-bottom: 24px;
}

.light-theme .photo-upload-area:hover {
    background: #e8f2f9;
    border-color: #1f5a8d;
}

.light-theme h3{
    color: #1a2744;
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: 16px;
}

.light-theme .unified-form {
    background: white;
    border-radius: 12px;
    padding: 24px;
}

.form-tabs {
    display: flex;
    margin-bottom: 24px;
}

.dark-theme .tab-btn {
    background: none;
    border: none;
    color: #888;
    padding: 12px 24px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    font-weight: 500;
}


.light-theme .tab-btn {
    background: none;
    border: none;
    color: #2d5f8d;
    padding: 12px 24px;
    cursor: pointer;

    font-weight: 600;
    font-size: 13px;

}

.tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    border-bottom: 2px solid #2d5f8d;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.priority-buttons {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}

.priority-btn {
    flex: 1;
    padding: 12px;
    border: 2px solid #3a3a3a;
    border-radius: 6px;
    background: #333;
    color: white;
    cursor: pointer;
    font-weight: 500;
}

.priority-btn.active {
    border-width: 3px;
    font-weight: 600;
}

.priority-btn.urgent.active {
    background: #f8d7da;
    color: #721c24;
}

.priority-btn.mild.active {
    color: #856404;
    background: #fff3cd
}

.priority-btn.common.active {
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

.current-file {
    margin-top: 5px;
    color: #28a745;
    font-size: 12px;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.existing-record-notice {
    background: #e7f3ff;
    border: 1px solid #b8daff;
    color: #004085;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

.form-section {
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    color: #b8c5ff;
    font-weight: 500;
    margin-bottom: 5px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
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
    gap: 12px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-cancel {
    background: white;
    color: #546e7a;
    border: 1px solid #dfe7f1;
}

.btn-submit {
    background: #2d7a3e;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
}

.btn-cancel:hover {
    background: #f8fafb;
    border-color: #2d5f8d;
    color: #2d5f8d;
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(45, 122, 62, 0.25);
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* For better mobile responsiveness */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentPriority = 'common';

<?php if (($editMode || $isAddCaseMode) && isset($existingCaseData['priority'])): ?>
    currentPriority = '<?php echo $existingCaseData['priority']; ?>';
<?php endif; ?>

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

// Loading overlay
function showLoading(message = 'Processing...') {
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.id = 'loadingOverlay';
    overlay.innerHTML = `
        <div class="loading-spinner"></div>
        <div class="loading-text">${message}</div>
    `;
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const uploadArea = input.closest('.photo-upload-area');
            uploadArea.querySelector('.photo-upload-text').innerHTML = `<img src="${e.target.result}" style="max-width: 100px; max-height: 100px;">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
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

function setPriority(priority) {
    currentPriority = priority;
    document.getElementById('priorityInput').value = priority;
    
    // Update button styles
    document.querySelectorAll('.priority-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// In the validateUnifiedForm() function, add this after the gender validation:
const childStatus = document.querySelector('select[name="child_status"]');

if (!childStatus.value) {
    errors.push('Child Status is required');
    if (childStatus) childStatus.classList.add('field-error');
}
// SIMPLIFIED FORM VALIDATION - NO CASE VALIDATION
function validateUnifiedForm() {
    const errors = [];
    
    // Clear previous error styles
    document.querySelectorAll('.field-error').forEach(field => {
        field.classList.remove('field-error');
    });
    
    <?php if (!$isAddCaseMode): ?>
    // Only validate absolutely required fields for child
    const childAge = document.querySelector('input[name="child_age"]');
    const childGender = document.querySelector('select[name="child_gender"]');
    const entryDate = document.querySelector('input[name="entry_date"]');
    const address = document.querySelector('input[name="child_address"]');
    
    // Basic validation
    if (!childAge.value || childAge.value < 0 || childAge.value > 18) {
        errors.push('Valid Child Age (0-18) is required');
        if (childAge) childAge.classList.add('field-error');
    }
    
    if (!childGender.value) {
        errors.push('Child Gender is required');
        if (childGender) childGender.classList.add('field-error');
    }
    
    if (!entryDate.value) {
        errors.push('Entry Date is required');
        if (entryDate) entryDate.classList.add('field-error');
    }
    
    if (!address.value || address.value.trim() === '') {
        errors.push('Address is required');
        if (address) address.classList.add('field-error');
    }
    <?php endif; ?>
    
    // NO CASE VALIDATION - Case fields are optional
    
    return errors;
}

// Initialize priority on page load
document.addEventListener('DOMContentLoaded', function() {
    const priorityInput = document.getElementById('priorityInput');
    if (priorityInput && !priorityInput.value) {
        priorityInput.value = 'common';
    }
    
    // Set default priority button as active
    if (currentPriority) {
        document.querySelectorAll('.priority-btn').forEach(btn => {
            if (btn.classList.contains(currentPriority)) {
                btn.classList.add('active');
            }
        });
    }
    
    <?php if ($isAddCaseMode): ?>
    // Auto-switch to case tab in add case mode
    switchTab('case');
    <?php endif; ?>
});

// Form submission handler
document.getElementById('unifiedForm').addEventListener('submit', function(e) {
    console.log('Form submission started');
    
    const errors = validateUnifiedForm();
    
    if (errors.length > 0) {
        e.preventDefault();
        console.log('Validation errors:', errors);
        
        // Show errors in a user-friendly way
        const errorHtml = errors.map(error => `<li>${error}</li>`).join('');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'notification error show';
        errorDiv.innerHTML = `
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Please fix the following errors:</div>
                <div class="notification-message"><ul>${errorHtml}</ul></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        // Remove existing error alerts
        const existingAlerts = document.querySelectorAll('.notification.error');
        existingAlerts.forEach(alert => alert.remove());
        
        // Insert new error alert at the top of the form
        const form = document.getElementById('unifiedForm');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(errorDiv, form);
        }
        
        // Scroll to top
        window.scrollTo(0, 0);
        
        return false;
    }
    
    console.log('Form validation passed, submitting...');
    
    // Show loading overlay
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
    }
    
    showLoading('Saving your information...');
    
    // Auto-hide loading after 30 seconds as fallback
    setTimeout(() => {
        hideLoading();
        if (submitBtn) {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }, 30000);
    
    return true;
});

// Hide loading when page unloads (in case of redirect)
window.addEventListener('beforeunload', function() {
    hideLoading();
});

// Debug: Log form data on submit
document.getElementById('unifiedForm').addEventListener('submit', function(e) {
    const formData = new FormData(this);
    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>