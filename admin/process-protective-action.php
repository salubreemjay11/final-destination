<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();
$currentUser = getCurrentUser();

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_action'])) {
    
    error_log("DEBUG: Processing protective action request");
    
    if (!isset($_SESSION['protective_action_data'])) {
        error_log("DEBUG: No session data found");
        $_SESSION['error_message'] = "Session data lost. Please start over.";
        header('Location: initiate-protective-action.php');
        exit();
    }

    $selectedCaseId = $_POST['selected_case'] ?? '';
    $data = $_SESSION['protective_action_data'];

    if (empty($selectedCaseId)) {
        error_log("DEBUG: Missing case selection");
        $_SESSION['error_message'] = "No case selected.";
        header('Location: select-person.php');
        exit();
    }

    // Get selected case
    $stmt = $pdo->prepare("SELECT case_id, child_name FROM cases WHERE case_id = ?");
    $stmt->execute([$selectedCaseId]);
    $selectedCase = $stmt->fetch();

    if (!$selectedCase) {
        error_log("DEBUG: Case not found: " . $selectedCaseId);
        $_SESSION['error_message'] = "Selected case not found: " . $selectedCaseId;
        header('Location: select-person.php');
        exit();
    }

    try {
        // Generate unique action_id
        $action_id = 'ACT-' . date('YmdHis') . rand(100, 999);
        error_log("DEBUG: Generated action_id: " . $action_id);

        // Handle notifications - simple string conversion
        $notificationsValue = '';
        if (isset($data['notifications']) && is_array($data['notifications'])) {
            $notificationsValue = implode(', ', $data['notifications']);
        }

        // If notifications is empty, set a default value
        if (empty($notificationsValue)) {
            $notificationsValue = 'No notifications selected';
        }

        // Prepare the insert statement
        $stmt = $pdo->prepare("
            INSERT INTO protective_actions 
            (action_id, case_id, case_type, action_type, priority, justification, coordinating_officer, 
             notifications, case_description, followup_date, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?, NOW())
        ");

        error_log("DEBUG: Attempting to insert protective action");
        error_log("DEBUG - action_id: " . $action_id);
        error_log("DEBUG - case_id: " . $selectedCaseId);
        error_log("DEBUG - case_type: " . $data['case_type']);
        error_log("DEBUG - action_type: " . $data['action_type']);
        error_log("DEBUG - notifications: " . $notificationsValue);

        $result = $stmt->execute([
            $action_id,
            $selectedCaseId,
            $data['case_type'],
            $data['action_type'],
            $data['priority'],
            $data['justification'],
            $data['coordinating_officer'],
            $notificationsValue,
            $data['case_description'] ?? '',
            $data['followup_date'] ?? null,
            $currentUser['id']
        ]);

        if ($result) {
            error_log("SUCCESS: Protective action inserted successfully");
            
            // Update case status
            $updateStmt = $pdo->prepare("UPDATE cases SET status = 'Protective Action Active' WHERE case_id = ?");
            $updateStmt->execute([$selectedCaseId]);
            
            // Log activity
            logActivity($currentUser['id'], 'Protective Action Initiated', 'protective_actions', $action_id);
            
            // Clear session data
            unset($_SESSION['protective_action_data']);
            
            error_log("SUCCESS: Redirecting to action-successfully.php");
            header("Location: action-successfully.php?action_id=" . urlencode($action_id));
            exit();
        } else {
            $errorInfo = $stmt->errorInfo();
            $error = "Database insert failed: " . $errorInfo[2];
            error_log("ERROR: Insert failed - " . $error);
            
            $_SESSION['error_message'] = $error;
            header("Location: confirm-action-person.php?selected_case=" . urlencode($selectedCaseId));
            exit();
        }

    } catch (Exception $e) {
        $error = 'Failed to initiate protective action: ' . $e->getMessage();
        error_log("EXCEPTION: " . $e->getMessage());
        
        $_SESSION['error_message'] = $error;
        header("Location: confirm-action-person.php?selected_case=" . urlencode($selectedCaseId));
        exit();
    }
} else {
    error_log("DEBUG: Invalid access to process-protective-action.php");
    header('Location: initiate-protective-action.php');
    exit();
}
?>