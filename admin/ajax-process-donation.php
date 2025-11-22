<?php
// ajax-process-donation.php - Standalone version with direct database connection

// Enable error reporting but don't display to users
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering and clean any previous output
if (ob_get_level()) ob_end_clean();
ob_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Basic session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Simple authentication check
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        throw new Exception('Not authenticated');
    }

    // Create database connection directly
    $host = '127.0.0.1'; // or 'localhost'
    $dbname = 'orphanfare';
    $username = 'root'; // Change if different
    $password = ''; // Change if you have a password
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_status':
            handleUpdateStatus($pdo, $_SESSION['user_id']);
            break;
            
        case 'cancel_donation':
            handleCancelDonation($pdo, $_SESSION['user_id']);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    // Clear any output and send error JSON
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

function handleUpdateStatus($pdo, $user_id) {
    $donationId = $_POST['donation_id'] ?? '';
    $newStatus = $_POST['status'] ?? '';
    
    // Validate inputs
    if (empty($donationId) || empty($newStatus)) {
        throw new Exception('Missing donation ID or status');
    }
    
    // FIXED: Updated valid statuses to match frontend
    $validStatuses = ['Received', 'Completed'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception('Invalid status value: ' . $newStatus);
    }
    
    // Check if donation exists
    $stmt = $pdo->prepare("SELECT donor_name, status as old_status FROM donations WHERE donation_id = ?");
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch();
    
    if (!$donation) {
        throw new Exception('Donation not found: ' . $donationId);
    }
    
    // Update the status
    $stmt = $pdo->prepare("UPDATE donations SET status = ?, updated_at = NOW() WHERE donation_id = ?");
    $result = $stmt->execute([$newStatus, $donationId]);
    
    if ($result) {
        // Simple logging
        error_log("User $user_id updated donation $donationId status to $newStatus");
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Database update failed');
    }
}

function handleCancelDonation($pdo, $user_id) {
    $donationId = $_POST['donation_id'] ?? '';
    
    if (empty($donationId)) {
        throw new Exception('Missing donation ID');
    }
    
    // Check if donation exists
    $stmt = $pdo->prepare("SELECT donor_name, description FROM donations WHERE donation_id = ?");
    $stmt->execute([$donationId]);
    $donation = $stmt->fetch();
    
    if (!$donation) {
        throw new Exception('Donation not found: ' . $donationId);
    }
    
    // Delete the donation
    $stmt = $pdo->prepare("DELETE FROM donations WHERE donation_id = ?");
    $result = $stmt->execute([$donationId]);
    
    if ($result) {
        // Simple logging
        error_log("User $user_id cancelled donation $donationId");
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete donation');
    }
}

// End output buffering and send
ob_end_flush();
?>