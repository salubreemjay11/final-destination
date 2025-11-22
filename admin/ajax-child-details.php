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

if (!isset($_POST['action']) || $_POST['action'] !== 'get_child_details') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

if (!isset($_POST['child_id'])) {
    echo json_encode(['success' => false, 'message' => 'Child ID required']);
    exit();
}

// Check permissions - CORRECT PATH
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    require_once 'includes/permissions.php';
    $tempPermissionManager = new PermissionManager($pdo, $_SESSION['role'], $_SESSION['user_id']);
    if (!$tempPermissionManager->hasPermission('child_management', 'view')) {
        echo json_encode(['success' => false, 'message' => 'Permission denied - No access to child details']);
        exit();
    }
}

$childId = $_POST['child_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM children WHERE child_id = ?");
    $stmt->execute([$childId]);
    $child = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($child) {
        echo json_encode(['success' => true, 'child' => $child]);
    } else {
        echo json_encode(['success' => false, 'message' => "Child with ID '$childId' not found in database"]);
    }
} catch (Exception $e) {
    error_log("Child details error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
exit();
?>