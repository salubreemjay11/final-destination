<?php
session_start();

// If already logged in and 2FA verified, redirect to appropriate dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['2fa_verified']) && $_SESSION['2fa_verified'] === true) {
        if ($_SESSION['role'] === 'super_admin') {
            header("Location: superadmin/superadmin.php");
        } else {
            header("Location: admin/dashboard.php");
        }
        exit();
    }
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Include PasswordPolicy
require_once 'admin/includes/PasswordPolicy.php';
$passwordPolicy = new PasswordPolicy($conn);

// Include TwoFactorAuth with error handling
$twoFactorAuth = null;
$twoFactorAvailable = false;

// Check system-wide 2FA requirement from system_settings
$systemWide2FA = false;
$systemSettings = [];

$settings_check = $conn->query("SHOW TABLES LIKE 'system_settings'");
if ($settings_check && $settings_check->num_rows > 0) {
    $settings_sql = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key = 'two_factor_auth'";
    $settings_result = $conn->query($settings_sql);
    if ($settings_result && $settings_result->num_rows > 0) {
        $settings_row = $settings_result->fetch_assoc();
        $systemWide2FA = ($settings_row['setting_value'] == '1');
    }
}

try {
    if (file_exists('admin/includes/TwoFactorAuth.php')) {
        require_once 'admin/includes/TwoFactorAuth.php';
        $twoFactorAuth = new TwoFactorAuth($conn);
        $twoFactorAvailable = true;
    }
} catch (Exception $e) {
    error_log("TwoFactorAuth initialization error in login: " . $e->getMessage());
    $twoFactorAvailable = false;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Check if user exists and get their login attempt status
    $sql = "SELECT id, username, email, password, role, status, failed_attempts, last_login, account_locked 
            FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $error = '';
            
            // Check if account is locked
            if ($user['account_locked'] == 1) {
                $lockTime = strtotime($user['last_login']);
                $currentTime = time();
                $lockDuration = 30 * 60; // 30 minutes in seconds
                
                if (($currentTime - $lockTime) < $lockDuration) {
                    $remainingTime = ceil(($lockDuration - ($currentTime - $lockTime)) / 60);
                    $error = "Account temporarily locked due to too many failed attempts. Try again in {$remainingTime} minutes. <a href='contact_admin.php' style='color: #007bff;'>Contact Admin</a>";
                } else {
                    // Auto-unlock after lock duration
                    $unlock_sql = "UPDATE users SET account_locked = 0, failed_attempts = 0 WHERE email = ?";
                    $unlock_stmt = $conn->prepare($unlock_sql);
                    $unlock_stmt->bind_param("s", $email);
                    $unlock_stmt->execute();
                    $unlock_stmt->close();
                }
            }
            
            if (empty($error)) {
                // Get login attempt settings
                $attemptSettings = $passwordPolicy->getLoginAttemptSettings();
                $maxAttempts = $attemptSettings['max_attempts'];
                $lockoutAttempts = $attemptSettings['lockout_attempts'];
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Successful login - reset failed attempts
                    $reset_sql = "UPDATE users SET failed_attempts = 0, account_locked = 0, last_login = NOW() WHERE email = ?";
                    $reset_stmt = $conn->prepare($reset_sql);
                    $reset_stmt->bind_param("s", $email);
                    $reset_stmt->execute();
                    $reset_stmt->close();
                    
                    // Check if user is active
                    if ($user['status'] === 'active') {
                        // Check if 2FA is required (system-wide or user-specific)
                        $twoFactorStatus = null;
                        $requires2FA = $systemWide2FA; // Start with system-wide setting
                        
                        if ($twoFactorAvailable && $twoFactorAuth) {
                            $twoFactorStatus = $twoFactorAuth->get2FAStatus($user['id']);
                            // If system-wide 2FA is enabled OR user has individual 2FA enabled
                            if ($systemWide2FA || ($twoFactorStatus && $twoFactorStatus['two_factor_enabled'] == 1)) {
                                $requires2FA = true;
                            }
                        }
                        
                        if ($requires2FA) {
                            // 2FA is enabled - require verification
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['requires_2fa'] = true;
                            $_SESSION['login_time'] = time();
                            
                            // Record login attempt in audit logs
                            recordAuditLog($conn, $user['id'], 'login_2fa_required', 'User login requires 2FA verification', $_SERVER['REMOTE_ADDR']);
                            
                            // Redirect to 2FA verification page
                            header("Location: admin/login-2fa.php");
                            exit();
                        } else {
                            // 2FA not enabled or not available - proceed with normal login
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['role'] = $user['role'];
                            $_SESSION['logged_in'] = true;
                            $_SESSION['2fa_verified'] = true; // No 2FA required
                            $_SESSION['login_time'] = time();
                            
                            // Record login in audit logs
                            recordAuditLog($conn, $user['id'], 'login', 'User logged into the system', $_SERVER['REMOTE_ADDR']);
                            
                            // Redirect based on role
                            if ($user['role'] === 'super_admin') {
                                header("Location: superadmin/superadmin.php");
                            } else {
                                if (file_exists('admin/dashboard.php')) {
                                    header("Location: admin/dashboard.php");
                                } else {
                                    header("Location: dashboard.php");
                                }
                            }
                            exit();
                        }
                    } else {
                        $error = "Your account is not active. Please contact administrator.";
                    }
                } else {
                    // Failed login - increment attempt counter
                    $newAttempts = $user['failed_attempts'] + 1;
                    $update_sql = "UPDATE users SET failed_attempts = ?, last_login = NOW()";
                    
                    // Check if we need to lock the account
                    if ($newAttempts >= $lockoutAttempts) {
                        $update_sql .= ", account_locked = 1";
                        $error = "Account locked due to too many failed attempts. <a href='contact_admin.php' style='color: #007bff;'>Contact Administrator</a>";
                        
                        // Notify admin about account lockout
                        notifyAdmin($conn, $user['id'], $email, $newAttempts, true);
                    } 
                    // Show contact admin message after 3 attempts
                    elseif ($newAttempts >= $maxAttempts) {
                        $error = "Multiple failed login attempts detected. <a href='contact_admin.php' style='color: #007bff;'>Click here to contact administrator</a>.";
                        
                        // Notify admin about multiple failed attempts
                        notifyAdmin($conn, $user['id'], $email, $newAttempts, false);
                    } else {
                        $remainingAttempts = $maxAttempts - $newAttempts;
                        $error = "Invalid email or password. {$remainingAttempts} attempts remaining. <a href='quick_fix.php' style='color: #007bff;'>Click here to reset password</a>";
                    }
                    
                    $update_sql .= " WHERE email = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("is", $newAttempts, $email);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
        } else {
            $error = "No account found with this email. <a href='quick_fix.php' style='color: #007bff;'>Click here to create one</a>";
        }
        
        $stmt->close();
    } else {
        $error = "Database error: " . $conn->error;
    }
}

// Function to record audit logs
function recordAuditLog($conn, $user_id, $action, $description, $ip_address) {
    $table_check = $conn->query("SHOW TABLES LIKE 'audit_logs'");
    if ($table_check && $table_check->num_rows > 0) {
        $sql = "INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }
}

// Function to notify admin about failed login attempts
function notifyAdmin($conn, $user_id, $email, $attempts, $isLocked) {
    // Get admin users
    $admin_sql = "SELECT id, email FROM users WHERE role IN ('super_admin', 'admin') AND status = 'active'";
    $admin_result = $conn->query($admin_sql);
    
    if ($admin_result && $admin_result->num_rows > 0) {
        while ($admin = $admin_result->fetch_assoc()) {
            // Record notification in database
            $notification_sql = "INSERT INTO admin_notifications (admin_id, user_id, notification_type, title, message, is_read) VALUES (?, ?, ?, ?, ?, 0)";
            $notification_stmt = $conn->prepare($notification_sql);
            
            if ($isLocked) {
                $title = "Account Lockout Alert";
                $message = "User {$email} has been locked out after {$attempts} failed login attempts. Immediate attention required.";
                $type = "account_lockout";
            } else {
                $title = "Multiple Failed Login Attempts";
                $message = "User {$email} has {$attempts} failed login attempts. Consider contacting the user.";
                $type = "failed_attempts";
            }
            
            $notification_stmt->bind_param("iisss", $admin['id'], $user_id, $type, $title, $message);
            $notification_stmt->execute();
            $notification_stmt->close();
            
            // In a real application, you would also send email notifications here
            // sendEmailNotification($admin['email'], $title, $message);
        }
    }
    
    // Also record in audit logs
    recordAuditLog($conn, $user_id, 'security_alert', "Multiple failed login attempts: {$attempts}", $_SERVER['REMOTE_ADDR']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Orphanfare</title>
    <link rel="stylesheet" href="css/common.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #18338c 0%, #2a2a2a 100%);
            padding: 20px;
        }

        .login-card {
            background: #122647;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #3a3a3a;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-image {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .login-logo h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-logo p {
            color: #b8c5ff;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: #b8c5ff;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background-color: #1a1a1a;
            border: 1px solid #3a3a3a;
            border-radius: 6px;
            color: #ffffff;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #2563eb;
        }

        .login-btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }

        .demo-accounts {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
            font-size: 13px;
        }

        .demo-accounts h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
        }

        .demo-accounts ul {
            margin: 0;
            padding-left: 20px;
        }

        .demo-accounts li {
            margin-bottom: 4px;
        }

        .role-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            font-size: 13px;
        }

        .quick-fix-link {
            text-align: center;
            margin-top: 15px;
        }

        .quick-fix-link a {
            color: #b8c5ff;
            text-decoration: none;
            font-size: 14px;
        }

        .quick-fix-link a:hover {
            text-decoration: underline;
        }

        .password-requirements {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .password-requirements h4 {
            color: #b8c5ff;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-requirements li {
            color: #ffffff;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .security-info {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #f59e0b;
            font-size: 12px;
        }

        .security-info h4 {
            color: #f59e0b;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <!-- Logo Image -->
                <img src="img/logo-system.jpg" alt="Orphanfare Logo" class="logo-image">
                <h1>Orphanfare</h1>
                <p>Child Welfare Management System</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Password Requirements -->
            

            <form method="POST" action="login.php" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" name="email" placeholder="Enter your email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">Login to System</button>
            </form>

            <div class="quick-fix-link">
                <a href="contact_admin.php"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.6 17.6 0 0 0 4.168 6.608 17.6 17.6 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.68.68 0 0 0-.58-.122l-2.19.547a1.75 1.75 0 0 1-1.657-.459L5.482 8.062a1.75 1.75 0 0 1-.46-1.657l.548-2.19a.68.68 0 0 0-.122-.58zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                </svg> Contact Administrator</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginBtn = document.getElementById('loginBtn');
            loginBtn.disabled = true;
            loginBtn.textContent = 'Logging in...';
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>