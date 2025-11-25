<?php
$pageTitle = 'Request Role Change - Orphanfare';
require_once 'includes/header.php';

// Debug table creation
try {
    // First check if table exists
    $checkTable = $pdo->query("SHOW TABLES LIKE 'role_change_requests'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        echo "<div class='alert alert-warning'>Table doesn't exist. Creating it now...</div>";
        
        // Create table with the correct column names that match your existing structure
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS role_change_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            current_role_value VARCHAR(50) NOT NULL,
            requested_role_value VARCHAR(50) NOT NULL,
            request_reason TEXT,
            request_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            reviewed_by_user INT NULL,
            reviewed_at_time TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTableSQL);
        echo "<div class='alert alert-success'>Table created successfully!</div>";
        
        // Now add foreign keys if users table exists
        try {
            $checkUsersTable = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($checkUsersTable->rowCount() > 0) {
                $pdo->exec("ALTER TABLE role_change_requests ADD FOREIGN KEY (user_id) REFERENCES users(id)");
                $pdo->exec("ALTER TABLE role_change_requests ADD FOREIGN KEY (reviewed_by_user) REFERENCES users(id)");
                echo "<div class='alert alert-success'>Foreign keys added successfully!</div>";
            }
        } catch (Exception $fkError) {
            echo "<div class='alert alert-warning'>Note: Foreign keys not added: " . $fkError->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-success'>Table already exists!</div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-error'>Database setup error: " . $e->getMessage() . "</div>";
    $error = "Database setup failed. Please contact administrator.";
}

// Handle role change request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestedRole = $_POST['requested_role'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (!empty($requestedRole)) {
        try {
            // CORRECTED: Use the actual column names from your MySQL table
            $sql = "INSERT INTO role_change_requests (user_id, current_role_value, requested_role_value, request_reason) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $currentUser['id'],
                $currentUser['role'],
                $requestedRole,
                $reason
            ]);
            
            if ($result) {
                $message = "Role change request submitted successfully! It will be reviewed by Super Admin.";
            } else {
                $error = "Failed to submit request. Please try again.";
            }
        } catch (Exception $e) {
            $error = "Error submitting request: " . $e->getMessage();
        }
    } else {
        $error = "Please select a role.";
    }
}

// Get available roles (excluding current role and super_admin)
$availableRoles = ['admin', 'Social Worker', 'Social Welfare Assistant', 'user'];
$availableRoles = array_diff($availableRoles, [$currentUser['role'], 'super_admin']);
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Request Role Change</h1>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-container" style="max-width: 600px;">
        <form method="POST" class="role-request-form">
            <div class="form-group">
                <label class="form-label">Current Role</label>
                <input type="text" class="form-input" value="<?php echo htmlspecialchars($currentUser['role']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Requested Role *</label>
                <select name="requested_role" class="form-select" required>
                    <option value="">Select a role...</option>
                    <?php foreach ($availableRoles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role); ?>">
                            <?php echo htmlspecialchars($role); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Reason for Request</label>
                <textarea name="reason" class="form-textarea" placeholder="Explain why you need this role change..." rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</main>

<style>
.form-container {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 30px;
    border: 1px solid #3a3a3a;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 8px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    font-size: 14px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}
</style>

<?php require_once 'includes/footer.php'; ?>