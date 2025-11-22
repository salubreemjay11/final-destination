<?php
session_start();

// Database configuration - use the same as your admin pages
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Include CustomFieldManager - adjust path based on your structure
$customFieldManagerPath = $_SERVER['DOCUMENT_ROOT'] . '/orphanfare/superadmin/includes/CustomFieldManager.php';
if (!file_exists($customFieldManagerPath)) {
    // Try alternative path
    $customFieldManagerPath = '../superadmin/includes/CustomFieldManager.php';
}

if (!file_exists($customFieldManagerPath)) {
    echo json_encode(['success' => false, 'message' => 'CustomFieldManager not found at: ' . $customFieldManagerPath]);
    exit();
}

require_once $customFieldManagerPath;

// Initialize field manager
$fieldManager = new CustomFieldManager($pdo);

if ($_POST['action'] === 'get_child_custom_fields') {
    $childId = $_POST['child_id'] ?? '';
    
    if (empty($childId)) {
        echo json_encode(['success' => false, 'message' => 'Child ID required']);
        exit();
    }
    
    try {
        // First, fix any field type issues
        $fieldManager->fixFieldTypesAutomatically();
        
        // Get custom field values for this child
        $customFields = $fieldManager->getFieldValues($childId, 'children');
        
        error_log("Custom fields retrieved for child $childId: " . print_r($customFields, true));
        
        echo json_encode([
            'success' => true,
            'fields' => $customFields
        ]);
        
    } catch (Exception $e) {
        error_log("Error in get_child_custom_fields: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error loading custom fields: ' . $e->getMessage()
        ]);
    }
}
?>