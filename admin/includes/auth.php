<?php
// admin/includes/auth.php

// Set session timeout to 30 minutes (1800 seconds) - MUST BE BEFORE session_start()
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);

session_start();

// Manual session timeout check (30 minutes)
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        $session_duration = 30 * 60; // 30 minutes in seconds
        if (time() - $_SESSION['login_time'] > $session_duration) {
            // Session expired
            session_unset();
            session_destroy();
            header("Location: ../login.php?error=session_expired");
            exit();
        }
    }
}

function requireLogin() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ../login.php');
        exit();
    }
    
    // Check session timeout on every page load
    checkSessionTimeout();
}

function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? 1,
        'username' => $_SESSION['username'] ?? 'Admin',
        'email' => $_SESSION['email'] ?? 'admin@orphanfare.com',
        'role' => $_SESSION['role'] ?? 'admin'
    ];
}

// Helper function to format dates
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Dummy SecureAuth class for now
class SecureAuth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function is2FARequired() {
        return false; // Disable 2FA for now
    }
}

function generateId($prefix, $table, $id_column) {
    global $pdo;
    try {
        $query = "SELECT $id_column FROM $table ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $last_id = $stmt->fetchColumn();
        
        if ($last_id) {
            $number = intval(substr($last_id, -3)) + 1;
        } else {
            $number = 1;
        }
        
        // Ensure the ID fits within 20 characters
        $id = $prefix . '-' . date('Y') . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
        
        // Truncate if necessary (though this format should be fine)
        if (strlen($id) > 20) {
            $id = substr($id, 0, 20);
        }
        
        return $id;
    } catch (Exception $e) {
        // Fallback ID generation
        $id = $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        if (strlen($id) > 20) {
            $id = substr($id, 0, 20);
        }
        return $id;
    }
}

// Update the logActivity function in auth.php
function logActivity($user_id, $action, $table_affected, $record_id) {
    global $pdo;
    try {
        $query = "INSERT INTO audit_log_admin (user_id, action, description, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $description = "$action on $table_affected (ID: $record_id)";
        $stmt->execute([
            $user_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
        return false;
    }
}

function trackScheduleActivity($user_id, $action_type, $event_id, $details = '') {
    global $pdo;
    try {
        // First, ensure the schedule_activities table exists
        $create_table_query = "
        CREATE TABLE IF NOT EXISTS schedule_activities (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action_type ENUM('event_created', 'event_updated', 'event_deleted', 'status_changed', 'email_sent') NOT NULL,
            event_id VARCHAR(20) NOT NULL,
            details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_id (event_id),
            INDEX idx_created_at (created_at),
            INDEX idx_user_id (user_id)
        )";
        $stmt = $pdo->prepare($create_table_query);
        $stmt->execute();
        
        // Now insert the activity
        $query = "INSERT INTO schedule_activities (user_id, action_type, event_id, details, ip_address, user_agent) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $user_id,
            $action_type,
            $event_id,
            $details,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
        return true;
    } catch (Exception $e) {
        error_log("Schedule activity tracking error: " . $e->getMessage());
        return false;
    }
}

// Helper function to get status display text - only declare if not exists
if (!function_exists('getStatusDisplay')) {
    function getStatusDisplay($status) {
        $statusMap = [
            'Pending' => 'Pending',
            'Active' => 'Approved', 
            'Inactive' => 'Rejected',
            'Approved' => 'Approved',
            'Rejected' => 'Rejected'
        ];
        return $statusMap[$status] ?? $status;
    }
}

// Helper function to get status badge class - only declare if not exists
if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass($status) {
        $classMap = [
            'Pending' => 'status-pending',
            'Active' => 'status-active',
            'Approved' => 'status-approved',
            'Rejected' => 'status-rejected',
            'Inactive' => 'status-rejected'
        ];
        return $classMap[$status] ?? 'status-pending';
    }
}

// Helper function to get initials from name
function getInitials($name) {
    $initials = '';
    $words = explode(' ', $name);
    
    foreach ($words as $word) {
        if (!empty(trim($word))) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    
    // Return first 2 initials, or just one if only one word
    return substr($initials, 0, 2);
}

// Helper function to get consistent avatar color based on name
function getAvatarColor($name) {
    $colors = [
        '#1a237e', '#283593', '#303f9f', '#3949ab', '#3f51b5',
        '#5c6bc0', '#7986cb', '#9fa8da', '#c5cae9', '#e8eaf6'
    ];
    
    // Use the name to generate a consistent color
    $hash = 0;
    for ($i = 0; $i < strlen($name); $i++) {
        $hash = ord($name[$i]) + (($hash << 5) - $hash);
    }
    
    $index = abs($hash) % count($colors);
    return $colors[$index];
}

?>