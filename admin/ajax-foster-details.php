<?php
// ajax-foster-details.php - SIMPLE VERSION
define('IN_AJAX', true);

// Use the exact same require pattern as foster-info.php
require_once 'includes/header.php';

header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_foster_details') {
    try {
        $fosterId = $_POST['foster_id'] ?? '';
        
        if (empty($fosterId)) {
            echo json_encode(['success' => false, 'message' => 'Foster ID is required']);
            exit;
        }
        
        // Get all foster details
        $stmt = $pdo->prepare("SELECT * FROM foster_parents WHERE foster_id = ?");
        $stmt->execute([$fosterId]);
        $foster = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$foster) {
            echo json_encode(['success' => false, 'message' => 'Foster parent not found']);
            exit;
        }
        
        // Return success with foster data
        echo json_encode([
            'success' => true, 
            'foster' => $foster
        ]);
        
    } catch (Exception $e) {
        error_log("AJAX foster details error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error loading foster details'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>