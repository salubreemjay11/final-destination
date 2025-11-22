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

// Validate required fields
if (empty($_POST['name']) || empty($_POST['age']) || empty($_POST['gender'])) {
    echo json_encode(['success' => false, 'message' => 'Name, age, and gender are required fields']);
    exit();
}

$data = [
    'name' => $_POST['name'],
    'age' => $_POST['age'],
    'gender' => $_POST['gender'],
    'date_of_birth' => $_POST['date_of_birth'] ?? null,
    'address' => $_POST['address'] ?? '',
    'health_status' => $_POST['health_status'] ?? '',
    'allergies' => $_POST['allergies'] ?? '',
    'emergency_contact' => $_POST['emergency_contact'] ?? '',
    'problem_description' => $_POST['problem_description'] ?? '',
    'notes' => $_POST['notes'] ?? ''
];

try {
    $stmt = $pdo->prepare("
        UPDATE children SET 
        name = ?, age = ?, gender = ?, date_of_birth = ?, address = ?, 
        health_status = ?, allergies = ?, emergency_contact = ?, 
        problem_description = ?, notes = ?, updated_at = NOW()
        WHERE child_id = ?
    ");
    
    $result = $stmt->execute([
        $data['name'], $data['age'], $data['gender'], $data['date_of_birth'], 
        $data['address'], $data['health_status'], $data['allergies'], 
        $data['emergency_contact'], $data['problem_description'], 
        $data['notes'], $childId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Child updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update child']);
    }
} catch (Exception $e) {
    error_log("Update child error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit();
?>