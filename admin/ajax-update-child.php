<?php
// Start session and database connection
session_start();
require_once '../config/database.php';

// Set JSON header immediately
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['action']) || $_POST['action'] !== 'update_child') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

// Check permissions - CORRECT PATH
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    require_once 'includes/permissions.php';
    $tempPermissionManager = new PermissionManager($pdo, $_SESSION['role'], $_SESSION['user_id']);
    if (!$tempPermissionManager->hasPermission('child_management', 'edit')) {
        echo json_encode(['success' => false, 'message' => 'Permission denied - You cannot edit children']);
        exit();
    }
}

$childId = $_POST['child_id'];

// Validate required fields - REMOVED NAME VALIDATION
if (empty($_POST['age']) || empty($_POST['gender']) || empty($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Age, gender, and status are required fields']);
    exit();
}

// Collect all data including vaccine fields
$data = [
    'age' => $_POST['age'],
    'gender' => $_POST['gender'],
    'status' => $_POST['status'],
    'date_of_birth' => $_POST['date_of_birth'] ?? null,
    'entry_date' => $_POST['entry_date'] ?? null,
    'address' => $_POST['address'] ?? '',
    'health_status' => $_POST['health_status'] ?? '',
    'allergies' => $_POST['allergies'] ?? '',
    'emergency_contact' => $_POST['emergency_contact'] ?? '',
    'problem_description' => $_POST['problem_description'] ?? '',
    'notes' => $_POST['notes'] ?? '',
    'civil_status' => $_POST['civil_status'] ?? '',
    'birth_place' => $_POST['birth_place'] ?? '',
    'educational_attainment' => $_POST['educational_attainment'] ?? '',
    'occupation' => $_POST['occupation'] ?? '',
    'monthly_income' => $_POST['monthly_income'] ?? '',
    'religion' => $_POST['religion'] ?? '',
    'problem_presented' => $_POST['problem_presented'] ?? '',
    'assessment_recommendation' => $_POST['assessment_recommendation'] ?? '',
    
    // Vaccine fields - handle checkboxes properly
   'vaccine_bcg' => isset($_POST['vaccine_bcg']) && $_POST['vaccine_bcg'] === '1' ? 1 : 0,
    'vaccine_hepb' => isset($_POST['vaccine_hepb']) && $_POST['vaccine_hepb'] === '1' ? 1 : 0,
    'vaccine_dtap' => isset($_POST['vaccine_dtap']) && $_POST['vaccine_dtap'] === '1' ? 1 : 0,
    'vaccine_polio' => isset($_POST['vaccine_polio']) && $_POST['vaccine_polio'] === '1' ? 1 : 0,
    'vaccine_pcv' => isset($_POST['vaccine_pcv']) && $_POST['vaccine_pcv'] === '1' ? 1 : 0,
    'vaccine_rota' => isset($_POST['vaccine_rota']) && $_POST['vaccine_rota'] === '1' ? 1 : 0,
    'vaccine_measles' => isset($_POST['vaccine_measles']) && $_POST['vaccine_measles'] === '1' ? 1 : 0,
    'vaccine_varicella' => isset($_POST['vaccine_varicella']) && $_POST['vaccine_varicella'] === '1' ? 1 : 0,
    'vaccine_hepa' => isset($_POST['vaccine_hepa']) && $_POST['vaccine_hepa'] === '1' ? 1 : 0,
    'vaccine_mmr' => isset($_POST['vaccine_mmr']) && $_POST['vaccine_mmr'] === '1' ? 1 : 0,
    'vaccine_other' => $_POST['vaccine_other'] ?? '',
    'vaccine_notes' => $_POST['vaccine_notes'] ?? '',
    'previous_family_env' => $_POST['previous_family_env'] ?? '',
    'vaccination_status' => $_POST['vaccination_status'] ?? '',
    'cf_medical_history' => $_POST['cf_medical_history'] ?? '',
    'cf_educational_level' => $_POST['cf_educational_level'] ?? '',
    'cf_special_needs' => $_POST['cf_special_needs'] ?? '',
    'cf_hobbies' => $_POST['cf_hobbies'] ?? '',
    'cf_school_name' => $_POST['cf_school_name'] ?? '',
    'cf_grade_level' => $_POST['cf_grade_level'] ?? '',
    'contact_phone' => $_POST['contact_phone'] ?? ''
];

try {
    // Update children table with vaccine fields
    $stmt = $pdo->prepare("
        UPDATE children SET 
        age = ?, gender = ?, status = ?, date_of_birth = ?, entry_date = ?, address = ?, 
        health_status = ?, allergies = ?, emergency_contact = ?, contact_phone = ?,
        problem_description = ?, notes = ?, 
        civil_status = ?, birth_place = ?, educational_attainment = ?, occupation = ?, 
        monthly_income = ?, religion = ?, problem_presented = ?, assessment_recommendation = ?,
        vaccine_bcg = ?, vaccine_hepb = ?, vaccine_dtap = ?, vaccine_polio = ?, vaccine_pcv = ?,
        vaccine_rota = ?, vaccine_measles = ?, vaccine_varicella = ?, vaccine_hepa = ?, vaccine_mmr = ?,
        vaccine_other = ?, vaccine_notes = ?, previous_family_env = ?, vaccination_status = ?,
        cf_medical_history = ?, cf_educational_level = ?, cf_special_needs = ?, cf_hobbies = ?,
        cf_school_name = ?, cf_grade_level = ?, updated_at = NOW()
        WHERE child_id = ?
    ");
    
    $result = $stmt->execute([
        $data['age'], $data['gender'], $data['status'], $data['date_of_birth'], 
        $data['entry_date'], $data['address'], $data['health_status'], $data['allergies'], 
        $data['emergency_contact'], $data['contact_phone'], $data['problem_description'], 
        $data['notes'], $data['civil_status'], $data['birth_place'], $data['educational_attainment'], 
        $data['occupation'], $data['monthly_income'], $data['religion'], $data['problem_presented'], 
        $data['assessment_recommendation'], $data['vaccine_bcg'], $data['vaccine_hepb'], 
        $data['vaccine_dtap'], $data['vaccine_polio'], $data['vaccine_pcv'], $data['vaccine_rota'], 
        $data['vaccine_measles'], $data['vaccine_varicella'], $data['vaccine_hepa'], $data['vaccine_mmr'], 
        $data['vaccine_other'], $data['vaccine_notes'], $data['previous_family_env'], $data['vaccination_status'],
        $data['cf_medical_history'], $data['cf_educational_level'], $data['cf_special_needs'], 
        $data['cf_hobbies'], $data['cf_school_name'], $data['cf_grade_level'], $childId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Child updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update child']);
    }
} catch (Exception $e) {
    error_log("Update child error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// Handle custom fields if CustomFieldManager is available
if (isset($_POST['custom_fields'])) {
    try {
        // Check if we can access the CustomFieldManager
        $customFieldsPath = '../superadmin/includes/CustomFieldManager.php';
        if (file_exists($customFieldsPath)) {
            require_once $customFieldsPath;
            $fieldManager = new CustomFieldManager($pdo);
            
            $customFields = json_decode($_POST['custom_fields'], true);
            if ($customFields && is_array($customFields)) {
                foreach ($customFields as $fieldName => $fieldValue) {
                    $fieldManager->saveFieldValue($childId, 'children', $fieldName, $fieldValue);
                    error_log("Saved custom field via AJAX: $fieldName = $fieldValue");
                }
            }
        }
    } catch (Exception $e) {
        error_log("Custom field save error: " . $e->getMessage());
    }
}
exit();
?>