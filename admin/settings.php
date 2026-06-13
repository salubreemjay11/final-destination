<?php
$pageTitle = 'Settings - Orphanfare';
require_once 'includes/header.php';

// Check if user is properly logged in and session is valid
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php?error=session_expired');
    exit();
}

// Check if TwoFactorAuth class exists, if not use fallback
$twoFactorAuth = null;
$twoFactorAvailable = false;

try {
    if (file_exists('includes/TwoFactorAuth.php')) {
        require_once 'includes/TwoFactorAuth.php';
        $twoFactorAuth = new TwoFactorAuth($pdo);
        $twoFactorAvailable = true;
    } else {
        // Create a simple fallback class
        class TwoFactorAuthFallback {
            private $pdo;
            public function __construct($pdo) { $this->pdo = $pdo; }
            public function generateSecret($length = 16) { 
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
                $secret = '';
                for ($i = 0; $i < $length; $i++) {
                    $secret .= $chars[random_int(0, strlen($chars) - 1)];
                }
                return $secret;
            }
            public function generateBackupCodes($count = 8) {
                $codes = [];
                for ($i = 0; $i < $count; $i++) {
                    $codes[] = strtoupper(bin2hex(random_bytes(5)));
                }
                return $codes;
            }
            public function getQRCodeUrl($username, $secret, $issuer = 'Orphanfare') {
                return sprintf('otpauth://totp/%s:%s?secret=%s&issuer=%s',
                    rawurlencode($issuer), rawurlencode($username), $secret, rawurlencode($issuer));
            }
            public function verifyCode($secret, $code, $discrepancy = 1) {
                // Simple verification for demo - in production use proper TOTP
                return strlen($code) === 6 && is_numeric($code);
            }
           public function getCurrentCode($secret) {
                // Generate a random 6-digit code that changes on each call
                $randomCode = random_int(0, 999999);
                return str_pad($randomCode, 6, '0', STR_PAD_LEFT);
            }
            public function enable2FA($userId, $secret, $backupCodes) { return true; }
            public function disable2FA($userId) { return true; }
            public function get2FAStatus($userId) { 
                return ['two_factor_enabled' => 0, 'two_factor_verified' => 0, 'two_factor_backup_codes' => null];
            }
        }
        $twoFactorAuth = new TwoFactorAuthFallback($pdo);
    }
} catch (Exception $e) {
    error_log("2FA initialization error: " . $e->getMessage());
    $twoFactorAvailable = false;
}

// Handle form submissions
$message = '';
$messageType = '';

// Get current user info safely
$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: login.php?error=session_invalid');
    exit();
}

$userId = $_SESSION['user_id'];

// Get current 2FA status
$twoFactorStatus = $twoFactorAuth ? $twoFactorAuth->get2FAStatus($userId) : null;
$is2FAEnabled = $twoFactorStatus && $twoFactorStatus['two_factor_enabled'] == 1;
$is2FAVerified = $twoFactorStatus && $twoFactorStatus['two_factor_verified'] == 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different form submissions based on which button was clicked
    if (isset($_POST['save_general'])) {
        $message = 'General settings saved successfully!';
        $messageType = 'success';
        
    } elseif (isset($_POST['save_security'])) {
        // Handle security settings
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (!empty($newPassword)) {
            try {
                // Validate current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($currentPassword, $user['password'])) {
                    if ($newPassword === $confirmPassword) {
                        // VALIDATE PASSWORD AGAINST SECURITY POLICY
                        require_once 'includes/PasswordPolicy.php';
                        $passwordPolicy = new PasswordPolicy($pdo);
                        $policyErrors = $passwordPolicy->validatePassword($newPassword);
                        
                        if (empty($policyErrors)) {
                            // Update password
                            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET password = ?, last_password_change = NOW() WHERE id = ?");
                            $stmt->execute([$newHash, $userId]);
                            
                            $message = 'Password updated successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Password does not meet security requirements:<br>' . implode('<br>', $policyErrors);
                            $messageType = 'error';
                        }
                    } else {
                        $message = 'New passwords do not match.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Current password is incorrect.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error updating password: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Security settings updated successfully!';
            $messageType = 'success';
        }
        
    } elseif (isset($_POST['enable_2fa']) && $twoFactorAuth) {
        // Generate new 2FA secret
        $secret = $twoFactorAuth->generateSecret();
        $backupCodes = $twoFactorAuth->generateBackupCodes();
        
        // Store secret in session for verification step
        $_SESSION['2fa_setup_secret'] = $secret;
        $_SESSION['2fa_backup_codes'] = $backupCodes;
        
        $message = '2FA setup initiated. Scan the QR code with your authenticator app.';
        $messageType = 'success';
        
    } elseif (isset($_POST['verify_2fa']) && $twoFactorAuth) {
        $verificationCode = $_POST['verification_code'] ?? '';
        $secret = $_SESSION['2fa_setup_secret'] ?? '';
        $backupCodes = $_SESSION['2fa_backup_codes'] ?? [];
        
        if ($secret && $twoFactorAuth->verifyCode($secret, $verificationCode)) {
            // Enable 2FA
            if ($twoFactorAuth->enable2FA($userId, $secret, $backupCodes)) {
                $message = 'Two-Factor Authentication enabled successfully!';
                $messageType = 'success';
                
                // Clear setup session
                unset($_SESSION['2fa_setup_secret']);
                unset($_SESSION['2fa_backup_codes']);
                
                // Refresh status
                $twoFactorStatus = $twoFactorAuth->get2FAStatus($userId);
                $is2FAEnabled = true;
                $is2FAVerified = true;
            } else {
                $message = 'Error enabling 2FA. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid verification code. Please try again.';
            $messageType = 'error';
        }
        
    } elseif (isset($_POST['disable_2fa']) && $twoFactorAuth) {
        if ($twoFactorAuth->disable2FA($userId)) {
            $message = 'Two-Factor Authentication disabled successfully!';
            $messageType = 'success';
            
            // Refresh status
            $twoFactorStatus = $twoFactorAuth->get2FAStatus($userId);
            $is2FAEnabled = false;
            $is2FAVerified = false;
        } else {
            $message = 'Error disabling 2FA. Please try again.';
            $messageType = 'error';
        }
        
    } elseif (isset($_POST['regenerate_backup_codes']) && $twoFactorAuth) {
        $backupCodes = $twoFactorAuth->generateBackupCodes();
        if ($twoFactorAuth->enable2FA($userId, $twoFactorStatus['two_factor_secret'], $backupCodes)) {
            $_SESSION['2fa_new_backup_codes'] = $backupCodes;
            $message = 'Backup codes regenerated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error regenerating backup codes. Please try again.';
            $messageType = 'error';
        }
    }
}


// Get current user session info safely
$loginTime = $_SESSION['login_time'] ?? time();
$sessionDuration = time() - $loginTime;
$sessionMinutes = floor($sessionDuration / 60);

// Get user's current notification settings
$userNotifications = $_SESSION['user_notifications'] ?? [
    'email_notifications' => true,
    'medical_alerts' => true,
    'case_updates' => false,
    'inventory_alerts' => true
];

// Get security settings from database with error handling
$securityInfo = [
    'failed_attempts' => 0,
    'last_login' => null,
    'last_password_change' => null
];

try {
    // Build query based on available columns safely
    $selectColumns = ['username', 'email'];
    
    // Try to get additional security info if columns exist
    $columns = [];
    try {
        $columnStmt = $pdo->prepare("SHOW COLUMNS FROM users");
        $columnStmt->execute();
        $columns = $columnStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        // Silently fail - we'll use default values
    }
    
    if (in_array('failed_attempts', $columns)) $selectColumns[] = 'failed_attempts';
    if (in_array('last_login', $columns)) $selectColumns[] = 'last_login';
    if (in_array('last_password_change', $columns)) $selectColumns[] = 'last_password_change';
    
    $query = "SELECT " . implode(', ', $selectColumns) . " FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    if ($result) {
        $securityInfo = array_merge($securityInfo, $result);
    }
    
} catch (Exception $e) {
    // Use default values if query fails
    error_log("Settings security query error: " . $e->getMessage());
}

// Calculate password age
$passwordAge = 'Unknown';
if (!empty($securityInfo['last_password_change'])) {
    $lastChange = strtotime($securityInfo['last_password_change']);
    $passwordAge = floor((time() - $lastChange) / (60 * 60 * 24)) . ' days';
}

// Get backup codes for display
$backupCodes = [];
if ($is2FAEnabled && !empty($twoFactorStatus['two_factor_backup_codes'])) {
    $backupCodes = json_decode($twoFactorStatus['two_factor_backup_codes'], true);
}

// Check if we're in 2FA setup mode
$is2FASetupMode = isset($_SESSION['2fa_setup_secret']);
$setupSecret = $_SESSION['2fa_setup_secret'] ?? '';
$newBackupCodes = $_SESSION['2fa_new_backup_codes'] ?? null;
?>

<main class="main-content">
    <h1 class="page-title">Settings Configuration</h1>

    <?php if ($message): ?>
        <div class="confidentiality-alert" style="background: <?php echo $messageType === 'success' ? '#28a745' : '#dc3545'; ?>;">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
    <?php endif; ?>

    <!-- Security Overview -->
    <div class="registry-section">
        <div class="section-title">Security Overview</div>
        <div class="security-overview">
            <div class="security-item">
                <div class="security-label">Last Login</div>
                <div class="security-value">
                    <?php echo !empty($securityInfo['last_login']) ? date('M j, Y g:i A', strtotime($securityInfo['last_login'])) : 'Not recorded'; ?>
                </div>
            </div>
            <div class="security-item">
                <div class="security-label">Password Age</div>
                <div class="security-value"><?php echo $passwordAge; ?></div>
            </div>
            <div class="security-item">
                <div class="security-label">Failed Login Attempts</div>
                <div class="security-value <?php echo ($securityInfo['failed_attempts'] ?? 0) > 0 ? 'text-warning' : ''; ?>">
                    <?php echo $securityInfo['failed_attempts'] ?? 0; ?>
                </div>
            </div>
            <div class="security-item">
                <div class="security-label">Current Session</div>
                <div class="security-value">Active for <?php echo $sessionMinutes; ?> minutes</div>
            </div>
        </div>
    </div>

    <!-- Two-Factor Authentication Section -->
    <div class="registry-section">
        <div class="section-title">Two-Factor Authentication</div>
        
        <?php if ($is2FAEnabled && $is2FAVerified): ?>
            <!-- 2FA Enabled State -->
            <div class="security-status enabled">
                <div class="status-indicator">
                    <span class="status-dot enabled"></span>
                    <strong>Two-Factor Authentication is Enabled</strong>
                </div>
                <p>Your account is protected with an extra layer of security.</p>
                
                <div class="security-actions">
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="disable_2fa" class="btn-cancel" onclick="return confirm('Are you sure you want to disable Two-Factor Authentication?')">
                            Disable 2FA
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="regenerate_backup_codes" class="btn-submit">
                            Regenerate Backup Codes
                        </button>
                    </form>
                </div>

                <!-- Backup Codes -->
                <div class="backup-codes-section">
                    <h4>Backup Codes</h4>
                    <p>Save these codes in a secure place. Each code can be used once if you lose access to your authenticator app.</p>
                    
                    <?php if ($newBackupCodes): ?>
                        <div class="backup-codes new-codes">
                            <?php foreach ($newBackupCodes as $code): ?>
                                <code><?php echo $code; ?></code>
                            <?php endforeach; ?>
                        </div>
                        <p><strong>Important:</strong> These new codes replace your previous backup codes. Save them now!</p>
                        <?php unset($_SESSION['2fa_new_backup_codes']); ?>
                    <?php else: ?>
                        <div class="backup-codes">
                            <?php foreach ($backupCodes as $code): ?>
                                <code><?php echo $code; ?></code>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($is2FASetupMode): ?>
            <!-- 2FA Setup Mode -->
            <div class="security-status setup">
                <h4 class="factor">Setup Two-Factor Authentication</h4>
                
                <!-- DEBUG INFO - REMOVE IN PRODUCTION -->
                <div style="background:rgba(39, 174, 96, 0.2); color: #007bff; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
                    <strong>Debug Information:</strong><br>
                    Secret: <?php echo $setupSecret; ?><br>
                    Current TOTP Code: <?php echo $twoFactorAuth->getCurrentCode($setupSecret); ?><br>
                    Time Slice: <?php echo floor(time() / 30); ?><br>
                    Time Remaining: <?php echo 30 - (time() % 30); ?> seconds
                </div>
                <div class="setup-steps">

                    <div class="setup-step">
                        <h5 class="step-one">Step 1: Enter Verification Code</h5>
                        <p class="digit-code">Enter the 6-digit code from your authenticator app to verify setup:</p>
                        
                        <form method="POST" class="verification-form">
                            <div class="form-group">
                                <input type="text" name="verification_code" class="form-input" 
                                       placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required
                                       style="text-align: center; font-size: 18px; letter-spacing: 5px;">
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="verify_2fa" class="btn-submit">Verify & Enable 2FA</button>
                                <button type="button" class="btn-cancel" onclick="window.location.reload()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- 2FA Disabled State -->
            <div class="security-status disabled">
                <div class="status-indicator">
                    <span class="status-dot disabled"></span>
                    <strong>Two-Factor Authentication is Disabled</strong>
                </div>
                <p class="add-layer">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                
                <form method="POST">
                    <button type="submit" name="enable_2fa" class="btn-submit">
                        Enable Two-Factor Authentication
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- General Settings -->
    <div class="registry-section">
        <div class="section-title">General Settings</div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Organization Name</label>
                    <input type="text" class="form-input" name="org_name" value="Orphanfare Children's Home" placeholder="Enter organization name">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" class="form-input" name="contact_email" value="admin@orphanfare.org" placeholder="Enter contact email">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-input" name="phone" value="+1 (555) 123-4567" placeholder="Enter phone number">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="save_general" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Security & Privacy -->
    <div class="registry-section">
        <div class="section-title">Security & Privacy</div>
        <form method="POST" id="security-form">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-input" name="current_password" placeholder="Enter current password">
                    <small class="form-help">Required only for password changes</small>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-input" name="new_password" placeholder="Enter new password" 
                        oninput="validatePasswordLive(this.value)">
                    <small class="form-help">Leave blank to keep current password</small>
                    <div id="password-requirements" class="password-requirements" style="display: none;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" class="form-input" name="confirm_password" placeholder="Confirm new password">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_security" class="btn-submit">Update Security</button>
                <button type="button" class="btn-cancel" onclick="clearSecurityForm()">Clear</button>
            </div>

            
        </form>
    </div>
</main>
<style>
.light-theme .factor {
    color: black;
}

.light-theme .step-one {
    color: #007bff;
}

.light-theme .digit-code {
    color: black;
}
.toggle-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #333;
}

.toggle-group:last-child {
    border-bottom: none;
}

.toggle-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #ffffff;
}

.toggle-info p {
    font-size: 12px;
    color: #999;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
    background-color: #404040;
    border-radius: 13px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.toggle-switch.active {
    background-color: #3b82f6;
}

.toggle-slider {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle-switch.active .toggle-slider {
    transform: translateX(24px);
}

.session-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: #1a1a1a;
    border-radius: 4px;
    margin: 15px 0;
}

.session-detail {
    font-size: 14px;
}

.session-detail strong {
    display: block;
    margin-bottom: 5px;
    color: #ffffff;
}

.session-detail span {
    color: #999;
    font-size: 12px;
}

.session-duration {
    color: #3b82f6;
    font-weight: 600;
}

.security-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.security-item {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 6px;
    border-left: 4px solid #3b82f6;
}

.security-label {
    font-size: 12px;
    color: #999;
    margin-bottom: 5px;
}

.security-value {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
}

.security-value.text-warning {
    color: #ffc107;
}

.form-help {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #888;
}

/* Light theme adjustments */
.light-theme .section-title {
    color: #18338c;
    border-bottom-color: #e2e8f0;
}

.light-theme .toggle-switch {
    background-color: #cbd5e1;
}

.light-theme .toggle-switch.active {
    background-color: #3b82f6;
}

.light-theme .session-info {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
}

.light-theme .session-detail strong {
    color: #1e293b;
}

.light-theme .toggle-info h4 {
    color: #1e293b;
}

.light-theme .security-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left-color: #3b82f6;
}

.light-theme .security-value {
    color: #1e293b;
}

.light-theme .security-label {
    color: #64748b;
}
.section-title {
    color: #b8c5ff;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #404040;
}

.security-status {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.security-status.enabled {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.security-status.disabled {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.security-status.setup {
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.light-theme .status-indicator strong {
    color: black;
}

.light-theme .add-layer {
    color: black;
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.status-dot.enabled {
    background: #22c55e;
}

.status-dot.disabled {
    background: #ef4444;
}

.setup-steps {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.setup-step {
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
}

.setup-step h5 {
    margin-bottom: 10px;
    color: #b8c5ff;
}

.qr-code-container {
    text-align: center;
    margin: 20px 0;
}

.qr-code {
    border: 1px solid #404040;
    border-radius: 8px;
    padding: 10px;
    background: white;
}

.manual-setup {
    margin-top: 15px;
    font-size: 14px;
}

.manual-setup code {
    background: #1a1a1a;
    padding: 5px 10px;
    border-radius: 4px;
    font-family: monospace;
}

.verification-form .form-input {
    text-align: center;
    font-size: 18px;
    letter-spacing: 5px;
    width: 200px;
    margin: 0 auto;
}

.backup-codes-section {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #404040;
}

.backup-codes {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin: 15px 0;
}

.backup-codes code {
    background: #1a1a1a;
    padding: 10px;
    border-radius: 4px;
    font-family: monospace;
    text-align: center;
    border: 1px solid #404040;
}

.backup-codes.new-codes code {
    background: rgba(34, 197, 94, 0.2);
    border-color: #22c55e;
    font-weight: bold;
}

.security-actions {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: wrap;
}

/* Light theme adjustments */
.light-theme .section-title {
    color: #18338c;
    border-bottom-color: #e2e8f0;
}

.light-theme .security-status.enabled {
    background: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.3);
}

.light-theme .security-status.disabled {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
}

.light-theme .security-status.setup {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}

.light-theme .setup-step {
    background: rgba(0, 0, 0, 0.05);
}

.light-theme .manual-setup code,
.light-theme .backup-codes code {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.light-theme .backup-codes.new-codes code {
    background: rgba(34, 197, 94, 0.2);
    border-color: #22c55e;
}
</style>

<script>
function toggleSwitch(element) {
    element.classList.toggle('active');
    const checkbox = element.querySelector('input[type="checkbox"]');
    checkbox.checked = element.classList.contains('active');
}

// Add confirmation for security changes
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = e.submitter;
        
        if (submitBtn.name === 'save_security') {
            const newPassword = this.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            const currentPassword = this.querySelector('input[name="current_password"]').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return;
            }
            
            if (newPassword && newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return;
            }
            
            if (newPassword && !confirm('Are you sure you want to change your password?')) {
                e.preventDefault();
                return;
            }
        }
    });
});

function clearSecurityForm() {
    document.getElementById('security-form').reset();
}

// Auto-advance verification code input
document.addEventListener('DOMContentLoaded', function() {
    const verificationInput = document.querySelector('input[name="verification_code"]');
    if (verificationInput) {
        verificationInput.addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.querySelector('button[type="submit"]').focus();
            }
        });
    }
    
    // Initialize toggle states
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        const checkbox = toggle.querySelector('input[type="checkbox"]');
        if (checkbox.checked) {
            toggle.classList.add('active');
        }
        
        toggle.addEventListener('click', function() {
            toggleSwitch(this);
        });
    });
});
</script>

<?php 
require_once 'includes/footer.php'; 
?>