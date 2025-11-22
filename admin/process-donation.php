<?php
// process-donation.php - Clean version

// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Create database connection directly (bypass header.php issues)
try {
    $host = '127.0.0.1';
    $dbname = 'orphanfare';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    header('Location: donation.php?error=database_error');
    exit();
}

// Load CustomFieldManager class
try {
    // Try different possible paths for the CustomFieldManager
    $customFieldManagerPaths = [
        __DIR__ . '/../superadmin/includes/CustomFieldManager.php',
        __DIR__ . '/includes/CustomFieldManager.php',
        'C:/xampp/htdocs/superadmin/includes/CustomFieldManager.php'
    ];
    
    $customFieldManagerLoaded = false;
    foreach ($customFieldManagerPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $customFieldManagerLoaded = true;
            error_log("CustomFieldManager loaded from: " . $path);
            break;
        }
    }
    
    if (!$customFieldManagerLoaded) {
        throw new Exception("CustomFieldManager.php not found in any of the expected paths");
    }
    
} catch (Exception $e) {
    error_log("CustomFieldManager loading error: " . $e->getMessage());
    // Continue without custom fields - don't break the entire donation process
}

// Simple logging function
function logActivity($userId, $action, $table, $recordId) {
    error_log("Activity: User $userId $action on $table record $recordId");
    return true;
}

// Simple ID generation
function generateId($prefix) {
    return $prefix . '-' . date('Ymd-His') . '-' . rand(1000, 9999);
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'record_donation':
            handleRecordDonation($pdo, $_SESSION['user_id']);
            break;
            
        case 'update_status':
            handleUpdateStatus($pdo, $_SESSION['user_id']);
            break;
            
        case 'cancel_donation':
            handleCancelDonation($pdo, $_SESSION['user_id']);
            break;
            
        default:
            header('Location: donation.php?error=invalid_action');
            exit();
    }
} else {
    header('Location: donation.php');
    exit();
}

function handleRecordDonation($pdo, $userId) {
    try {
        // Generate donation ID
        $donationId = generateId('DON');
        
        // Initialize custom fields variables
        $fieldManager = null;
        $donationCustomFields = [];
        
        // Load custom field manager if available
        if (class_exists('CustomFieldManager')) {
            $fieldManager = new CustomFieldManager($pdo);
            $donationCustomFields = $fieldManager->getModuleFields('donations');
            error_log("Custom fields loaded: " . count($donationCustomFields) . " fields found");
        } else {
            error_log("CustomFieldManager class not available - proceeding without custom fields");
        }
        
        // Handle anonymous donors
        $donorName = trim($_POST['donor_name'] ?? '');
        if (empty($donorName)) {
            $donorName = 'Anonymous Donor';
        }
        
        // Build dynamic INSERT query with custom fields
        $columns = [
            'donation_id', 'donor_name', 'donor_contact', 'donor_email', 
            'donation_type', 'description', 'date_received', 'status', 'notes'
        ];
        $placeholders = array_fill(0, count($columns), '?');
        $values = [
            $donationId,
            $donorName, // Use processed donor name
            $_POST['donor_contact'] ?? null,
            $_POST['donor_email'] ?? null,
            $_POST['donation_type'] ?? 'Goods',
            $_POST['description'] ?? '',
            $_POST['date_received'] ?? date('Y-m-d'),
            $_POST['status'] ?? 'Received',
            $_POST['notes'] ?? null
        ];
        
        // Add custom fields to the INSERT if available
        if (!empty($donationCustomFields)) {
            foreach ($donationCustomFields as $field) {
                $fieldName = $field['field_name'];
                $dbColumn = 'cf_' . $fieldName;
                $value = $_POST['custom_field_' . $fieldName] ?? '';
                
                // Handle checkbox arrays
                $processedValue = $value;
                if (is_array($value)) {
                    $processedValue = implode(',', array_filter($value));
                }
                
                if ($processedValue !== '') {
                    $columns[] = $dbColumn;
                    $placeholders[] = '?';
                    $values[] = trim($processedValue);
                    error_log("Adding custom field to INSERT: $dbColumn = '$processedValue'");
                }
            }
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO donations (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")
        ");
        
        $result = $stmt->execute($values);
        
        if ($result) {
            // Save custom fields using field manager as backup if available
            if ($fieldManager) {
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'custom_field_') === 0) {
                        $fieldName = str_replace('custom_field_', '', $key);
                        
                        // Handle checkbox arrays
                        $processedValue = $value;
                        if (is_array($value)) {
                            $processedValue = implode(',', array_filter($value));
                        }
                        
                        $fieldManager->saveFieldValue($donationId, 'donations', $fieldName, $processedValue);
                        error_log("Saved custom field via field manager: $fieldName = '$processedValue'");
                    }
                }
            }
            
            logActivity($userId, 'Donation Recorded', 'donations', $donationId);
            header('Location: donation.php?success=donation_added');
            exit();
        } else {
            header('Location: donation.php?error=donation_failed');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Donation processing error: " . $e->getMessage());
        header('Location: donation.php?error=donation_failed&message=' . urlencode($e->getMessage()));
        exit();
    }
}

function handleUpdateStatus($pdo, $userId) {
    // Set JSON header
    header('Content-Type: application/json');
    
    try {
        $donationId = $_POST['donation_id'] ?? '';
        $newStatus = $_POST['status'] ?? '';
        
        // Validate inputs
        if (empty($donationId) || empty($newStatus)) {
            echo json_encode(['success' => false, 'message' => 'Missing donation ID or status']);
            exit();
        }
        
        // Validate status
        $validStatuses = ['Received', 'Pending', 'Processed'];
        if (!in_array($newStatus, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status value']);
            exit();
        }
        
        // Check if donation exists
        $stmt = $pdo->prepare("SELECT donor_name, status as old_status FROM donations WHERE donation_id = ?");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
        
        if (!$donation) {
            echo json_encode(['success' => false, 'message' => 'Donation not found']);
            exit();
        }
        
        // Update the status
        $stmt = $pdo->prepare("UPDATE donations SET status = ?, updated_at = NOW() WHERE donation_id = ?");
        $result = $stmt->execute([$newStatus, $donationId]);
        
        if ($result) {
            // Log the status change
            logActivity($userId, 'Donation Status Updated', 'donations', $donationId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
        
    } catch (Exception $e) {
        error_log("Status update error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}

function handleCancelDonation($pdo, $userId) {
    // Set JSON header
    header('Content-Type: application/json');
    
    try {
        $donationId = $_POST['donation_id'] ?? '';
        
        if (empty($donationId)) {
            echo json_encode(['success' => false, 'message' => 'Missing donation ID']);
            exit();
        }
        
        // Get donation details for logging
        $stmt = $pdo->prepare("SELECT donor_name, description FROM donations WHERE donation_id = ?");
        $stmt->execute([$donationId]);
        $donation = $stmt->fetch();
        
        if ($donation) {
            // Delete the donation
            $stmt = $pdo->prepare("DELETE FROM donations WHERE donation_id = ?");
            $result = $stmt->execute([$donationId]);
            
            if ($result) {
                logActivity($userId, 'Donation Cancelled', 'donations', $donationId);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete donation']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Donation not found']);
        }
        
    } catch (Exception $e) {
        error_log("Cancellation error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}
?>