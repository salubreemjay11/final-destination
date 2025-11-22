<?php
$pageTitle = 'Edit User - Orphanfare';
require_once 'includes/superheader.php';

// TEMPORARY FIX: Simple role-based permission check
$allowed_roles = ['super_admin', 'admin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: access-denied.php');
    exit();
}

// Initialize variables
$user = null;
$message = '';
$error = '';

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header('Location: user-management.php?error=' . urlencode('Invalid user ID'));
    exit();
}

// Fetch user data
$sql = "SELECT id, username, email, role, status, created_at, updated_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: user-management.php?error=' . urlencode('User not found'));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_role = $_POST['role'] ?? '';
    $new_status = $_POST['status'] ?? '';
    
    // Validate input
    $valid_roles = ['super_admin', 'admin', 'user', 'Social Worker', 'Social Welfare Assistant'];
    $valid_statuses = ['active', 'inactive', 'pending'];
    
    if (!in_array($new_role, $valid_roles)) {
        $error = "Invalid role selected";
    } elseif (!in_array($new_status, $valid_statuses)) {
        $error = "Invalid status selected";
    } else {
        // Prevent modification of super admin (user ID 1)
        if ($user['id'] == 1 && ($new_role != 'super_admin' || $new_status != 'active')) {
            $error = "Cannot modify the super admin account";
        } else {
            // Update user
            $update_sql = "UPDATE users SET role = ?, status = ?, updated_at = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $new_role, $new_status, $user_id);
            
            if ($update_stmt->execute()) {
                $message = "User updated successfully";
                
                // Log the action
                $audit_sql = "INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent) 
                             VALUES (?, 'update', ?, ?, ?)";
                $audit_stmt = $conn->prepare($audit_sql);
                $description = "Updated user: {$user['username']} (Role: {$user['role']} -> {$new_role}, Status: {$user['status']} -> {$new_status})";
                $audit_stmt->bind_param("isss", $_SESSION['user_id'], $description, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
                $audit_stmt->execute();
                $audit_stmt->close();
                
                // Refresh user data
                $refresh_sql = "SELECT id, username, email, role, status, created_at, updated_at FROM users WHERE id = ?";
                $refresh_stmt = $conn->prepare($refresh_sql);
                $refresh_stmt->bind_param("i", $user_id);
                $refresh_stmt->execute();
                $refresh_result = $refresh_stmt->get_result();
                $user = $refresh_result->fetch_assoc();
                $refresh_stmt->close();
            } else {
                $error = "Error updating user: " . $conn->error;
            }
            $update_stmt->close();
        }
    }
}
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">Edit User</h1>
        <a href="user-management.php" class="btn btn-secondary">← Back to User Management</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <div class="card-header">
            <div class="card-title">User Information</div>
        </div>
        
        <form method="POST" class="user-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-input" readonly>
                    <small class="form-help">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input" readonly>
                    <small class="form-help">Email cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="role">Role *</label>
                    <select id="role" name="role" class="form-input" required>
                        <option value="">Select Role</option>
                        <option value="super_admin" <?php echo $user['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Social Worker" <?php echo $user['role'] === 'Social Worker' ? 'selected' : ''; ?>>Social Worker</option>
                        <option value="Social Welfare Assistant" <?php echo $user['role'] === 'Social Welfare Assistant' ? 'selected' : ''; ?>>Social Welfare Assistant</option>
                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    </select>
                    <small class="form-help">User's access level and permissions</small>
                </div>
                
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-input" required>
                        <option value="">Select Status</option>
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="pending" <?php echo $user['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                    <small class="form-help">User's account status</small>
                </div>
            </div>
            
            <div class="form-group">
                <label>User ID</label>
                <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" class="form-input" readonly>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Created At</label>
                    <input type="text" value="<?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?>" class="form-input" readonly>
                </div>
                
                <div class="form-group">
                    <label>Last Updated</label>
                    <input type="text" value="<?php echo date('Y-m-d H:i:s', strtotime($user['updated_at'])); ?>" class="form-input" readonly>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="user-management.php" class="btn btn-secondary">Cancel</a>
                
                <?php if ($user['id'] != 1): ?>
                    <a href="user-management.php?delete_id=<?php echo $user['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                       style="margin-left: auto;">
                        Delete User
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Role Information Section -->
    <div class="dashboard-card" style="margin-top: 20px;">
        <div class="card-header">
            <div class="card-title">Role Descriptions</div>
        </div>
        
        <div class="role-descriptions">
            <div class="role-item">
                <strong>Super Admin</strong>
                <p>Full system access with all permissions. Can manage all users and system settings.</p>
            </div>
            
            <div class="role-item">
                <strong>Admin</strong>
                <p>Administrative access to user management and system configuration.</p>
            </div>
            
            <div class="role-item">
                <strong>Social Worker</strong>
                <p>Access to child records, case management, and reporting functions.</p>
            </div>
            
            <div class="role-item">
                <strong>Social Welfare Assistant</strong>
                <p>Basic access to view and update assigned cases with limited permissions.</p>
            </div>
            
            <div class="role-item">
                <strong>User</strong>
                <p>Basic access with limited permissions, typically for general staff.</p>
            </div>
        </div>
    </div>
</div>

<style>
.user-form {
    max-width: 800px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
}

.form-input[readonly] {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.form-help {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    transition: background-color 0.3s;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #545b62;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.role-descriptions {
    display: grid;
    gap: 15px;
}

.role-item {
    padding: 15px;
    border-left: 4px solid #007bff;
    background-color: #f8f9fa;
}

.role-item strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.role-item p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-actions .btn {
        margin-bottom: 10px;
        text-align: center;
    }
}
</style>

<?php require_once 'includes/superfooter.php'; ?>