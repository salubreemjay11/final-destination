<?php
$pageTitle = 'Add New User - Orphanfare';
require_once 'includes/superheader.php';

// Handle form submission
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $password_hash, $role, $status);
            
            if ($stmt->execute()) {
                $message = "User created successfully!";
                
                // Record in audit logs
                recordAuditLog($conn, $_SESSION['user_id'], 'create', "Created user: $username ($role)", $_SERVER['REMOTE_ADDR']);
                
                // Clear form
                $username = $email = '';
                $role = 'user';
                $status = 'active';
            } else {
                $error = "Error creating user: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<div class="page-active">
    <div class="page-header">
        <div>
            <h1 class="page-title">Add New User</h1>
            <p class="page-subtitle">Create a new user account in the system</p>
        </div>
        <a href="user-management.php" class="btn btn-secondary">← Back to Users</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="add-user.php">
            <div class="form-section">
                <div class="section-title">Basic Information</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-input" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               placeholder="Enter username" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-input" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                               placeholder="Enter email address" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">Security Settings</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-input" name="password" 
                               placeholder="Enter password" required id="password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" class="form-input" name="confirm_password" 
                               placeholder="Confirm password" required id="confirm_password">
                    </div>
                </div>
                
                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul>
                        <li>Minimum 6 characters</li>
                        <li>Should not be easily guessable</li>
                        <li>Consider using a mix of letters, numbers, and symbols</li>
                    </ul>
                    <div id="password-strength" style="margin-top: 8px; font-size: 12px;"></div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">Role & Status</div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">User Role *</label>
                        <select class="form-select" name="role" required>
                            <option value="user" <?php echo ($_POST['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Social Worker" <?php echo ($_POST['role'] ?? '') === 'Social Worker' ? 'selected' : ''; ?>>Social Worker</option>
                            <option value="Social Welfare Assistant" <?php echo ($_POST['role'] ?? '') === 'Social Welfare Assistant' ? 'selected' : ''; ?>>Social Welfare Assistant</option>
                            <option value="super_admin" <?php echo ($_POST['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Status *</label>
                        <select class="form-select" name="status" required>
                            <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="pending" <?php echo ($_POST['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="user-management.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('password-strength');
    
    function checkPasswordStrength(password) {
        let strength = 'Weak';
        let color = '#e74c3c';
        
        if (password.length >= 8) {
            strength = 'Medium';
            color = '#f39c12';
        }
        
        if (password.length >= 12 && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
            strength = 'Strong';
            color = '#27ae60';
        }
        
        strengthIndicator.textContent = `Strength: ${strength}`;
        strengthIndicator.style.color = color;
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm && password !== confirm) {
            confirmInput.style.borderColor = '#e74c3c';
        } else if (confirm) {
            confirmInput.style.borderColor = '#27ae60';
        } else {
            confirmInput.style.borderColor = '#3a3a3a';
        }
    }
    
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });
    
    confirmInput.addEventListener('input', checkPasswordMatch);
});
</script>

<?php require_once 'includes/superfooter.php'; ?>