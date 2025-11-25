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
    'assessment_recommendation' => $_POST['assessment_recommendation'] ?? ''
];

try {
    $stmt = $pdo->prepare("
        UPDATE children SET 
        age = ?, gender = ?, status = ?, date_of_birth = ?, entry_date = ?, address = ?, 
        health_status = ?, allergies = ?, emergency_contact = ?, 
        problem_description = ?, notes = ?, 
        civil_status = ?, birth_place = ?, educational_attainment = ?, occupation = ?, 
        monthly_income = ?, religion = ?, problem_presented = ?, assessment_recommendation = ?,
        updated_at = NOW()
        WHERE child_id = ?
    ");
    
    $result = $stmt->execute([
        $data['age'], $data['gender'], $data['status'], $data['date_of_birth'], 
        $data['entry_date'], $data['address'], $data['health_status'], $data['allergies'], 
        $data['emergency_contact'], $data['problem_description'], $data['notes'],
        $data['civil_status'], $data['birth_place'], $data['educational_attainment'], $data['occupation'],
        $data['monthly_income'], $data['religion'], $data['problem_presented'], $data['assessment_recommendation'],
        $childId
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