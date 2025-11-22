<?php
$pageTitle = 'Event Types Management - Orphanfare';
require_once 'includes/superheader.php';

// Check if user is super_admin
if ($_SESSION['role'] !== 'super_admin') {
    header('Location: access-denied.php');
    exit();
}

// Initialize message variables
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_event_type'])) {
        $type_key = trim($_POST['type_key']);
        $type_name = trim($_POST['type_name']);
        $icon = trim($_POST['icon']);
        $visible_to = $_POST['visible_to'] ?? [];

        // If no roles are selected, save as empty array (visible to nobody)
        $visible_to_json = json_encode($visible_to);
        if ($visible_to_json === false) {
            $visible_to_json = '[]'; // Empty array = visible to nobody
        }
        
        // Validate inputs
        if (empty($type_key) || empty($type_name) || empty($icon)) {
            $error = "All fields are required!";
        } else {
            // Check if type_key already exists
            $check_sql = "SELECT id FROM event_types WHERE type_key = ? AND is_active = 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $type_key);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $error = "Event type key already exists!";
            } else {
                // Insert new event type with proper JSON encoding
                $visible_to_json = json_encode($visible_to);
                if ($visible_to_json === false) {
                    $visible_to_json = '[]'; // Fallback to empty array
                }
                
                $sql = "INSERT INTO event_types (type_key, type_name, icon, visible_to, is_active) 
                        VALUES (?, ?, ?, ?, 1)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $type_key, $type_name, $icon, $visible_to_json);
                
                if ($stmt->execute()) {
                    $message = "Event type added successfully!";
                } else {
                    $error = "Error adding event type: " . $conn->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    }
    
    if (isset($_POST['update_visibility'])) {
        $type_id = intval($_POST['type_id']);
        $visible_to = $_POST['visible_to'] ?? [];

        // If no roles are selected, save as empty array (visible to nobody)
        $visible_to_json = json_encode($visible_to);
        if ($visible_to_json === false) {
            $visible_to_json = '[]'; // Empty array = visible to nobody
        }
        
        // Proper JSON encoding with validation
        $visible_to_json = json_encode($visible_to);
        if ($visible_to_json === false) {
            $error = "Error encoding visibility data";
        } else {
            $sql = "UPDATE event_types SET visible_to = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $visible_to_json, $type_id);
            
            if ($stmt->execute()) {
                $message = "Visibility updated successfully!";
            } else {
                $error = "Error updating visibility: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Get all event types from database
$event_types = [];
$sql = "SELECT * FROM event_types WHERE is_active = 1 ORDER BY type_name";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Proper JSON decoding with error handling
        $visible_to = json_decode($row['visible_to'] ?? '[]', true);
        $row['visible_to'] = is_array($visible_to) ? $visible_to : [];
        $event_types[] = $row;
    }
}

// Available roles for visibility
$available_roles = ['super_admin', 'admin', 'Social Worker', 'Social Welfare Assistant', 'user'];
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">Event Types Management</h1>
    </div>

    <!-- Success/Error Notifications -->
    <?php if (isset($_GET['success'])): ?>
        <div class="notification success show">
            <div class="notification-icon">✓</div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['success']) {
                        case 'event_type_added':
                            echo 'Event type added successfully!';
                            break;
                        case 'visibility_updated':
                            echo 'Visibility updated successfully!';
                            break;
                        default:
                            echo 'Operation completed successfully!';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['error']) {
                        case 'event_type_exists':
                            echo 'Event type key already exists!';
                            break;
                        case 'validation_error':
                            echo 'All fields are required!';
                            break;
                        case 'update_failed':
                            echo 'Failed to update visibility. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <!-- PHP-generated notifications (for form submissions) -->
    <?php if ($message): ?>
        <div class="notification success show">
            <div class="notification-icon">✓</div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message"><?php echo $message; ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message"><?php echo $error; ?></div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <!-- Add New Event Type -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">Add New Event Type</div>
        </div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Type Key</label>
                    <input class="type-key" type="text" name="type_key" required placeholder="e.g., home_visit">
                </div>
                <div class="form-group">
                    <label>Type Name</label>
                    <input class="type-name" type="text" name="type_name" required placeholder="e.g., Home Visit">
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <input class="icon-event" type="text" name="icon" required placeholder="e.g., 🏠">
                </div>
            </div>
            <div class="form-group">
                <label>Visible To Roles</label>
                <div class="checkbox-group">
                    <?php foreach ($available_roles as $role): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="visible_to[]" value="<?php echo $role; ?>" checked>
                            <?php echo $role; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" name="add_event_type" class="btn btn-primary">Add Event Type</button>
        </form>
    </div>

    <!-- Manage Existing Event Types -->
    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">Manage Event Types</div>
        </div>
        
        <?php if (empty($event_types)): ?>
            <div style="text-align: center; padding: 40px; color: #888;">
                <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                <p>No event types found</p>
                <small>Add your first event type using the form above</small>
            </div>
        <?php else: ?>
            <div class="event-types-list">
                <?php foreach ($event_types as $type): ?>
                    <div class="event-type-item">
                        <div class="event-type-info">
                            <span class="event-type-icon"><?php echo $type['icon']; ?></span>
                            <div>
                                <strong><?php echo $type['type_name']; ?></strong>
                                <small>(<?php echo $type['type_key']; ?>)</small>
                            </div>
                        </div>
                        
                        <form method="POST" class="visibility-form">
                            <input type="hidden" name="type_id" value="<?php echo $type['id']; ?>">
                            <div class="visibility-options">
                                <?php foreach ($available_roles as $role): ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="visible_to[]" value="<?php echo $role; ?>"
                                            <?php echo in_array($role, $type['visible_to']) ? 'checked' : ''; ?>>
                                        <?php echo $role; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" name="update_visibility" class="btn btn-secondary">Update Visibility</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Notification Styles */
.type-key {
    width: 100%;
    padding: 10px 16px;
    background-color: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.type-name {
    width: 100%;
    padding: 10px 16px;
    background-color: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.icon-event {
    width: 100%;
    padding: 10px 16px;
    background-color: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 400px;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.notification.error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.notification.warning {
    border-left-color: #ffc107;
    background: #fff3cd;
    color: #856404;
}

.notification.info {
    border-left-color: #17a2b8;
    background: #d1ecf1;
    color: #0c5460;
}

.notification-icon {
    font-size: 20px;
    font-weight: bold;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
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

.notification-close:hover {
    opacity: 1;
}

/* Existing Event Types Styles */
.event-types-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.event-type-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px;
    background: #2a2a2a;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
}

.light-theme .event-type-item{
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px;
    background: #2d5f8d;
    border-radius: 8px;
    border: none;
    color: ;
}

.event-type-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.event-type-icon {
    font-size: 24px;
}

.visibility-form {
    display: flex;
    align-items: center;
    gap: 15px;
}

.visibility-options {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.checkbox-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .event-type-item {
        flex-direction: column;
        gap: 15px;
    }
    
    .visibility-form {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.innerHTML = `
        <div class="notification-icon">${type === 'success' ? '✓' : '⚠'}</div>
        <div class="notification-content">
            <div class="notification-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Auto-hide notifications after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const notifications = document.querySelectorAll('.notification.show');
    notifications.forEach(notification => {
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    });
});

// Make function globally available
window.showNotification = showNotification;
</script>

<?php require_once 'includes/superfooter.php'; ?>