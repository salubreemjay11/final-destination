<?php
ob_start(); // Start output buffering at the VERY TOP
session_start();
// Check for success message from URL parameters
if (isset($_GET['success']) && $_GET['success'] == 1) {
    if (isset($_GET['message'])) {
        $_SESSION['success_message'] = urldecode($_GET['message']);
    } else {
        $_SESSION['success_message'] = "Operation completed successfully!";
    }
    // Redirect to clear URL parameters
    header("Location: schedule.php");
    exit();
}
$pageTitle = 'Schedule & Events - Orphanfare';
require_once 'includes/header.php';
require_once 'includes/email-gateway.php';

// Check if user has view permission for schedule
if (!$permissionManager->hasPermission('schedule', 'view')) {
    header('Location: access-denied.php');
    exit();
}

// Load event types from database based on user's role visibility
$availableEventTypes = [];
$eventTypeIcons = [];

try {
    // Get all active event types
    $eventTypesQuery = "SELECT * FROM event_types WHERE is_active = 1 ORDER BY type_name";
    $eventTypesStmt = $pdo->prepare($eventTypesQuery);
    $eventTypesStmt->execute();
    $allEventTypes = $eventTypesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter event types based on user's role visibility
    foreach ($allEventTypes as $eventType) {
        $visibleTo = json_decode($eventType['visible_to'] ?? '[]', true);
        
        // Super admin can see all event types
        if ($_SESSION['role'] === 'super_admin' || in_array($_SESSION['role'], $visibleTo)) {
            $availableEventTypes[$eventType['type_key']] = $eventType['type_name'];
            
            // Use the SVG icon from database, or fallback to default SVG
            if (!empty($eventType['icon'])) {
                $eventTypeIcons[$eventType['type_key']] = $eventType['icon'];
            } else {
                // Fallback to default SVG icons
                $defaultIcons = [
                    'home_visit' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/></svg>',
                    'meeting' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.5.5 0 0 1 7 12.5c0-1.665.5-2.986 1-3.74.478-.768 1.048-1.227 1.5-1.227s1.022.459 1.5 1.227c.5.754 1 2.075 1 3.74a.5.5 0 0 1-.5.5zM6 12a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 12m-1-1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/></svg>',
                    'team_building' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/></svg>',
                    'staff_training' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>',
                    'financial' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/><path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/><path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/><path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/></svg>',
                    'orientation' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-compass" viewBox="0 0 16 16"><path d="M8 16.016a7.5 7.5 0 0 0 1.962-14.74A1 1 0 0 0 9 0H7a1 1 0 0 0-.962 1.276A7.5 7.5 0 0 0 8 16.016m6.5-7.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0"/><path d="m6.94 7.44 4.95-2.83-2.83 4.95-4.949 2.83 2.828-4.95z"/></svg>',
                    'calamity_duty' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>'
                ];
                $eventTypeIcons[$eventType['type_key']] = $defaultIcons[$eventType['type_key']] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-event" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>';
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Error loading event types: " . $e->getMessage());
    // Fallback to default event types if there's an error
    $availableEventTypes = [
        'home_visit' => 'Home Visit',
        'meeting' => 'Meeting',
        'team_building' => 'Team Building',
        'staff_training' => 'Staff Training',
        'financial' => 'Financial Review',
        'orientation' => 'Orientation',
        'calamity_duty' => 'Calamity Duty'
    ];
    $eventTypeIcons = [
        'home_visit' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-door" viewBox="0 0 16 16"><path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z"/></svg>',
        'meeting' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.5.5 0 0 1 7 12.5c0-1.665.5-2.986 1-3.74.478-.768 1.048-1.227 1.5-1.227s1.022.459 1.5 1.227c.5.754 1 2.075 1 3.74a.5.5 0 0 1-.5.5zM6 12a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 12m-1-1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5"/></svg>',
        'team_building' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-arms-up" viewBox="0 0 16 16"><path d="M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/><path d="m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z"/></svg>',
        'staff_training' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/></svg>',
        'financial' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/><path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/><path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/><path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/></svg>',
        'orientation' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-compass" viewBox="0 0 16 16"><path d="M8 16.016a7.5 7.5 0 0 0 1.962-14.74A1 1 0 0 0 9 0H7a1 1 0 0 0-.962 1.276A7.5 7.5 0 0 0 8 16.016m6.5-7.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0"/><path d="m6.94 7.44 4.95-2.83-2.83 4.95-4.949 2.83 2.828-4.95z"/></svg>',
        'calamity_duty' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/></svg>'
    ];
}

// Load Custom Field Manager for Schedule
$fieldManager = null;
$scheduleCustomFields = [];
$existingScheduleCustomValues = [];

try {
    if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
        require_once '../superadmin/includes/CustomFieldManager.php';
    } elseif (file_exists('includes/CustomFieldManager.php')) {
        require_once 'includes/CustomFieldManager.php';
    } else {
        throw new Exception('CustomFieldManager.php not found');
    }
    
    $fieldManager = new CustomFieldManager($pdo);

    // Load custom fields for schedule module
    $scheduleCustomFields = $fieldManager->getModuleFields('schedule');
    
    error_log("Schedule custom fields loaded: " . count($scheduleCustomFields));
    
} catch (Exception $e) {
    error_log("Custom Field Manager Error for Schedule: " . $e->getMessage());
    $customFieldsError = "Custom fields are temporarily unavailable for schedule.";
}

// Load custom field values for existing events when viewing
if (isset($_GET['event_id']) && isset($_GET['mode']) && $_GET['mode'] === 'view') {
    $eventId = $_GET['event_id'];
    
    try {
        // Load existing custom field values
        if ($fieldManager) {
            $existingScheduleCustomValues = $fieldManager->getFieldValues($eventId, 'schedule');
            error_log("Existing Schedule Custom Values: " . print_r($existingScheduleCustomValues, true));
        }
    } catch (Exception $e) {
        error_log("Error loading schedule custom field values: " . $e->getMessage());
    }
}

// Check permissions for display
$canCreate = $permissionManager->hasPermission('schedule', 'create');
$canEdit = $permissionManager->hasPermission('schedule', 'edit');
$canDelete = $permissionManager->hasPermission('schedule', 'delete');

// Add these initializations near the top of schedule.php, after your existing variable declarations
$recentActivities = [];
$stats = [
    'home_visit' => 0,
    'meeting' => 0,
    'team_building' => 0,
    'staff_training' => 0,
    'financial' => 0,
    'orientation' => 0,
    'calamity_duty' => 0
];
$upcoming_events = [];
$completed_events = [];
$all_events = [];
$calendar_events = [];
$scheduled_days = [];
$completed_days = [];
$unavailable_days = [];

// Create events_gallery table if not exists
try {
    $create_gallery_table_query = "
    CREATE TABLE IF NOT EXISTS events_gallery (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id VARCHAR(20) NOT NULL,
        image_path VARCHAR(500) NOT NULL,
        caption TEXT,
        description TEXT,
        uploaded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
    )";
    $stmt = $pdo->prepare($create_gallery_table_query);
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Events gallery table creation error: " . $e->getMessage());
}

// Create upload directories if they don't exist
$uploadDirs = [
    'uploads/schedule/',
    'uploads/schedule/gallery/',
    'uploads/schedule/events/'
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        error_log("Created directory: " . $dir);
    }
}

// Create calendar_availability table if not exists
try {
    $create_availability_table_query = "
    CREATE TABLE IF NOT EXISTS calendar_availability (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unavailable_date DATE NOT NULL,
        start_time TIME,
        end_time TIME,
        reason VARCHAR(255),
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_date (unavailable_date)
    )";
    $stmt = $pdo->prepare($create_availability_table_query);
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Calendar availability table creation error: " . $e->getMessage());
}

// Ensure events table exists
try {
    $create_table_query = "
    CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id VARCHAR(20) UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_type ENUM('home_visit', 'meeting', 'team_building', 'staff_training', 'financial', 'orientation', 'calamity_duty') NOT NULL,
        event_date DATE NOT NULL,
        event_time TIME NOT NULL,
        location VARCHAR(255),
        assigned_to VARCHAR(255),
        notes TEXT,
        status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
        is_active BOOLEAN DEFAULT 1,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $stmt = $pdo->prepare($create_table_query);
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Events table creation error: " . $e->getMessage());
}

// Automatically update events that have passed their scheduled time
try {
    $auto_update_query = "UPDATE events 
                         SET status = 'Completed' 
                         WHERE status = 'Scheduled' 
                         AND (event_date < CURDATE() OR (event_date = CURDATE() AND event_time < CURTIME()))
                         AND is_active = 1";
    $stmt = $pdo->prepare($auto_update_query);
    $stmt->execute();
    $updated_count = $stmt->rowCount();
    
    if ($updated_count > 0) {
        error_log("Automatically completed $updated_count past events");
    }
} catch (PDOException $e) {
    error_log("Error auto-updating events: " . $e->getMessage());
}

try {
    // Get event statistics
    $stats = [];
    $event_types = ['home_visit', 'meeting', 'team_building', 'staff_training', 'financial', 'orientation', 'calamity_duty'];
    foreach ($event_types as $type) {
        $query = "SELECT COUNT(*) as count FROM events WHERE event_type = ? AND status = 'Scheduled' AND is_active = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats[$type] = $result['count'];
    }

    // Get all events (including past completed ones)
    $query = "SELECT e.*, u.username as created_by_name 
            FROM events e 
            LEFT JOIN users u ON e.created_by = u.id 
            WHERE e.is_active = 1
            ORDER BY 
                e.event_date DESC, 
                e.event_time DESC 
            LIMIT 50";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $all_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Separate events into upcoming and completed for display
    $upcoming_events = [];
    $completed_events = [];

    foreach ($all_events as $event) {
        if ($event['status'] === 'Completed') {
            $completed_events[] = $event;
        } else {
            $upcoming_events[] = $event;
        }
    }

    // Get all events for calendar highlighting (for entire current year)
    $current_year = date('Y');
    $year_start = ($current_year - 1) . '-01-01'; // Include previous year
    $year_end = ($current_year + 3) . '-12-31';   // Extend to 3 years in future

    $calendar_events_query = "SELECT event_date, status FROM events 
                            WHERE event_date BETWEEN ? AND ?
                            AND is_active = 1";
    $stmt = $pdo->prepare($calendar_events_query);
    $stmt->execute([$year_start, $year_end]);
    $calendar_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unavailable dates
    $unavailable_query = "SELECT unavailable_date, start_time, end_time, reason FROM calendar_availability 
                         WHERE unavailable_date BETWEEN ? AND ?";
    $stmt = $pdo->prepare($unavailable_query);
    $stmt->execute([$year_start, $year_end]);
    $unavailable_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract event days for JavaScript (organized by year and month)
    $scheduled_days = [];
    $completed_days = [];
    $unavailable_days = [];
    
    foreach ($calendar_events as $event) {
        $event_date = new DateTime($event['event_date']);
        $year = $event_date->format('Y');
        $month = $event_date->format('n');
        $day = (int)$event_date->format('j');
        
        if ($event['status'] === 'Completed') {
            if (!isset($completed_days[$year])) {
                $completed_days[$year] = [];
            }
            if (!isset($completed_days[$year][$month])) {
                $completed_days[$year][$month] = [];
            }
            $completed_days[$year][$month][] = $day;
        } else {
            if (!isset($scheduled_days[$year])) {
                $scheduled_days[$year] = [];
            }
            if (!isset($scheduled_days[$year][$month])) {
                $scheduled_days[$year][$month] = [];
            }
            $scheduled_days[$year][$month][] = $day;
        }
    }
    
    // Extract unavailable days
    foreach ($unavailable_dates as $unavailable) {
        $unavailable_date = new DateTime($unavailable['unavailable_date']);
        $year = $unavailable_date->format('Y');
        $month = $unavailable_date->format('n');
        $day = (int)$unavailable_date->format('j');
        
        if (!isset($unavailable_days[$year])) {
            $unavailable_days[$year] = [];
        }
        if (!isset($unavailable_days[$year][$month])) {
            $unavailable_days[$year][$month] = [];
        }
        $unavailable_days[$year][$month][] = [
            'day' => $day,
            'start_time' => $unavailable['start_time'],
            'end_time' => $unavailable['end_time'],
            'reason' => $unavailable['reason']
        ];
    }

    // Remove duplicates and reindex arrays
    foreach ($scheduled_days as $year => $months) {
        foreach ($months as $month => $days) {
            $scheduled_days[$year][$month] = array_values(array_unique($days));
        }
    }
    foreach ($completed_days as $year => $months) {
        foreach ($months as $month => $days) {
            $completed_days[$year][$month] = array_values(array_unique($days));
        }
    }

} catch (PDOException $e) {
    error_log("Database error in schedule.php: " . $e->getMessage());
    $error_message = "Unable to load schedule data. Please try again.";
}

// Handle form submissions - SINGLE UNIFIED HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle new event creation
    if (isset($_POST['add_event']) && $canCreate) {
        try {
            $event_id = generateId('EVT', 'events', 'event_id');
            error_log("Generated Event ID: " . $event_id);
            
            // DATE AVAILABILITY CHECK
            $event_date = $_POST['event_date'];
            
            // Check if date is marked as unavailable
            $availability_check = $pdo->prepare("SELECT * FROM calendar_availability WHERE unavailable_date = ?");
            $availability_check->execute([$event_date]);
            $unavailable_date = $availability_check->fetch();
            
            if ($unavailable_date) {
                $reason = $unavailable_date['reason'] ?: 'No reason specified';
                $error_message = "❌ Cannot schedule event on " . $event_date . " - This date is marked as unavailable. Reason: " . $reason;
                throw new Exception($error_message);
            }
            
            // Build dynamic SQL for events table including custom fields
            $eventColumns = ['event_id', 'title', 'description', 'event_type', 'event_date', 'event_time', 'location', 'assigned_to', 'notes', 'created_by'];
            $eventPlaceholders = array_fill(0, count($eventColumns), '?');
            $eventValues = [
                $event_id,
                $_POST['title'],
                $_POST['description'],
                $_POST['event_type'],
                $_POST['event_date'],
                $_POST['event_time'],
                $_POST['location'],
                $_POST['assigned_to'],
                $_POST['notes'],
                $currentUser['id']
            ];

            // ADD CUSTOM FIELDS TO EVENT INSERT
            $customFieldsAdded = 0;
            foreach ($scheduleCustomFields as $field) {
                $fieldName = $field['field_name'];
                $dbColumn = 'cf_' . $fieldName;
                $value = $_POST['custom_field_' . $fieldName] ?? '';

                // Handle checkbox arrays and empty values
                $processedValue = $value;
                if (is_array($value)) {
                    $processedValue = implode(',', array_filter($value));
                }

                // Always add the column, even if empty
                $eventColumns[] = $dbColumn;
                $eventPlaceholders[] = '?';
                $eventValues[] = trim($processedValue);
                $customFieldsAdded++;
            }

            $query = "INSERT INTO events (" . implode(', ', $eventColumns) . ") 
                     VALUES (" . implode(', ', $eventPlaceholders) . ")";
            
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute($eventValues);
            
            // SAVE CUSTOM FIELDS USING FIELD MANAGER (as backup)
            if ($fieldManager) {
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'custom_field_') === 0) {
                        $fieldName = str_replace('custom_field_', '', $key);
                        
                        // Handle checkbox arrays
                        $processedValue = $value;
                        if (is_array($value)) {
                            $processedValue = implode(',', array_filter($value));
                        }
                        
                        // Check if it's a schedule field
                        $isScheduleField = false;
                        foreach ($scheduleCustomFields as $field) {
                            if ($field['field_name'] === $fieldName) {
                                $isScheduleField = true;
                                break;
                            }
                        }
                        
                        if ($isScheduleField) {
                            $fieldManager->saveFieldValue($event_id, 'schedule', $fieldName, $processedValue);
                        }
                    }
                }
            }
            
            // Log the activity
            logActivity($currentUser['id'], 'Event Created', 'events', $event_id);
            
            // Track schedule activity
            trackScheduleActivity($currentUser['id'], 'event_created', $event_id, 
                "Event: " . $_POST['title'] . " | Type: " . $_POST['event_type']);
            
            // FIXED: Use JavaScript redirect instead of header redirect
            echo "<script>window.location.href = 'schedule.php?success=1';</script>";
            exit();
            
        } catch (Exception $e) {
            error_log("Error adding event with custom fields: " . $e->getMessage());
            $error_message = "Failed to add event: " . $e->getMessage();
        }
    } 
    // Handle image upload for event gallery
    elseif (isset($_POST['upload_event_image']) && $canEdit) {
        try {
            $event_id = $_POST['event_id'];
            $caption = $_POST['caption'] ?? '';
            $description = $_POST['image_description'] ?? '';
            
            if (isset($_FILES['event_image']) && is_array($_FILES['event_image']['name'])) {
                // Multiple files uploaded
                $uploadDir = 'uploads/schedule/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadedCount = 0;
                $totalFiles = count($_FILES['event_image']['name']);
                
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['event_image']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['event_image']['name'][$i]);
                        $fileExtension = pathinfo($_FILES['event_image']['name'][$i], PATHINFO_EXTENSION);
                        $filename = 'event_' . $event_id . '_' . time() . '_' . $i . '.' . $fileExtension;
                        $filePath = $uploadDir . $filename;
                        
                        // Check if file is an image
                        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $fileType = $_FILES['event_image']['type'][$i];
                        
                        if (in_array($fileType, $allowedTypes)) {
                            if (move_uploaded_file($_FILES['event_image']['tmp_name'][$i], $filePath)) {
                                // Insert into database
                                $query = "INSERT INTO events_gallery (event_id, image_path, caption, description, uploaded_by) 
                                        VALUES (?, ?, ?, ?, ?)";
                                $stmt = $pdo->prepare($query);
                                $stmt->execute([$event_id, $filePath, $caption, $description, $currentUser['id']]);
                                
                                $uploadedCount++;
                            }
                        }
                    }
                }
                
                if ($uploadedCount > 0) {
                    // Log gallery activity
                    logActivity($currentUser['id'], 'Event Photos Uploaded', 'events_gallery', $event_id);
                    trackScheduleActivity($currentUser['id'], 'photos_uploaded', $event_id, 
                        "Photos: " . $uploadedCount . " | Event: " . $event_id);
                    
                    // FIXED: Use JavaScript redirect
                    echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode("Successfully uploaded $uploadedCount photo(s)!") . "';</script>";
                    exit();
                } else {
                    throw new Exception('No valid images were uploaded.');
                }
                
            } elseif (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
                // Single file uploaded
                $uploadDir = 'uploads/schedule/gallery/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generate unique filename
                $fileExtension = pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION);
                $filename = 'event_' . $event_id . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $filename;
                
                // Check if file is an image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = $_FILES['event_image']['type'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Only JPG, PNG, GIF, and WebP images are allowed.');
                }
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['event_image']['tmp_name'], $filePath)) {
                    // Insert into database
                    $query = "INSERT INTO events_gallery (event_id, image_path, caption, description, uploaded_by) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$event_id, $filePath, $caption, $description, $currentUser['id']]);
                    
                    // Log gallery activity
                    logActivity($currentUser['id'], 'Event Photo Uploaded', 'events_gallery', $event_id);
                    trackScheduleActivity($currentUser['id'], 'photo_uploaded', $event_id, 
                        "Photo: " . $caption . " | Event: " . $event_id);
                    
                    // FIXED: Use JavaScript redirect
                    echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode('Image uploaded successfully!') . "';</script>";
                    exit();
                } else {
                    throw new Exception('Failed to move uploaded file.');
                }
            } else {
                throw new Exception('Please select at least one valid image file.');
            }
            
        } catch (Exception $e) {
            $error_message = "Failed to upload image: " . $e->getMessage();
        }
    }
    // Handle calendar availability
    elseif (isset($_POST['mark_unavailable']) && $canEdit) {
        try {
            $unavailable_date = $_POST['unavailable_date'];
            $start_time = $_POST['start_time'] ?? null;
            $end_time = $_POST['end_time'] ?? null;
            $reason = $_POST['unavailable_reason'] ?? '';
            
            $query = "INSERT INTO calendar_availability (unavailable_date, start_time, end_time, reason, created_by) 
                     VALUES (?, ?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE 
                     start_time = VALUES(start_time), 
                     end_time = VALUES(end_time), 
                     reason = VALUES(reason)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$unavailable_date, $start_time, $end_time, $reason, $currentUser['id']]);
            
            // FIXED: Use JavaScript redirect
            echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode('Date marked as unavailable!') . "';</script>";
            exit();
            
        } catch (Exception $e) {
            $error_message = "Failed to mark date as unavailable: " . $e->getMessage();
        }
    }
    // Handle remove availability
    elseif (isset($_POST['remove_unavailable']) && $canEdit) {
        try {
            $unavailable_date = $_POST['unavailable_date'];
            
            $query = "DELETE FROM calendar_availability WHERE unavailable_date = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$unavailable_date]);
            
            // FIXED: Use JavaScript redirect
            echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode('Date availability restored!') . "';</script>";
            exit();
            
        } catch (Exception $e) {
            $error_message = "Failed to remove unavailable date: " . $e->getMessage();
        }
    }
}

// Similarly update other actions:
if (isset($_POST['delete_event']) && $canDelete) {
    try {
        $query = "UPDATE events SET is_active = 0 WHERE event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['event_id']]);
        
        logActivity($currentUser['id'], 'Event Deleted', 'events', $_POST['event_id']);
        trackScheduleActivity($currentUser['id'], 'event_deleted', $_POST['event_id']);
        
        // FIXED: Use JavaScript redirect
        echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode('Event deleted successfully!') . "';</script>";
        exit();
        
    } catch (Exception $e) {
        error_log("Error deleting event: " . $e->getMessage());
        $error_message = "Failed to delete event: " . $e->getMessage();
    }
} elseif (isset($_POST['delete_event']) && !$canDelete) {
    $error_message = "Permission denied - You cannot delete events";
}

if (isset($_POST['update_event_status']) && $canEdit) {
    try {
        $query = "UPDATE events SET status = ? WHERE event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$_POST['status'], $_POST['event_id']]);
        
        logActivity($currentUser['id'], 'Event Status Updated', 'events', $_POST['event_id']);
        trackScheduleActivity($currentUser['id'], 'status_changed', $_POST['event_id'], 
            "New status: " . $_POST['status']);
        
        // FIXED: Use JavaScript redirect
        echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode('Event status updated successfully!') . "';</script>";
        exit();
        
    } catch (Exception $e) {
        error_log("Error updating event status: " . $e->getMessage());
        $error_message = "Failed to update event status: " . $e->getMessage();
    }
} elseif (isset($_POST['update_event_status']) && !$canEdit) {
    $error_message = "Permission denied - You cannot update event status";
}

// Handle email reminder sending
if (isset($_POST['send_email_reminder']) && $canEdit) {
    try {
        $event_id = $_POST['event_id'];
        $email_recipients = $_POST['email_recipients'];
        
        // Parse email addresses
        $emails = preg_split('/[\s,;]+/', $email_recipients);
        $valid_emails = [];
        $invalid_emails = [];
        
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $valid_emails[] = ['email' => $email, 'name' => ''];
                } else {
                    $invalid_emails[] = $email;
                }
            }
        }
        
        if (empty($valid_emails)) {
            throw new Exception("No valid email addresses provided");
        }
        
        // Get event details
        $query = "SELECT * FROM events WHERE event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            throw new Exception("Event not found");
        }
        
        // Send email reminders
        $email = new EmailNotification();
        $result = $email->sendMeetingNotification($valid_emails, $event);
        
        // Log the activity
        logActivity($currentUser['id'], 'Email Reminder Sent', 'events', $event_id);
        
        // Safe count for tracking
        $recipient_count = is_array($valid_emails) ? count($valid_emails) : 0;
        trackScheduleActivity($currentUser['id'], 'email_sent', $event_id, 
            "Recipients: " . $recipient_count);
        
        $message = "Email reminders sent successfully! " . 
                  $result['success'] . " delivered, " . 
                  $result['failed'] . " failed.";
        
        if (!empty($invalid_emails)) {
            $message .= " Invalid emails skipped: " . implode(', ', $invalid_emails);
        }
        
        // FIXED: Use JavaScript redirect
        echo "<script>window.location.href = 'schedule.php?success=1&message=" . urlencode($message) . "';</script>";
        exit();
        
    } catch (Exception $e) {
        error_log("Error sending email reminders: " . $e->getMessage());
        $error_message = "Failed to send email reminders: " . $e->getMessage();
    }
} elseif (isset($_POST['send_email_reminder']) && !$canEdit) {
    $error_message = "Permission denied - You cannot send email reminders";
}

// Check for success message from redirect
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
ob_end_flush();
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Schedule & Events</h1>
        <div class="header-actions">
            <?php if ($canCreate): ?>
                <button class="btn btn-primary" onclick="openModal()"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                </svg></div> Add New Event</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled title="No permission to add events"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
                </svg></div> Add New Event</button>
            <?php endif; ?>
            <button class="btn btn-secondary" onclick="refreshPage()"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
            <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
            </svg>
            </div> Refresh</button>
            <?php if ($canEdit): ?>
                <button class="btn btn-warning" onclick="openActivitiesModal()"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-card-heading" viewBox="0 0 16 16">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5z"/>
                </svg></div> View Activities</button>
                <button class="btn btn-danger" onclick="openAvailabilityModal()"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-x" viewBox="0 0 16 16">
                <path d="M6.146 8.146a.5.5 0 0 1 .708 0L8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 0 1 0-.708"/>
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                </svg></div> Manage Availability</button>
            <?php else: ?>
                <button class="btn btn-secondary" disabled title="No permission to view activities"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
                <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                </svg></div> View Activities</button>
                <button class="btn btn-secondary" disabled title="No permission to manage availability"><div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar2-x" viewBox="0 0 16 16">
                <path d="M6.146 8.146a.5.5 0 0 1 .708 0L8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 0 1 0-.708"/>
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                </svg></div> Manage Availability</button>
            <?php endif; ?>
        </div>
    </div>

<!-- Show read-only banner if no edit permission -->
    <?php if (!$canEdit && !$canCreate && !$canDelete): ?>
    <div class="read-only-banner" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        <strong><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
            <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"/>
        </svg> Read-Only Mode:</strong> You have view-only access to schedule management. You cannot make any changes.
    </div>
    <?php endif; ?>

    <!-- Success/Error Notifications -->
    <?php if (isset($success_message)): ?>
        <div class="notification success show">
            <div class="notification-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg></div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message"><?php echo htmlspecialchars($success_message); ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="notification error show">
            <div class="notification-icon"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
            </svg></div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message"><?php echo htmlspecialchars($error_message); ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">&times;</button>
        </div>
    <?php endif; ?>

    <div class="confidentiality-alert">
        <strong>Confidentiality Reminder:</strong> All event and schedule information is protected. Access is logged and monitored.
        <br><small><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
        </svg> Events automatically complete when their scheduled time passes</small>
    </div>

    <!-- Stats Grid -->
<div class="stats-grid">
    <?php 
    // Get statistics for each available event type
    $stats = [];
    foreach ($availableEventTypes as $typeKey => $typeName) {
        $query = "SELECT COUNT(*) as count FROM events WHERE event_type = ? AND status = 'Scheduled' AND is_active = 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$typeKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats[$typeKey] = $result['count'];
    }
    
    foreach ($availableEventTypes as $typeKey => $typeName): 
    ?>
        <div class="stat-card">
            <div class="stat-label"><?php echo htmlspecialchars($typeName); ?></div>
            <div class="stat-value"><?php echo $stats[$typeKey] ?? 0; ?></div>
        </div>
    <?php endforeach; ?>
</div>

    <div class="content-grid">
        <!-- Calendar Section -->
        <div class="calendar-card">
            <h3 class="section-title" id="currentMonth"><?php echo date('F Y'); ?></h3>
            <div class="calendar-header">
                <div class="calendar-nav">
                   <select id="yearSelect" onchange="changeCalendarYear()">
                        <?php
                        $current_year = date('Y');
                        $start_year = $current_year - 1;
                        $end_year = $current_year + 5; // Show 5 years into future
                        
                        for ($year = $start_year; $year <= $end_year; $year++) {
                            $selected = $year == $current_year ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                        ?>
                    </select>
                    <select id="monthSelect" onchange="changeCalendar()">
                        <?php
                        $current_month = date('n');
                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                  'July', 'August', 'September', 'October', 'November', 'December'];
                        foreach ($months as $index => $month) {
                            $month_num = $index + 1;
                            $selected = $month_num == $current_month ? 'selected' : '';
                            echo "<option value='$month_num' $selected>$month</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Sun</th>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                    </tr>
                </thead>
                <tbody id="calendarBody">
                </tbody>
            </table>
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="legend-dot scheduled-dot"></span>
                    <span>Upcoming Events</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot completed-dot"></span>
                    <span>Completed Events</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot unavailable-dot"></span>
                    <span>Unavailable</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot today-dot"></span>
                    <span>Today</span>
                </div>
            </div>
        </div>

        <!-- Events Section with Tabs -->
        <div class="events-card">
            <div class="events-tabs">
                <button class="tab-btn active" onclick="switchEventsTab('upcoming', this)">Upcoming Events</button>
                <button class="tab-btn" onclick="switchEventsTab('completed', this)">Completed Events</button>
                <button class="tab-btn" onclick="switchEventsTab('gallery', this)">Event Gallery</button>
            </div>
            
            <div id="upcomingEvents" class="events-tab-content active">
                <div class="events-header">
                    <h3 class="section-title">Upcoming Events</h3>
                    <div class="events-count">
                        <span class='scheduled-count'><?php echo count($upcoming_events); ?> scheduled</span>
                    </div>
                </div>
                
                <?php if (empty($upcoming_events)): ?>
                    <div class="no-events">
                        <div class="no-events-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-calendar" viewBox="0 0 16 16">
                            <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                        </svg></div>
                        <p>No upcoming events scheduled</p>
                        <?php if ($canCreate): ?>
                            <button class="btn btn-primary" onclick="openModal()">Schedule Your First Event</button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Schedule Your First Event</button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="events-list">
                        <?php foreach ($upcoming_events as $event): 
                            $is_completed = $event['status'] === 'Completed';
                            $event_date = new DateTime($event['event_date']);
                            $event_time = new DateTime($event['event_time']);
                            $now = new DateTime();
                            
                            // Check if event should be automatically completed
                            $should_be_completed = ($event_date->format('Y-m-d') < $now->format('Y-m-d')) || 
                                                  ($event_date->format('Y-m-d') == $now->format('Y-m-d') && $event_time < $now);
                        ?>
                            <div class="event-item <?php echo $is_completed ? 'completed' : ''; ?>" 
                                 data-event-date="<?php echo $event['event_date']; ?>" 
                                 data-event-time="<?php echo $event['event_time']; ?>"
                                 data-event-status="<?php echo $event['status']; ?>">
                                <div class="event-icon">
                                    <?php 
                                    echo $eventTypeIcons[$event['event_type']] ?? '';
                                    ?>
                                </div>
                                <div class="event-content">
                                    <div class="event-header">
                                        <div class="event-main">
                                            <div class="event-date">
                                                <?php 
                                                $today = new DateTime();
                                                $tomorrow = new DateTime('tomorrow');
                                                
                                                if ($event_date->format('Y-m-d') == $today->format('Y-m-d')) {
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bullseye" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                                        <path d="M8 13A5 5 0 1 1 8 3a5 5 0 0 1 0 10m0 1A6 6 0 1 0 8 2a6 6 0 0 0 0 12"/>
                                                        <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8"/>
                                                        <path d="M9.5 8a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                                                    </svg> Today - ';
                                                } elseif ($event_date->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
                                                    echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                                                        <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                                        <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
                                                    </svg> Tomorrow - ';
                                                }
                                                echo $event_date->format('M j, Y');
                                                if ($is_completed) {
                                                    echo ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16" style="display: inline;">
                                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                                                        <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                                                    </svg>';
                                                } elseif ($should_be_completed && !$is_completed) {
                                                    echo ' <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hourglass-split" viewBox="0 0 16 16" style="display: inline;">
                                                        <path d="M2.5 15a.5.5 0 1 1 0-1h1v-1a4.5 4.5 0 0 1 2.557-4.06c.29-.139.443-.377.443-.59v-.7c0-.213-.154-.451-.443-.59A4.5 4.5 0 0 1 3.5 3V2h-1a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1h-1v1a4.5 4.5 0 0 1-2.557 4.06c-.29.139-.443.377-.443.59v.7c0 .213.154.451.443.59A4.5 4.5 0 0 1 12.5 13v1h1a.5.5 0 0 1 0 1zm2-13v1c0 .537.12 1.045.337 1.5h6.326c.216-.455.337-.963.337-1.5V2zm3 6.35c0 .701-.478 1.236-1.011 1.492A3.5 3.5 0 0 0 4.5 13s.866-1.299 3-1.48zm1 0v3.17c2.134.181 3 1.48 3 1.48a3.5 3.5 0 0 0-1.989-3.158C8.978 9.586 8.5 9.052 8.5 8.351z"/>
                                                    </svg> (Auto-completing...)';
                                                }
                                                ?>
                                            </div>
                                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <div class="event-details">
                                                <span class="event-time"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
                                                <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                                <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
                                                </svg> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                                <?php if (!empty($event['location'])): ?>
                                                    <span class="event-location"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                                                </svg> <?php echo htmlspecialchars($event['location']); ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($event['assigned_to'])): ?>
                                                    <span class="event-assigned"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                                        </svg> <?php echo htmlspecialchars($event['assigned_to']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($event['description'])): ?>
                                                <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="event-actions">
                                            <?php if ($canEdit): ?>
                                                <select class="status-select" onchange="updateEventStatus('<?php echo $event['event_id']; ?>', this.value)" title="Change Status">
                                                    <option value="Scheduled" <?php echo $event['status'] === 'Scheduled' ? 'selected' : ''; ?>> Scheduled</option>
                                                    <option value="Completed" <?php echo $event['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="Cancelled" <?php echo $event['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                                <button class="email-reminder-btn" onclick="openEmailModal('<?php echo $event['event_id']; ?>')" title="Send Email Reminder">Send Email</button>
                                                <button class="gallery-btn" onclick="openGalleryModal('<?php echo $event['event_id']; ?>', '<?php echo htmlspecialchars($event['title']); ?>')" title="Add Photos">Add Photos</button>
                                            <?php else: ?>
                                                <select class="status-select" disabled style="opacity: 0.6; cursor: not-allowed;">
                                                    <option selected><?php echo htmlspecialchars($event['status']); ?></option>
                                                </select>
                                                <button class="email-reminder-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to send emails">Send Email</button>
                                                <button class="gallery-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to add photos">Add Photos</button>
                                            <?php endif; ?>
                                            
                                            <?php if ($canDelete): ?>
                                                <form method="POST" class="delete-form">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                                    <button type="submit" name="delete_event" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event?')" title="Delete Event">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                    </svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="delete-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to delete"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                </svg></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($event['notes'])): ?>
                                        <div class="event-notes">
                                            <strong>Notes:</strong> <?php echo htmlspecialchars($event['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($should_be_completed && !$is_completed): ?>
                                        <div class="auto-complete-notice">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                                                <path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641zM6.374 1 4.168 8.5H7.5a.5.5 0 0 1 .478.647L6.78 13.04 11.478 7H8a.5.5 0 0 1-.474-.658L9.306 1z"/>
                                            </svg> This event will be automatically marked as completed on next page refresh
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="completedEvents" class="events-tab-content">
                <div class="events-header">
                    <h3 class="section-title">Completed Events</h3>
                    <div class="events-count">
                        <span class='completed-count'><?php echo count($completed_events); ?> completed</span>
                    </div>
                </div>
                
                <?php if (empty($completed_events)): ?>
                    <div class="no-events">
                        <div class="no-events-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
                        </svg></div>
                        <p>No completed events yet</p>
                        <small>Completed events will appear here automatically</small>
                    </div>
                <?php else: ?>
                    <div class="events-list">
                        <?php foreach ($completed_events as $event): 
                            $event_date = new DateTime($event['event_date']);
                        ?>
                            <div class="event-item completed" 
                                 data-event-date="<?php echo $event['event_date']; ?>">
                                <div class="event-icon">
                                    <?php 
                                    echo $eventTypeIcons[$event['event_type']] ?? '';
                                    ?>
                                </div>
                                <div class="event-content">
                                    <div class="event-header">
                                        <div class="event-main">
                                            <div class="event-date">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                                            </svg> Completed on <?php echo $event_date->format('M j, Y'); ?>
                                            </div>
                                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <div class="event-details">
                                                <span class="event-time"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
                                                <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
                                                <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
                                                </svg> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                                <?php if (!empty($event['location'])): ?>
                                                    <span class="event-location"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
                                                    </svg> <?php echo htmlspecialchars($event['location']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($event['description'])): ?>
                                                <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="event-actions">
                                            <?php if ($canEdit): ?>
                                                <button class="gallery-btn" onclick="openGalleryModal('<?php echo $event['event_id']; ?>', '<?php echo htmlspecialchars($event['title']); ?>')" title="View Photos">View Photos</button>
                                            <?php else: ?>
                                                <button class="gallery-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to view photos"> View Photos</button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <form method="POST" class="delete-form">
                                                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                                    <button type="submit" name="delete_event" class="delete-btn" onclick="return confirm('Are you sure you want to delete this completed event?')" title="Delete Event">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                    <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                    <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                    </svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button class="delete-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to delete"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                                </svg></button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($event['notes'])): ?>
                                        <div class="event-notes">
                                            <strong>Notes:</strong> <?php echo htmlspecialchars($event['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Event Gallery Tab -->
            <div id="galleryEvents" class="events-tab-content">
                <div class="events-header">
                    <h3 class="section-title">Event Gallery</h3>
                    <div class="events-count">
                        <span class='gallery-count'>All Event Photos</span>
                    </div>
                </div>
                
                <div class="gallery-container" id="eventGallery">
                    <div class="gallery-controls">
                        <form method="GET" action="schedule.php" id="galleryForm" style="display: inline;">
                            <input type="hidden" name="tab" value="gallery">
                            <select class="view-gallery" name="view_gallery" onchange="this.form.submit()">
                                <option value="">Select Event to View Photos</option>
                                <?php 
                                $selected_event_id = $_GET['view_gallery'] ?? '';
                                foreach ($all_events as $event): 
                                    $selected = ($selected_event_id == $event['event_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $event['event_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($event['title']); ?> (<?php echo date('M j, Y', strtotime($event['event_date'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php if ($canEdit && !empty($selected_event_id)): 
                            // Find the selected event title
                            $selected_event_title = '';
                            foreach ($all_events as $event) {
                                if ($event['event_id'] == $selected_event_id) {
                                    $selected_event_title = $event['title'];
                                    break;
                                }
                            }
                        ?>
                            <button class="btn btn-primary" onclick="openGalleryModal('<?php echo $selected_event_id; ?>', '<?php echo htmlspecialchars($selected_event_title); ?>')">Manage Photos</button>
                        <?php endif; ?>
                    </div>
                    
                    <div class="all-photos-grid" id="allPhotosGrid">
                        <?php if (!empty($selected_event_id)): ?>
                            <?php
                            // Load photos for selected event
                            $gallery_photos = [];
                            try {
                                $gallery_query = "SELECT eg.*, u.username as uploaded_by_name 
                                                FROM events_gallery eg 
                                                LEFT JOIN users u ON eg.uploaded_by = u.id 
                                                WHERE eg.event_id = ? 
                                                ORDER BY eg.created_at DESC";
                                $stmt = $pdo->prepare($gallery_query);
                                $stmt->execute([$selected_event_id]);
                                $gallery_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Exception $e) {
                                error_log("Error loading gallery photos: " . $e->getMessage());
                            }
                            ?>
                            
                            <?php if (!empty($gallery_photos)): ?>
                                <?php foreach ($gallery_photos as $photo): ?>
                                    <div class="photo-item">
                                        <img src="<?php echo htmlspecialchars($photo['image_path']); ?>" 
                                            alt="<?php echo htmlspecialchars($photo['caption'] ?? 'Event photo'); ?>" 
                                            class="photo-image" 
                                            onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI1MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiMzYTNhM2EiLz48c3ZnIHg9Ijc1IiB5PSI1NSIgd2lkdGg9IjUwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2I4YzVmZiIgc3Ryb2tlLXdpZHRoPSIyIj48cGF0aCBkPSJNMjAgMjFVMTlBMiAyIDAgMCAxIDIyIDE3SDI4QTIgMiAwIDAgMSAzMCAxOVYyMU0xNiA1QTcgNyAwIDEgMSAyIDVBMTYgMTYgMCAwIDEgMTYgNVoiLz48L3N2Zz48L3N2Zz4='">
                                        <div class="photo-info">
                                            <div class="photo-caption"><?php echo htmlspecialchars($photo['caption'] ?? 'No caption'); ?></div>
                                            <div class="photo-description"><?php echo htmlspecialchars($photo['description'] ?? 'No description'); ?></div>
                                            <div class="photo-meta">
                                                <span class="photo-date"><?php echo date('M j, Y', strtotime($photo['created_at'])); ?></span>
                                                <span class="photo-uploader">By: <?php echo htmlspecialchars($photo['uploaded_by_name'] ?? 'Unknown'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-photos">
                                    <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
                                        <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-calendar3-event" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                <path d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg></div>
                                        <p>No photos found for this event</p>
                                        <small>Upload photos using the "Manage Photos" button above</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-events">
                                <div class="no-events-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-calendar3-event" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                <path d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg></div>
                                <p>Select an event to view its photos</p>
                                <small>Choose an event from the dropdown above</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Event Modal -->
<div id="eventModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16" style="display: inline; margin-right: 8px;">
                <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
            </svg> Schedule New Event</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <?php if ($canCreate): ?>
                <form method="POST" id="eventForm">
                    <input type="hidden" name="add_event" value="1">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Event Title *</label>
                            <input type="text" name="title" class="form-input" placeholder="e.g., Home Visit - Espiritu 401" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Event Type *</label>
                            <select name="event_type" class="form-select" required>
                                <option value="">Select Event Type</option>
                                <?php foreach ($availableEventTypes as $key => $name): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>">
                                        <?php echo $eventTypeIcons[$key] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-event" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>'; ?> <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Date *</label>
                            <input type="date" name="event_date" class="form-input" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Time *</label>
                            <input type="time" name="event_time" class="form-input" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-input" placeholder="e.g., Study Room, Barangay Hall">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Assigned To</label>
                            <input type="text" name="assigned_to" class="form-input" placeholder="e.g., Maria Santos, Dr. Group">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" rows="3" placeholder="Brief description of the event..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-textarea" rows="2" placeholder="Any special instructions or notes..."></textarea>
                    </div>

                     <!-- ADD CUSTOM FIELDS SECTION -->
                     <?php if ($fieldManager && !empty($scheduleCustomFields)): ?>
                    <div class="custom-fields-section">
                        <h4>Additional Event Information</h4>
                        <div class="form-grid">
                            <?php foreach ($scheduleCustomFields as $field): 
                                $existingValue = $existingScheduleCustomValues[$field['field_name']] ?? '';
                                echo str_replace(
                                    'name="custom_field[' . $field['field_name'] . ']"',
                                    'name="custom_field_' . $field['field_name'] . '"',
                                    $fieldManager->renderField($field, $existingValue)
                                );
                            endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                        </svg> Save Event</button>
                    </div>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: #888;">
                    <div style="font-size: 48px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-lock" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2M5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1"/>
                    </svg></div>
                    <h3 style="color: #856404; margin-bottom: 10px;">Read-Only Access</h3>
                    <p>You do not have permission to create events.</p>
                    <button type="button" class="btn-cancel" onclick="closeModal()" style="margin-top: 20px;">Close</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Gallery Modal -->
<div id="galleryModal" class="modal-overlay">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3 id="galleryModalTitle"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-images" viewBox="0 0 16 16" style="display: inline; margin-right: 8px;">
                <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
            </svg> Event Gallery</h3>
            <button class="modal-close" onclick="closeGalleryModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="gallery-tabs">
                <button class="gallery-tab-btn active" onclick="switchGalleryTab('photos', this)">Photos</button>
                <button class="gallery-tab-btn active" onclick="switchGalleryTab('upload', this)"> Upload Photo</button>
            </div>
            
            <div id="photosTab" class="gallery-tab-content active">
                <div class="photos-grid" id="photosGrid">
                    <!-- Photos will be loaded here -->
                </div>
            </div>
            
            <div id="uploadTab" class="gallery-tab-content">
                <div class="upload-section">
                    <h4>Upload Event Photos</h4>
                    <form method="POST" enctype="multipart/form-data" id="uploadForm">
                        <input type="hidden" name="upload_event_image" value="1">
                        <input type="hidden" name="event_id" id="uploadEventId">
                        
                        <div class="form-group">
                            <label class="form-label">Select Photos (Multiple files allowed)</label>
                            <div class="file-upload-area" onclick="document.getElementById('eventImagesInput').click()">
                                <div class="upload-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16">
                                    <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                    <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0"/>
                                    </svg></div>
                                <div class="upload-text">Drag & drop photos here or click to browse</div>
                                <div class="upload-subtext">Supports: JPG, PNG, GIF, WebP (Max 5MB each)</div>
                            </div>
                            <input type="file" id="eventImagesInput" name="event_image" multiple accept=".jpg,.jpeg,.png,.gif,.webp" style="display: none;" onchange="handleEventImageUpload(event)">
                            <div id="eventImagePreview" class="file-preview"></div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Caption</label>
                            <input type="text" name="caption" class="form-input" placeholder="Brief caption for the images">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="image_description" class="form-textarea" rows="3" placeholder="Detailed description of the photos..."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="clearEventImageUpload()">Clear Selection</button>
                            <button type="submit" class="btn-submit"> Upload Photos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activities Modal -->
<div id="activitiesModal" class="modal-overlay">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-card-checklist" viewBox="0 0 16 16" style="display: inline; margin-right: 8px;">
                <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/>
                <path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/>
            </svg> Recent Activities & Events</h3>
            <button class="modal-close" onclick="closeActivitiesModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="activities-list">
                <?php if (!empty($recentActivities)): ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?php 
                                $activityIcons = [
                                    'event_created' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>',
                                    'event_deleted' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/></svg>',
                                    'status_changed' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z"/></svg>',
                                    'email_sent' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/></svg>',
                                    'photo_uploaded' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-image" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg>',
                                    'article_added' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-text" viewBox="0 0 16 16"><path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0-2a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg>'
                                ];
                                echo $activityIcons[$activity['activity_type']] ?? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2"/></svg>';
                                ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <strong><?php echo htmlspecialchars($activity['username']); ?></strong>
                                    <?php echo htmlspecialchars($activity['activity_type']); ?>
                                </div>
                                <div class="activity-details">
                                    <?php echo htmlspecialchars($activity['details'] ?? ''); ?>
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-activities">
                        <div class="no-activities-icon"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
                            <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                            <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                            <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                        </svg></div>
                        <p>No recent activities found</p>
                        <small>Activities will appear here as events are created and modified</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Availability Modal -->
<div id="availabilityModal" class="modal-overlay">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar-x" viewBox="0 0 16 16" style="display: inline; margin-right: 8px;">
                <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
            </svg> Manage Calendar Availability</h3>
            <button class="modal-close" onclick="closeAvailabilityModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="availability-tabs">
                <button class="availability-tab-btn active" onclick="switchAvailabilityTab('mark', this)"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-slash-circle" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="M11.354 4.646a.5.5 0 0 0-.708 0l-6 6a.5.5 0 0 0 .708.708l6-6a.5.5 0 0 0 0-.708"/>
                </svg> Mark Unavailable</button>
                <button class="availability-tab-btn" onclick="switchAvailabilityTab('view', this)"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                    <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg> View Unavailable Dates</button>
            </div>
            
            <!-- Mark Unavailable Tab -->
            <div id="markTab" class="availability-tab-content active">
                <form method="POST" id="availabilityForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Unavailable Date *</label>
                            <input type="date" name="unavailable_date" id="unavailable_date" class="form-input" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Start Time (Optional)</label>
                            <input type="time" name="start_time" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">End Time (Optional)</label>
                            <input type="time" name="end_time" class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Reason (Optional)</label>
                        <textarea name="unavailable_reason" class="form-textarea" rows="3" 
                                  placeholder="Why is this date unavailable? (e.g., Holiday, Maintenance, etc.)"></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeAvailabilityModal()">Cancel</button>
                        <button type="submit" name="mark_unavailable" class="btn-submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-slash-circle" viewBox="0 0 16 16" style="display: inline; margin-right: 5px;">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                            <path d="M11.354 4.646a.5.5 0 0 0-.708 0l-6 6a.5.5 0 0 0 .708.708l6-6a.5.5 0 0 0 0-.708"/>
                        </svg> Mark as Unavailable</button>
                    </div>
                </form>
            </div>
            
            <!-- View Unavailable Dates Tab -->
            <div id="viewTab" class="availability-tab-content">
                <div class="unavailable-dates-list" id="unavailableDatesList">
                    <!-- Unavailable dates will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification.success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}
.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.icon {
    display: inline-block;
    
}

:root {
    --svg-color: #1e293b;
    --svg-accent-color: #3b82f6;
    --svg-muted-color: #64748b;
    }

.dark-theme {
    --svg-color: #e2e8f0;
    --svg-accent-color: #60a5fa;
    --svg-muted-color: #94a3b8;
}

/* Apply to ALL SVGs automatically */
svg {
    color: var(--svg-color) !important;
    transition: color 0.3s ease, fill 0.3s ease;
}

/* Accent colored icons */
.event-icon svg,
.stat-card svg,
.calendar-table td.has-event::after svg {
    color: var(--svg-accent-color) !important;
}

/* Muted icons */
.event-item.completed svg,
.calendar-table td.other-month svg {
    color: var(--svg-muted-color) !important;
    fill: var(--svg-muted-color) !important;
}

/* Buttons inherit their colors */
.btn svg {
    color: inherit !important;
    fill: inherit !important;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 400px;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s 
ease;
    border-left: 4px solid;
}

.light-theme .section-title{
    color: black;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.notification-icon {
    font-size: 20px;
    font-weight: bold;
}

.notification-message {
    font-size: 14px;
    opacity: 0.9;
}

.notification-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}
/* Availability Modal Styles */
.availability-tabs {
    display: flex;
    border-bottom: 1px solid #3a3a3a;
    margin-bottom: 20px;
}

.availability-tab-btn {
    background: none;
    border: none;
    color: #888;
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.availability-tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.availability-tab-content {
    display: none;
}

.availability-tab-content.active {
    display: block;
}

.unavailable-dates-list {
    max-height: 400px;
    overflow-y: auto;
}

.unavailable-dates-header {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #3a3a3a;
}

.unavailable-dates-header h4 {
    color: #ffffff;
    margin-bottom: 5px;
}

.unavailable-date-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #2a2a2a;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 4px solid #dc3545;
    transition: all 0.2s;
}

.light-theme .unavailable-date-item {
    background: #f9f9f9;
    border-left-color: #dc3545;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.dark-theme .unavailable-date-item:not(.past):hover {
    background: #333;
    transform: translateX(4px);
}

.light-theme .unavailable-date-item:not(.past):hover {
    background: #e0e0e0;
    transform: translateX(4px);
}

.dark-theme .unavailable-date-item.past {
    opacity: 0.6;
    background: #1a1a1a;
}

.light-theme .unavailable-date-item.past {
    background: #e8e8e8;
}

.date-info {
    flex: 1;
}

.date-main {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 5px;
}

.dark-theme .date-display {
    color: #ffffff;
    font-weight: 600;
    font-size: 14px;
}

.light-theme .date-display {
    color: #333333;
    font-weight: 600;
    font-size: 14px;
}

.dark-theme .time-display {
    color: #b8c5ff;
    font-size: 12px;
}

.light-theme .time-display {
    color: #047857;
    font-size: 12px;
}

.dark-theme .date-reason {
    color: #cccccc;
    font-size: 13px;
}

.light-theme .date-reason {
    color: #555555;
    font-size: 13px;
}

.date-actions .remove-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.2s;
}

.date-actions .remove-btn:hover {
    background: #c82333;
}

.past-badge {
    background: #6c757d;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.no-unavailable-dates {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.error-message {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid #dc3545;
    border-radius: 8px;
    margin: 10px 0;
}

/* Calendar Unavailable Styles */
.calendar-table td.unavailable {
    background: rgba(220, 53, 69, 0.3) !important;
    border: 1px solid rgba(220, 53, 69, 0.5) !important;
    position: relative;
    color: #ff6b6b;
}

.calendar-table td.unavailable::after {
    content: '🚫';
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 10px;
}

.calendar-table td.unavailable.today {
    background: rgba(220, 53, 69, 0.5) !important;
    border: 2px solid #dc3545 !important;
}

.calendar-table td.unavailable.has-event {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.3) 0%, rgba(220, 53, 69, 0.3) 100%) !important;
}

.calendar-table td.unavailable.has-completed-event {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.3) 0%, rgba(220, 53, 69, 0.3) 100%) !important;
}

/* Fix legend dot color */
.unavailable-dot {
    background: #dc3545 !important;
}



/* Gallery Styles */
.gallery-tabs {
    display: flex;
    border-bottom: 1px solid #3a3a3a;
    margin-bottom: 20px;
}

.gallery-tab-btn {
    background: none;
    border: none;
    color: #888;
    padding: 12px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.gallery-tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.gallery-tab-content {
    display: none;
}

.gallery-tab-content.active {
    display: block;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.photo-item {
    background: #1a1a1a;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #3a3a3a;
    transition: transform 0.2s, box-shadow 0.2s;
}

.photo-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.photo-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-bottom: 1px solid #3a3a3a;
}

.light-theme .modal-header h3 {
    display: block;
    font-size: 1.17em;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    font-weight: bold;
    unicode-bidi: isolate;
    color: #1e293b;
}

.photo-info {
    padding: 15px;
}

.photo-caption {
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 16px;
    line-height: 1.3;
}

.photo-description {
    color: #cccccc;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 10px;
}

.photo-date {
    color: #888888;
    font-size: 12px;
    text-align: right;
}

/* Gallery Button */
.gallery-btn {
    background: #6f42c1;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.gallery-btn:hover {
    background: #5a2d9c;
}

/* Large Modal */
.large-modal {
    max-width: 900px;
    width: 95%;
}

/* Activities Styles */
.activities-list {
    max-height: 60vh;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #3a3a3a;
    align-items: flex-start;
}

.activity-icon {
    font-size: 20px;
    margin-top: 2px;
}

.activity-content {
    flex: 1;
}

.dark-theme .activity-title {
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 5px;
}

.light-theme {
    --activity-bg: #f9f9f9;
    --activity-border: #e0e0e0;
    --activity-text: #333333;
}

.dark-theme .activity-details {
    color: #cccccc;
    font-size: 14px;
    margin-bottom: 5px;
}

.light-theme .activity-details {
    color: #555555;
    font-size: 14px;
    margin-bottom: 5px;
}

.activity-time {
    color: #888888;
    font-size: 12px;
}

.no-activities {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.no-activities-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Fix legend dot color */
.unavailable-dot {
    background: #dc3545 !important;
}

.custom-fields-section {
    margin: 20px 0;
    padding: 20px;
    background: #333;
    border-radius: 8px;
    border: 1px solid #444;
}

.custom-fields-section h4 {
    color: #3b82f6;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #444;
}

.custom-fields-section .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.custom-field .form-label {
    color: #b8c5ff;
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.custom-field .form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #3a3a3a;
    border-radius: 4px;
    background: #1a1a1a;
    color: white;
    font-size: 14px;
}

.custom-field .help-text {
    color: #888;
    font-size: 12px;
    font-style: italic;
    margin-top: 4px;
}

.custom-field .radio-option,
.custom-field .checkbox-option {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    padding: 8px;
    background: #2a2a2a;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
}

.custom-field .radio-option input,
.custom-field .checkbox-option input {
    margin-right: 8px;
    transform: scale(1.2);
}

.custom-field .radio-option label,
.custom-field .checkbox-option label {
    color: #cccccc;
    font-weight: 500;
    margin-bottom: 0;
    cursor: pointer;
}

.custom-field .radio-option:hover,
.custom-field .checkbox-option:hover {
    background: #333333;
    border-color: #4a4a4a;
}

.custom-field .radio-option input:checked + label,
.custom-field .checkbox-option input:checked + label {
    color: #3b82f6;
    font-weight: 600;
}

.email-presets {
    margin: 10px 0;
}

.preset-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 5px;
}

.preset-btn {
    background: #444;
    color: white;
    border: 1px solid #555;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.preset-btn:hover {
    background: #555;
}
.email-reminder-btn {
    background: #6f42c1;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.email-reminder-btn:hover {
    background: #5a2d9c;
}

/* Email Preview Styles */
.email-preview {
    margin: 15px 0;
    padding: 15px;
    background: #2a2a2a;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.preview-content {
    margin-top: 10px;
    font-size: 14px;
}

.email-list {
    margin-top: 10px;
    max-height: 150px;
    overflow-y: auto;
}

.email-item {
    padding: 5px 10px;
    margin: 2px 0;
    border-radius: 4px;
    font-size: 13px;
    background: #333;
}

.email-item.valid {
    background: rgba(40, 167, 69, 0.1);
    border-left: 2px solid #28a745;
}

.email-item.invalid {
    background: rgba(220, 53, 69, 0.1);
    border-left: 2px solid #dc3545;
}

.form-help {
    color: #888;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #444;
    border-radius: 6px;
    background: #2a2a2a;
    color: white;
    font-family: monospace;
    font-size: 14px;
    resize: vertical;
    min-height: 120px;
}

.form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

/* Enhanced Styles */
.header-actions {
    display: flex;
    gap: 10px;
}

.btn-secondary {
    background: #2d5f8d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
}

.btn-secondary:hover {
    background: #5a6268;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.stat-card {
    background: #2a2a2a;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #3a3a3a;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.stat-label {
    color: #64748b;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.light-theme .file-upload-area {
    border: 2px dashed #2d5f8d;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s 
ease;
    background: #f0f6fb;
    margin-bottom: 24px;
}

.stat-value {
    color: #ffffff;
    font-size: 28px;
    font-weight: 700;
}

.events-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.events-count {
    display: flex;
    gap: 15px;
    font-size: 12px;
}

.scheduled-count {
    background: #3b82f6;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
}

.completed-count {
    background: #28a745;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    opacity: 0.8;
}

.events-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Event Item Styles */
.dark-theme .event-item {
    background: #333;
    border-radius: 10px;
    padding: 16px;
    border-left: 4px solid #3b82f6;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    gap: 12px;
}

.light-theme .event-item {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 10px;
    padding: 16px;
    border-left: 4px solid #3b82f6;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    gap: 12px;
}

.event-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Completed Events - Dark Style */
.event-item.completed {
    background: #1a1a1a;
    border-left: 4px solid #28a745;
    opacity: 0.7;
}

.light-theme .event-item.completed {
    background: #e8e8e8;
    border-left: 4px solid #28a745;
    opacity: 0.7;
}


.event-item.completed .event-icon {
    opacity: 0.6;
}

.event-item.completed .event-date,
.event-item.completed .event-title,
.event-item.completed .event-details,
.event-item.completed .event-description {
    color: #666;
}

.event-item.completed .event-notes {
    background: rgba(40, 167, 69, 0.1);
    border-left: 2px solid #28a745;
    color: #666;
}

.event-icon {
    font-size: 24px;
    display: flex;
    align-items: flex-start;
    padding-top: 2px;
}

.event-content {
    flex: 1;
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 8px;
}

.event-main {
    flex: 1;
}

.dark-theme .event-date {
    color: #b8c5ff;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 4px;
}

.light-theme .event-date {
    color: #18338c;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 4px;
}

.dark-theme .event-title {
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 6px;
    line-height: 1.3;
}

.light-theme .event-title {
    color: #1e293b;
    font-size: 15px;
    font-weight: 600;
    margin-bottom: 6px;
    line-height: 1.3;
}

.event-details {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: #ccc;
}

.dark-theme .event-details span {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
}

.light-theme .event-details span {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #888;
    font-size: 13px;
}

.event-description {
    color: #999;
    font-size: 12px;
    margin-top: 6px;
    line-height: 1.4;
}

.event-notes {
    background: rgba(59, 130, 246, 0.1);
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 11px;
    color: #b8c5ff;
    border-left: 2px solid #3b82f6;
    margin-top: 8px;
}

.auto-complete-notice {
    background: rgba(255, 193, 7, 0.1);
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 10px;
    color: #ffc107;
    border-left: 2px solid #ffc107;
    margin-top: 8px;
    font-weight: 600;
}

.event-actions {
    display: flex;
    gap: 8px;
    align-items: flex-start;
}

.status-select {
    background: #2a2a2a;
    border: 1px solid #444;
    border-radius: 6px;
    color: #fff;
    padding: 6px 10px;
    font-size: 11px;
    cursor: pointer;
    min-width: 120px;
}

.delete-form {
    margin: 0;
}

.delete-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.delete-btn:hover {
    background: #c82333;
}

.no-events {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.no-events-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

.no-events p {
    margin-bottom: 20px;
    font-size: 14px;
}


/* Calendar styles */
.calendar-table {
    width: 100%;
    border-collapse: collapse;
    background: #333;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 15px;
}

.dark-theme .calendar-table th {
    background: #404040;
    color: #b8c5ff;
    font-weight: 600;
    padding: 12px 8px;
    text-align: center;
    font-size: 12px;
    text-transform: uppercase;
}

.light-theme .calendar-table th {
    color: #b8c5ff;
    font-weight: 600;
    padding: 12px 8px;
    text-align: center;
    font-size: 12px;
    text-transform: uppercase;
}

.dark-theme .calendar-table td {
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #404040;
    transition: background-color 0.2s;
    position: relative;
}

.light-theme .calendar-table td {
    padding: 12px 8px;
    text-align: center;
    cursor: pointer;
    border: 1px solid #e0e0e0;
    transition: background-color 0.2s;
    position: relative;
}   

.light-theme #monthSelect {
    padding: 8px 12px;
    border: 1px solid black;
    border-radius: 6px;
    background: white;
    color: black;
    min-width: 150px;
}

.dark-theme #monthSelect {
    padding: 8px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    min-width: 150px;
}

.light-theme #yearSelect {
    padding: 8px 12px;
    border: 1px solid black;
    border-radius: 6px;
    background: white;
    color: black;
    min-width: 100px;
}

.dark-theme #yearSelect {
    padding: 8px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    min-width: 100px;
}

.gallery-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    justify-content: center;
}

.calendar-table td:hover {
    background-color: rgba(59, 130, 246, 0.1);
}

.dark-theme .calendar-table td.other-month {
    color: #666;
    background-color: #2a2a2a;
}

.light-theme .calendar-table td.other-month {
    background-color: #f0f0f0;
    color: #aaa;
}





.light-theme .view-gallery {
    padding: 8px 12px;
    border: 1px solid black;
    border-radius: 6px;
    background: white;
    color: black;
    min-width: 100px;
}

.dark-theme .view-gallery {
    padding: 8px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    min-width: 100px;
}

.calendar-table td.today {
    background-color: rgba(0, 188, 212, 0.2);
    border: 2px solid #00bcd4;
    font-weight: 600;
    color: #00bcd4;
}

/* Calendar dots for events */
.calendar-table td.has-event::after {
    content: '';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 6px;
    height: 6px;
    background: #3b82f6;
    border-radius: 50%;
}

.calendar-table td.has-completed-event::after {
    content: '';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 6px;
    height: 6px;
    background: #28a745;
    border-radius: 50%;
    opacity: 0.6;
}

.calendar-table td.has-event.today::after,
.calendar-table td.has-completed-event.today::after {
    background: #ffffff;
}

.calendar-legend {
    display: flex;
    gap: 20px;
    justify-content: center;
    font-size: 12px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.light-theme .legend-dot span {
    display: flex;
    gap: 15px;
    justify-content: center;
    font-size: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
    color: #1e293b;
}

.scheduled-dot {
    background:rgb(245, 165, 16);
}

.completed-dot {
    background: #28a745;
    opacity: 0.6;
}

.events-tabs {
    display: flex;
    border-bottom: 1px solid #3a3a3a;
    margin-bottom: 20px;
}

.dark-theme .events-tabs .tab-btn {
    background: none;
    border: none;
    color: #b8c5ff;
    padding: 12px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.light-theme .events-tabs .tab-btn {
    background: none;
    border: none;
    color: #1e293b;
    padding: 12px 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.events-tabs .tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.light-theme .events-tabs .tab-btn:hover {
    background: rgba(59, 130, 246, 0.05);
}


.events-tab-content {
    display: none;
}

.events-tab-content.active {
    display: block;
}

/* Enhanced Calendar Highlights */
.calendar-table td.has-event {
    background: rgba(59, 130, 246, 0.15) !important;
    border: 1px solid rgba(59, 130, 246, 0.3) !important;
    position: relative;
}

.calendar-table td.has-completed-event {
    background: rgba(40, 167, 69, 0.15) !important;
    border: 1px solid rgba(40, 167, 69, 0.3) !important;
    position: relative;
}

.calendar-table td.has-event.has-completed-event {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(40, 167, 69, 0.15) 100%) !important;
    border: 1px solid rgba(59, 130, 246, 0.3) !important;
}

/* Calendar dots for events - make them more visible */
.calendar-table td.has-event::after {
    content: '';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 8px;
    height: 8px;
    background: rgb(245, 165, 16);
    border-radius: 50%;
    border: 1px solid #ffffff;
}

.calendar-table td.has-completed-event::after {
    content: '';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    border: 1px solid #ffffff;
}

.calendar-table td.has-event.has-completed-event::after {
    background: linear-gradient(135deg, #3b82f6 0%, #28a745 100%);
}

/* Today cell with events */
.calendar-table td.today.has-event {
    background: rgba(0, 188, 212, 0.3) !important;
    border: 2px solid #00bcd4 !important;
}

.calendar-table td.today.has-completed-event {
    background: rgba(40, 167, 69, 0.3) !important;
    border: 2px solid #28a745 !important;
}

.calendar-table td.today.has-event::after,
.calendar-table td.today.has-completed-event::after {
    border: 1px solid #00bcd4;
}

/* Hover effects */
.calendar-table td.has-event:hover {
    background: rgba(59, 130, 246, 0.25) !important;
}

.calendar-table td.has-completed-event:hover {
    background: rgba(40, 167, 69, 0.25) !important;
}

.calendar-table td.has-event.has-completed-event:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.25) 0%, rgba(40, 167, 69, 0.25) 100%) !important;
}

/* Enhanced Legend Styles */
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}



.completed-dot {
    background: #28a745;
}

.today-dot {
    background: #00bcd4;
}

.both-dot {
    background: linear-gradient(135deg, #3b82f6 0%, #28a745 100%);
}

.calendar-legend {
    display: flex;
    gap: 15px;
    justify-content: center;
    font-size: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.light-theme .calendar-legend {
    display: flex;
    gap: 15px;
    justify-content: center;
    font-size: 12px;
    flex-wrap: wrap;
    margin-top: 10px;
    color: #1e293b;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-email {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
}

.btn-info {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-info:hover {
    background: #138496;
}

/* File Upload Styles for Schedule Gallery */
.dark-theme .file-upload-area {
    border: 2px dashed #3a3a3a;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #2a2a2a;
}

.file-upload-area:hover {
    background: #e8f2f9;
    border-color: #1f5a8d;
}

.file-upload-area.dragover {
    border-color: #3b82f6;
    background: rgba(59, 130, 246, 0.1);
}

.upload-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.upload-text {
    color: #3b82f6;
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 8px;
}

.upload-subtext {
    color: #888;
    font-size: 14px;
}

.file-preview {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.file-preview-item {
    background: #333;
    padding: 10px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    border: 1px solid #444;
}

.file-preview-item .remove-btn {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-preview-item .remove-btn:hover {
    background: #c82333;
}
</style>

<script>
// Gallery Modal
let currentGalleryEventId = null;

// Calendar functionality with year-specific filtering
let currentDate = new Date();
const serverScheduledDays = <?php echo json_encode($scheduled_days ?? []); ?>;
const serverCompletedDays = <?php echo json_encode($completed_days ?? []); ?>;
const serverUnavailableDays = <?php echo json_encode($unavailable_days ?? []); ?>;

function generateCalendar() {
    const calendarBody = document.getElementById('calendarBody');
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month title
    document.getElementById('currentMonth').textContent = 
        monthNames[month] + ' ' + year;
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    
    let html = '';
    let day = 1;
    
    // Get events for this specific month and year
    const scheduledDays = getScheduledDaysForCurrentMonth();
    const completedDays = getCompletedDaysForCurrentMonth();
    const unavailableDays = getUnavailableDaysForCurrentMonth();
    
    for (let i = 0; i < 6; i++) {
        html += '<tr>';
        for (let j = 0; j < 7; j++) {
            if (i === 0 && j < firstDay) {
                const prevMonth = new Date(year, month, 0).getDate();
                html += `<td class="other-month">${prevMonth - firstDay + j + 1}</td>`;
            } else if (day > daysInMonth) {
                html += `<td class="other-month">${day - daysInMonth}</td>`;
                day++;
            } else {
                let classes = '';
                const cellDate = new Date(year, month, day);
                
                // Check if this day is unavailable
                const unavailableInfo = unavailableDays.find(d => d.day === day);
                if (unavailableInfo) {
                    classes += 'unavailable ';
                }
                
                // Check if this day has scheduled events (blue highlight)
                if (scheduledDays.includes(day)) {
                    classes += 'has-event ';
                }
                
                // Check if this day has completed events (green highlight)
                if (completedDays.includes(day)) {
                    classes += 'has-completed-event ';
                }
                
                // Check if today
                if (cellDate.toDateString() === today.toDateString()) {
                    classes += 'today';
                }
                
                let title = '';
                if (unavailableInfo) {
                    title = `Unavailable: ${unavailableInfo.reason || 'No reason specified'}`;
                    if (unavailableInfo.start_time) {
                        title += `\nTime: ${unavailableInfo.start_time}`;
                        if (unavailableInfo.end_time) {
                            title += ` - ${unavailableInfo.end_time}`;
                        }
                    }
                }
                
                html += `<td class="${classes.trim()}" title="${title}" onclick="viewDayEvents(${day})" style="cursor: pointer; color:#2d5f8d">${day}</td>`;
                day++;
            }
        }
        html += '</tr>';
        if (day > daysInMonth) break;
    }
    
    calendarBody.innerHTML = html;
    
    // Update the calendar controls to reflect current view
    updateCalendarControls();
}

function getUnavailableDaysForCurrentMonth() {
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    try {
        // Check if we have data for this specific year-month combination
        if (serverUnavailableDays && serverUnavailableDays[currentYear] && 
            serverUnavailableDays[currentYear][currentMonth] && 
            Array.isArray(serverUnavailableDays[currentYear][currentMonth])) {
            return serverUnavailableDays[currentYear][currentMonth];
        }
        // Fallback to old structure (just by month)
        else if (serverUnavailableDays && serverUnavailableDays[currentMonth] && 
                 Array.isArray(serverUnavailableDays[currentMonth])) {
            return serverUnavailableDays[currentMonth];
        }
    } catch (e) {
        console.error('Error getting unavailable days:', e);
    }
    return [];
}

function getScheduledDaysForCurrentMonth() {
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    try {
        // Check new structure first, then fallback
        if (serverScheduledDays && serverScheduledDays[currentYear] && 
            serverScheduledDays[currentYear][currentMonth] && 
            Array.isArray(serverScheduledDays[currentYear][currentMonth])) {
            return serverScheduledDays[currentYear][currentMonth];
        }
        // Fallback to old structure
        else if (serverScheduledDays && serverScheduledDays[currentMonth] && 
                 Array.isArray(serverScheduledDays[currentMonth])) {
            return serverScheduledDays[currentMonth];
        }
    } catch (e) {
        console.error('Error getting scheduled days:', e);
    }
    return [];
}

function getCompletedDaysForCurrentMonth() {
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth() + 1;
    
    try {
        // Check new structure first, then fallback
        if (serverCompletedDays && serverCompletedDays[currentYear] && 
            serverCompletedDays[currentYear][currentMonth] && 
            Array.isArray(serverCompletedDays[currentYear][currentMonth])) {
            return serverCompletedDays[currentYear][currentMonth];
        }
        // Fallback to old structure
        else if (serverCompletedDays && serverCompletedDays[currentMonth] && 
                 Array.isArray(serverCompletedDays[currentMonth])) {
            return serverCompletedDays[currentMonth];
        }
    } catch (e) {
        console.error('Error getting completed days:', e);
    }
    return [];
}

// Calendar Navigation Functions
function changeCalendarYear() {
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value);
    
    currentDate = new Date(selectedYear, selectedMonth - 1, 1);
    generateCalendar();
}

function changeCalendar() {
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value);
    
    currentDate = new Date(selectedYear, selectedMonth - 1, 1);
    generateCalendar();
}

function updateCalendarControls() {
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    
    if (yearSelect && monthSelect) {
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1;
        
        // Update selects to match current view
        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
    }
}

// Gallery Functions
function loadGalleryPhotos() {
    console.log('loadGalleryPhotos called');
    const eventId = document.getElementById('galleryEventFilter').value;
    const photosGrid = document.getElementById('allPhotosGrid');
    
    if (!eventId) {
        photosGrid.innerHTML = `
            <div class="no-events">
                <div class="no-events-icon"></div>
                <p>Select an event to view its photos</p>
                <small>Choose an event from the dropdown above</small>
            </div>
        `;
        return;
    }
    
    photosGrid.innerHTML = '<div class="loading">Loading photos...</div>';
    
    // Create form data for the request
    const formData = new FormData();
    formData.append('event_id', eventId);
    
    fetch('ajax-get-event-gallery.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' // Include cookies/session
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' . response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Photos data received:', data);
        if (data.success && data.photos && data.photos.length > 0) {
            displayPhotos(data.photos, photosGrid);
        } else {
            showNoPhotosMessage(photosGrid, data.error || 'No photos found for this event');
        }
    })
    .catch(error => {
        console.error('Error loading photos:', error);
        showErrorMessage(photosGrid, 'Failed to load photos: ' + error.message);
    });
}

function displayPhotos(photos, container) {
    let html = '';
    photos.forEach(photo => {
        // Make sure the image path is correct
        const imagePath = photo.image_path.startsWith('http') ? photo.image_path : 
                         (photo.image_path.startsWith('/') ? photo.image_path : 
                         './' + photo.image_path);
        
        html += `
            <div class="photo-item">
                <img src="${imagePath}" alt="${photo.caption || 'Event photo'}" class="photo-image" 
                     onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI1MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiMzYTNhM2EiLz48c3ZnIHg9Ijc1IiB5PSI1NSIgd2lkdGg9IjUwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2I4YzVmZiIgc3Ryb2tlLXdpZHRoPSIyIj48cGF0aCBkPSJNMjAgMjFVMTlBMiAyIDAgMCAxIDIyIDE3SDI4QTIgMiAwIDAgMSAzMCAxOVYyMU0xNiA1QTcgNyAwIDEgMSAyIDVBMTYgMTYgMCAwIDEgMTYgNVoiLz48L3N2Zz48L3N2Zz4='">
                <div class="photo-info">
                    <div class="photo-caption">${photo.caption || 'No caption'}</div>
                    <div class="photo-description">${photo.description || 'No description'}</div>
                    <div class="photo-meta">
                        <span class="photo-date">${new Date(photo.created_at).toLocaleDateString()}</span>
                        <span class="photo-uploader">By: ${photo.uploaded_by_name || 'Unknown'}</span>
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function showNoPhotosMessage(container, message) {
    container.innerHTML = `
        <div class="no-photos">
            <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
                <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3-event" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                <path d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg></div>
                <p>${message}</p>
                <small>Upload photos using the "Manage Photos" button</small>
            </div>
        </div>
    `;
}

function showErrorMessage(container, message) {
    container.innerHTML = `
        <div class="error-message">
            <div style="text-align: center; padding: 40px; color: #dc3545; grid-column: 1 / -1;">
                <div style="font-size: 48px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                </svg></div>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="loadGalleryPhotos()" style="margin-top: 10px;">
                    Try Again
                </button>
            </div>
        </div>
    `;
}

<<<<<<< HEAD
=======
function displayPhotos(photos, container) {
    let html = '';
    photos.forEach(photo => {
        html += `
            <div class="photo-item">
                <img src="${photo.image_path}" alt="${photo.caption || 'Event photo'}" class="photo-image" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI1MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiMzYTNhM2EiLz48c3ZnIHg9Ijc1IiB5PSI1NSIgd2lkdGg9IjUwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2I4YzVmZiIgc3Ryb2tlLXdpZHRoPSIyIj48cGF0aCBkPSJNMjAgMjFVMTlBMiAyIDAgMCAxIDIyIDE3SDI4QTIgMiAwIDAgMSAzMCAxOVYyMU0xNiA1QTcgNyAwIDEgMSAyIDVBMTYgMTYgMCAwIDEgMTYgNVoiLz48L3N2Zz48L3N2Zz4='">
                <div class="photo-info">
                    <div class="photo-caption">${photo.caption || 'No caption'}</div>
                    <div class="photo-description">${photo.description || 'No description'}</div>
                    <div class="photo-meta">
                        <span class="photo-date">${new Date(photo.created_at).toLocaleDateString()}</span>
                        <span class="photo-uploader">By: ${photo.uploaded_by_name || 'Unknown'}</span>
                    </div>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
}

function showNoPhotosMessage(container, message) {
    container.innerHTML = `
        <div class="no-photos">
            <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
                <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3-event" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                <path d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg></div>
                <p>${message}</p>
                <small>Upload photos using the "Manage Photos" button</small>
            </div>
        </div>
    `;
}

>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12
function openGalleryForSelectedEvent() {
    const eventId = document.getElementById('galleryEventFilter').value;
    if (!eventId) {
        alert('Please select an event first');
        return;
    }
    
    const eventSelect = document.getElementById('galleryEventFilter');
    const selectedOption = eventSelect.options[eventSelect.selectedIndex];
    const eventTitle = selectedOption.text.split(' (')[0]; // Remove date part
    
    openGalleryModal(eventId, eventTitle);
}

function openGalleryModal(eventId, eventTitle) {
    currentGalleryEventId = eventId;
    document.getElementById('galleryModalTitle').textContent = ` Gallery: ${eventTitle}`;
    document.getElementById('uploadEventId').value = eventId;
    
    document.getElementById('galleryModal').classList.add('active');
    
    // Load gallery content
    loadEventPhotos(eventId);
    
    // Switch to photos tab by default
    switchGalleryTab('photos', document.querySelector('.gallery-tab-btn'));
}

function closeGalleryModal() {
    document.getElementById('galleryModal').classList.remove('active');
}

function switchGalleryTab(tabName, element) {
    // Update tab buttons
    document.querySelectorAll('.gallery-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activate clicked tab button
    if (element) {
        element.classList.add('active');
    }
    
    // Update tab content
    document.querySelectorAll('.gallery-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName + 'Tab').classList.add('active');
}

// Add this to your JavaScript section
document.addEventListener('DOMContentLoaded', function() {
    // Switch to gallery tab if we're viewing a gallery
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('view_gallery') || urlParams.get('tab') === 'gallery') {
        switchEventsTab('gallery', document.querySelector('.events-tabs .tab-btn:nth-child(3)'));
    }
    
    // Update the form to include the active tab
    const galleryForm = document.getElementById('galleryForm');
    if (galleryForm) {
        galleryForm.addEventListener('submit', function() {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'tab';
            hiddenInput.value = 'gallery';
            this.appendChild(hiddenInput);
        });
    }
});

// Add this function to check date availability
function checkDateAvailability() {
    const dateInput = document.querySelector('input[name="event_date"]');
    const statusElement = document.getElementById('date_availability_status');
    
    if (!dateInput || !dateInput.value) {
        if (statusElement) statusElement.innerHTML = '';
        return true;
    }
    
    const dateString = dateInput.value;
    const [year, month, day] = dateString.split('-');
    const monthNum = parseInt(month);
    const dayNum = parseInt(day);
    
    // Check if date is in the current view's unavailable days
    const unavailableDays = getUnavailableDaysForCurrentMonth();
    const isUnavailable = unavailableDays.some(d => d.day === dayNum);
    
    if (isUnavailable) {
        const unavailableInfo = unavailableDays.find(d => d.day === dayNum);
        if (statusElement) {
            statusElement.innerHTML = `❌ <strong>Unavailable Date</strong><br>${unavailableInfo.reason || 'No reason specified'}`;
            statusElement.style.color = '#dc3545';
        }
        return false;
    } else {
        if (statusElement) {
            statusElement.innerHTML = ' Date is available';
            statusElement.style.color = '#28a745';
        }
        return true;
    }
}

// Add event listener to date input
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="event_date"]');
    if (dateInput) {
        dateInput.addEventListener('change', checkDateAvailability);
        
        // Create status element if it doesn't exist
        if (!document.getElementById('date_availability_status')) {
            const statusElement = document.createElement('small');
            statusElement.id = 'date_availability_status';
            statusElement.style.cssText = 'font-size: 12px; margin-top: 5px; display: block;';
            dateInput.parentNode.appendChild(statusElement);
        }
    }
});

// Prevent form submission for unavailable dates
document.getElementById('eventForm').addEventListener('submit', function(e) {
    if (!checkDateAvailability()) {
        e.preventDefault();
        alert('❌ Cannot create event on an unavailable date. Please choose an available date.');
        document.querySelector('input[name="event_date"]').focus();
    }
});

function loadEventPhotos(eventId) {
    const photosGrid = document.getElementById('photosGrid');
    
    
    // AJAX call to load photos
    fetch('ajax-get-event-gallery.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'event_id=' + encodeURIComponent(eventId)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Modal photos data:', data);
        if (data.success && data.photos && data.photos.length > 0) {
            let html = '';
            data.photos.forEach(photo => {
                html += `
                    <div class="photo-item">
                        <img src="${photo.image_path}" alt="${photo.caption || 'Event photo'}" class="photo-image" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI1MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiMzYTNhM2EiLz48c3ZnIHg9Ijc1IiB5PSI1NSIgd2lkdGg9IjUwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2I4YzVmZiIgc3Ryb2tlLXdpZHRoPSIyIj48cGF0aCBkPSJNMjAgMjFVMTlBMiAyIDAgMCAxIDIyIDE3SDI4QTIgMiAwIDAgMSAzMCAxOVYyMU0xNiA1QTcgNyAwIDEgMSAyIDVBMTYgMTYgMCAwIDEgMTYgNVoiLz48L3N2Zz48L3N2Zz4='">
                        <div class="photo-info">
                            <div class="photo-caption">${photo.caption || 'No caption'}</div>
                            <div class="photo-description">${photo.description || 'No description'}</div>
                            <div class="photo-date">${new Date(photo.created_at).toLocaleDateString()}</div>
                        </div>
                    </div>
                `;
            });
            photosGrid.innerHTML = html;
        } else {
            photosGrid.innerHTML = `
                <div class="no-photos">
                    <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
                        <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3-event" viewBox="0 0 16 16">
                                <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                <path d="M12 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                </svg></div>
                        <p>No photos uploaded yet</p>
                        <small>Switch to the Upload tab to add photos</small>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading photos:', error);
        
    });
}

// Event Image Upload Functions
function handleEventImageUpload(event) {
    const files = event.target.files;
    const preview = document.getElementById('eventImagePreview');
    preview.innerHTML = '';

    for (let file of files) {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview-item';
        fileItem.innerHTML = `
            <span>${file.name}</span>
            <span style="color: #888; font-size: 12px;">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
            <button type="button" class="remove-btn" onclick="removeEventFile(this)">×</button>
        `;
        preview.appendChild(fileItem);
    }
}

function clearEventImageUpload() {
    document.getElementById('eventImagesInput').value = '';
    document.getElementById('eventImagePreview').innerHTML = '';
}

function removeEventFile(button) {
    button.parentElement.remove();
}

// Activities Modal
function openActivitiesModal() {
    document.getElementById('activitiesModal').classList.add('active');
}

function closeActivitiesModal() {
    document.getElementById('activitiesModal').classList.remove('active');
}

// Events Tab Functions
function switchEventsTab(tabName, element) {
    // Hide all tab contents
    document.querySelectorAll('.events-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.events-tabs .tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + 'Events').classList.add('active');
    
    // Activate clicked tab button
    if (element) {
        element.classList.add('active');
    }
}

function viewDayEvents(day) {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth() + 1;
    const dateString = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
    
    // Filter events for this day
    const eventItems = document.querySelectorAll('.event-item');
    let dayEvents = [];
    
    eventItems.forEach(item => {
        const eventDate = item.getAttribute('data-event-date');
        if (eventDate === dateString) {
            const eventTitle = item.querySelector('.event-title').textContent;
            const eventTime = item.querySelector('.event-time').textContent;
            dayEvents.push({ title: eventTitle, time: eventTime });
        }
    });
    
    if (dayEvents.length > 0) {
        let message = `Events on ${dateString}:\n\n`;
        dayEvents.forEach(event => {
            message += `${event.time} - ${event.title}\n`;
        });
        alert(message);
    } else {
        alert(`No events scheduled for ${dateString}`);
    }
}

function openModal() {
    document.getElementById('eventModal').classList.add('active');
}

function closeModal() {
    document.getElementById('eventModal').classList.remove('active');
}

function refreshPage() {
    window.location.reload();
}

function testEmail(event) {
    event.preventDefault();
    alert('Email functionality test - This would send a test email in production.');
}

// Availability Modal Functions
function openAvailabilityModal() {
    document.getElementById('availabilityModal').classList.add('active');
    loadUnavailableDates(); // Load current unavailable dates
}

function closeAvailabilityModal() {
    document.getElementById('availabilityModal').classList.remove('active');
}

function switchAvailabilityTab(tabName, element) {
    // Update tab buttons
    document.querySelectorAll('.availability-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activate clicked tab button
    if (element) {
        element.classList.add('active');
    }
    
    // Update tab content
    document.querySelectorAll('.availability-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName + 'Tab').classList.add('active');
}

function loadUnavailableDates() {
    const datesList = document.getElementById('unavailableDatesList');
    datesList.innerHTML = '<div class="loading">Loading unavailable dates...</div>';
    
    fetch('ajax-get-unavailable-dates.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.dates && data.dates.length > 0) {
                displayUnavailableDates(data.dates, datesList);
            } else {
                datesList.innerHTML = `
                    <div class="no-unavailable-dates">
                        <div style="text-align: center; padding: 40px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-calendar-x" viewBox="0 0 16 16">
                                <path d="M6.146 7.146a.5.5 0 0 1 .708 0L8 8.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 9l1.147 1.146a.5.5 0 0 1-.708.708L8 9.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 9 6.146 7.854a.5.5 0 0 1 0-.708"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                </svg></div>
                            <p>No unavailable dates scheduled</p>
                            <small>All dates are currently available for scheduling</small>
                        </div>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading unavailable dates:', error);
            datesList.innerHTML = '<div class="error">Error loading unavailable dates</div>';
        });
}

function displayUnavailableDates(dates, container) {
    let html = '<div class="unavailable-dates-header">';
    html += '<h4>Upcoming Unavailable Dates</h4>';
    html += '<small>Click on a date to remove it</small>';
    html += '</div>';
    
    dates.forEach(date => {
        const dateObj = new Date(date.unavailable_date);
        const today = new Date();
        const isPast = dateObj < today;
        
        html += `
            <div class="unavailable-date-item ${isPast ? 'past' : ''}" 
                 onclick="${isPast ? '' : 'removeUnavailableDate(\'' + date.unavailable_date + '\')'}" 
                 style="cursor: ${isPast ? 'default' : 'pointer'};">
                <div class="date-info">
                    <div class="date-main">
                        <span class="date-display">${dateObj.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })}</span>
                        ${date.start_time ? `<span class="time-display"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
  <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
  <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
</svg> ${date.start_time}${date.end_time ? ' - ' + date.end_time : ''}</span>` : ''}
                    </div>
                    <div class="date-reason">${date.reason || 'No reason specified'}</div>
                </div>
                <div class="date-actions">
                    ${isPast ? '<span class="past-badge">Past</span>' : '<button class="remove-btn" title="Remove Unavailability">🗑️</button>'}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function removeUnavailableDate(dateString) {
    if (confirm(`Are you sure you want to make ${dateString} available again?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const dateInput = document.createElement('input');
        dateInput.name = 'unavailable_date';
        dateInput.value = dateString;
        
        const actionInput = document.createElement('input');
        actionInput.name = 'remove_unavailable';
        actionInput.value = '1';
        
        form.appendChild(dateInput);
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function updateEventStatus(eventId, status) {
    if (confirm(`Change event status to ${status}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const eventIdInput = document.createElement('input');
        eventIdInput.name = 'event_id';
        eventIdInput.value = eventId;
        
        const statusInput = document.createElement('input');
        statusInput.name = 'status';
        statusInput.value = status;
        
        const actionInput = document.createElement('input');
        actionInput.name = 'update_event_status';
        actionInput.value = '1';
        
        form.appendChild(eventIdInput);
        form.appendChild(statusInput);
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    } else {
        // Reset the select to its original value
        event.target.value = event.target.getAttribute('data-original-value');
    }
}

function openEmailModal(eventId) {
    const emailRecipients = prompt('Enter email addresses (comma separated):');
    if (emailRecipients) {
        if (confirm(`Send email reminder for event ${eventId} to: ${emailRecipients}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const eventIdInput = document.createElement('input');
            eventIdInput.name = 'event_id';
            eventIdInput.value = eventId;
            
            const recipientsInput = document.createElement('input');
            recipientsInput.name = 'email_recipients';
            recipientsInput.value = emailRecipients;
            
            const actionInput = document.createElement('input');
            actionInput.name = 'send_email_reminder';
            actionInput.value = '1';
            
            form.appendChild(eventIdInput);
            form.appendChild(recipientsInput);
            form.appendChild(actionInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
}

// Initialize calendar and set original values
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing calendar...');
    generateCalendar();
    
    // Initialize drag and drop for event image uploads
    const eventUploadArea = document.querySelector('.file-upload-area');
    if (eventUploadArea) {
        eventUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        eventUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        eventUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            const input = document.getElementById('eventImagesInput');
            
            if (input && files.length > 0) {
                // Create a new DataTransfer to set files
                const dt = new DataTransfer();
                for (let file of files) {
                    // Only add image files
                    if (file.type.startsWith('image/')) {
                        dt.items.add(file);
                    }
                }
                input.files = dt.files;
                
                // Trigger the change event
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    }
    
    // Set original values for status selects
    document.querySelectorAll('.status-select').forEach(select => {
        select.setAttribute('data-original-value', select.value);
    });
    
    // Initialize gallery event filter change listener
    const galleryFilter = document.getElementById('galleryEventFilter');
    if (galleryFilter) {
        galleryFilter.addEventListener('change', function() {
            console.log('Gallery filter changed to:', this.value);
            loadGalleryPhotos();
        });
    }
});

// Better error handling for AJAX calls
function handleAjaxError(error, context) {
    console.error('AJAX Error in ' + context + ':', error);
    return {
        success: false,
        error: 'Network error: ' + error.message,
        photos: [],
        dates: []
    };
}

// Modified loadEventPhotos with better error handling
function loadEventPhotos(eventId) {
    const photosGrid = document.getElementById('photosGrid');
    photosGrid.innerHTML = '<div class="loading">Loading photos...</div>';
    
    fetch('ajax-get-event-gallery.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'event_id=' + encodeURIComponent(eventId)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.photos && data.photos.length > 0) {
            let html = '';
            data.photos.forEach(photo => {
                html += `
                    <div class="photo-item">
                        <img src="${photo.image_path}" alt="${photo.caption || 'Event photo'}" class="photo-image" 
                             onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjUwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDI1MCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjI1MCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiMzYTNhM2EiLz48c3ZnIHg9Ijc1IiB5PSI1NSIgd2lkdGg9IjUwIiBoZWlnaHQ9IjQwIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2I4YzVmZiIgc3Ryb2tlLXdpZHRoPSIyIj48cGF0aCBkPSJNMjAgMjFVMTlBMiAyIDAgMCAxIDIyIDE3SDI4QTIgMiAwIDAgMSAzMCAxOVYyMU0xNiA1QTcgNyAwIDEgMSAyIDVBMTYgMTYgMCAwIDEgMTYgNVoiLz48L3N2Zz48L3N2Zz4='">
                        <div class="photo-info">
                            <div class="photo-caption">${photo.caption || 'No caption'}</div>
                            <div class="photo-description">${photo.description || 'No description'}</div>
                            <div class="photo-date">${new Date(photo.created_at).toLocaleDateString()}</div>
                        </div>
                    </div>
                `;
            });
            photosGrid.innerHTML = html;
        } else {
            photosGrid.innerHTML = `
                <div class="no-photos">
                    <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
                        <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-camera-fill" viewBox="0 0 16 16">
                                    <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                    <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0"/>
                                    </svg></div>
                        <p>${data.message || data.error || 'No photos uploaded yet'}</p>
                        <small>Switch to the Upload tab to add photos</small>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading photos:', error);
        photosGrid.innerHTML = `
            <div class="no-photos">
                <div style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">
<<<<<<< HEAD
                    <div style="font-size: 64px; margin-bottom: 16px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                    </svg></div>
=======
                    <div style="font-size: 64px; margin-bottom: 16px;">⚠️</div>
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12
                    <p>Gallery temporarily unavailable</p>
                    <small>Photo gallery will be available soon</small>
                </div>
            </div>
        `;
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>