<?php

$pageTitle = 'System Configuration - Orphanfare';
require_once 'includes/superheader.php';

// Fetch system settings from database
$settings = [
    'organization_name' => 'Orphanfare Children\'s Home',
    'contact_email' => 'admin@Orphanfare.org',
    'phone_number' => '+63 (969) 164-5421',
    'min_password_length' => 8,
    'require_special_chars' => '0',
    'require_numbers' => '0',
    'require_uppercase' => '0',
    'require_lowercase' => '1',
    'superadmin_2fa_required' => '0',
    'session_timeout' => '30 minutes',
    'max_login_attempts' => '3',
    'lockout_attempts' => '5'
];

// Include TwoFactorAuth for superadmin 2FA
$twoFactorAuth = null;
$twoFactorAvailable = false;

try {
    if (file_exists('admin/includes/TwoFactorAuth.php')) {
        require_once 'admin/includes/TwoFactorAuth.php';
        $twoFactorAuth = new TwoFactorAuth($conn);
        $twoFactorAvailable = true;
    }
} catch (Exception $e) {
    error_log("TwoFactorAuth initialization error in system-configuration: " . $e->getMessage());
    $twoFactorAvailable = false;
}

// If TwoFactorAuth is not available, create a simple fallback
if (!$twoFactorAvailable || !$twoFactorAuth) {
    class TwoFactorAuthFallback {
        private $conn;
        public function __construct($connection) {
            $this->conn = $connection;
        }
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
            return sprintf(
                'otpauth://totp/%s:%s?secret=%s&issuer=%s',
                rawurlencode($issuer),
                rawurlencode($username),
                $secret,
                rawurlencode($issuer)
            );
        }
        public function verifyCode($secret, $code, $discrepancy = 1) {
            return strlen($code) === 6 && is_numeric($code);
        }
        public function enable2FA($userId, $secret, $backupCodes) {
            try {
                $backupCodesJson = json_encode($backupCodes);
                $sql = "UPDATE users SET 
                        two_factor_secret = ?, 
                        two_factor_enabled = 1, 
                        two_factor_verified = 1,
                        two_factor_backup_codes = ?
                        WHERE id = ?";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ssi", $secret, $backupCodesJson, $userId);
                $result = $stmt->execute();
                $stmt->close();
                
                return $result;
            } catch (Exception $e) {
                error_log("Error enabling 2FA: " . $e->getMessage());
                return false;
            }
        }
        public function disable2FA($userId) {
            try {
                $sql = "UPDATE users SET 
                        two_factor_secret = NULL, 
                        two_factor_enabled = 0, 
                        two_factor_verified = 0,
                        two_factor_backup_codes = NULL
                        WHERE id = ?";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $result = $stmt->execute();
                $stmt->close();
                
                return $result;
            } catch (Exception $e) {
                error_log("Error disabling 2FA: " . $e->getMessage());
                return false;
            }
        }
        public function get2FAStatus($userId) {
            try {
                $sql = "SELECT two_factor_secret, two_factor_enabled, two_factor_verified, two_factor_backup_codes 
                        FROM users 
                        WHERE id = ?";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                return $row ?: [
                    'two_factor_secret' => null,
                    'two_factor_enabled' => 0,
                    'two_factor_verified' => 0,
                    'two_factor_backup_codes' => null
                ];
            } catch (Exception $e) {
                error_log("Error getting 2FA status: " . $e->getMessage());
                return [
                    'two_factor_secret' => null,
                    'two_factor_enabled' => 0,
                    'two_factor_verified' => 0,
                    'two_factor_backup_codes' => null
                ];
            }
        }
    }
    
    $twoFactorAuth = new TwoFactorAuthFallback($conn);
    $twoFactorAvailable = true;
}

// Try to get settings from database if settings table exists
$table_check = $conn->query("SHOW TABLES LIKE 'system_settings'");
if ($table_check && $table_check->num_rows > 0) {
    $sql = "SELECT setting_key, setting_value FROM system_settings";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save_general'])) {
        // Save general settings
        $org_name = $conn->real_escape_string($_POST['organization_name']);
        $contact_email = $conn->real_escape_string($_POST['contact_email']);
        $phone_number = $conn->real_escape_string($_POST['phone_number']);
        
        // Update settings in database
        updateSetting($conn, 'organization_name', $org_name);
        updateSetting($conn, 'contact_email', $contact_email);
        updateSetting($conn, 'phone_number', $phone_number);
        
        $message = "General settings updated successfully!";
        $messageType = 'success';
        
    } elseif (isset($_POST['save_security'])) {
        // Handle security settings
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (!empty($newPassword)) {
            try {
                // Validate current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user && password_verify($currentPassword, $user['password'])) {
                    if ($newPassword === $confirmPassword) {
                        // Validate against password policy
                        require_once 'admin/includes/PasswordPolicy.php';
                        $passwordPolicy = new PasswordPolicy($conn);
                        $policyErrors = $passwordPolicy->validatePassword($newPassword);
                        
                        if (empty($policyErrors)) {
                            // Update password
                            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE users SET password = ?, last_password_change = NOW() WHERE id = ?");
                            $stmt->bind_param("si", $newHash, $_SESSION['user_id']);
                            $stmt->execute();
                            
                            $message = 'Password updated successfully!';
                            $messageType = 'success';
                        } else {
                            $message = 'Password does not meet requirements:<br>' . implode('<br>', $policyErrors);
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
        
    } elseif (isset($_POST['save_password_policy'])) {
        // Save password policy
        $min_length = intval($_POST['min_password_length'] ?? 8);
        $special_chars = isset($_POST['require_special_chars']) ? '1' : '0';
        $require_numbers = isset($_POST['require_numbers']) ? '1' : '0';
        $require_uppercase = isset($_POST['require_uppercase']) ? '1' : '0';
        $require_lowercase = isset($_POST['require_lowercase']) ? '1' : '0';
        $superadmin_2fa_required = isset($_POST['superadmin_2fa_required']) ? '1' : '0';
        $session_timeout = $conn->real_escape_string($_POST['session_timeout'] ?? '30 minutes');
        $max_login_attempts = intval($_POST['max_login_attempts'] ?? 3);
        $lockout_attempts = intval($_POST['lockout_attempts'] ?? 5);
        
        updateSetting($conn, 'min_password_length', $min_length);
        updateSetting($conn, 'require_special_chars', $special_chars);
        updateSetting($conn, 'require_numbers', $require_numbers);
        updateSetting($conn, 'require_uppercase', $require_uppercase);
        updateSetting($conn, 'require_lowercase', $require_lowercase);
        updateSetting($conn, 'superadmin_2fa_required', $superadmin_2fa_required);
        updateSetting($conn, 'session_timeout', $session_timeout);
        updateSetting($conn, 'max_login_attempts', $max_login_attempts);
        updateSetting($conn, 'lockout_attempts', $lockout_attempts);
        
        $message = "Security policy updated successfully!";
        $messageType = 'success';

    } elseif (isset($_POST['enable_superadmin_2fa']) && $twoFactorAuth) {
        // Generate new 2FA secret for superadmin
        $secret = $twoFactorAuth->generateSecret();
        $backupCodes = $twoFactorAuth->generateBackupCodes();
        
        // Store secret in session for verification step
        $_SESSION['superadmin_2fa_setup_secret'] = $secret;
        $_SESSION['superadmin_2fa_backup_codes'] = $backupCodes;
        
        $messageType = 'success';
        
    } elseif (isset($_POST['verify_superadmin_2fa']) && $twoFactorAuth) {
        $verificationCode = $_POST['superadmin_verification_code'] ?? '';
        $secret = $_SESSION['superadmin_2fa_setup_secret'] ?? '';
        $backupCodes = $_SESSION['superadmin_2fa_backup_codes'] ?? [];
        
        if ($secret && $twoFactorAuth->verifyCode($secret, $verificationCode)) {
            // Enable 2FA for superadmin
            if ($twoFactorAuth->enable2FA($_SESSION['user_id'], $secret, $backupCodes)) {
                $message = 'Two-Factor Authentication enabled successfully for your account!';
                $messageType = 'success';
                
                // Clear setup session
                unset($_SESSION['superadmin_2fa_setup_secret']);
                unset($_SESSION['superadmin_2fa_backup_codes']);
            } else {
                $message = 'Error enabling 2FA. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid verification code. Please try again.';
            $messageType = 'error';
        }
        
    } elseif (isset($_POST['disable_superadmin_2fa']) && $twoFactorAuth) {
        // Check if system requires superadmin 2FA
        $superadmin2FARequired = ($settings['superadmin_2fa_required'] ?? '0') == '1';
        
        if ($superadmin2FARequired) {
            $message = 'Cannot disable 2FA. System policy requires all Super Admin accounts to have Two-Factor Authentication enabled.';
            $messageType = 'error';
        } else {
            if ($twoFactorAuth->disable2FA($_SESSION['user_id'])) {
                $message = 'Two-Factor Authentication disabled successfully for your account!';
                $messageType = 'success';
            } else {
                $message = 'Error disabling 2FA. Please try again.';
                $messageType = 'error';
            }
        }
        
    } elseif (isset($_POST['regenerate_superadmin_backup_codes']) && $twoFactorAuth) {
        $backupCodes = $twoFactorAuth->generateBackupCodes();
        $twoFactorStatus = $twoFactorAuth->get2FAStatus($_SESSION['user_id']);
        if ($twoFactorAuth->enable2FA($_SESSION['user_id'], $twoFactorStatus['two_factor_secret'], $backupCodes)) {
            $_SESSION['superadmin_2fa_new_backup_codes'] = $backupCodes;
            $message = 'Backup codes regenerated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error regenerating backup codes. Please try again.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['download_data'])) {
        // TEST: Simple file download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="test_download.txt"');
        echo "This is a test download.\n";
        echo "If you can see this, the download works.\n";
        echo "Time: " . date('Y-m-d H:i:s');
        exit;
    }  elseif (isset($_POST['print_sql_dump']) && $_SESSION['role'] === 'superadmin') {
        // Generate printable document
        generatePrintableDocument($conn);

    } elseif (isset($_POST['download_system_backup']) && $_SESSION['role'] === 'superadmin') {
        // Generate system backup
        $systemData = generateSystemBackup($conn);
        $jsonData = json_encode($systemData, JSON_PRETTY_PRINT);
        $filename = 'orphanfare_system_backup_' . date('Y-m-d_His') . '.json';
        
        downloadJsonFile($jsonData, $filename);
    }  
}

// Function to update settings in database
function updateSetting($conn, $key, $value) {
    // Check if setting exists
    $check_sql = "SELECT COUNT(*) as count FROM system_settings WHERE setting_key = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row['count'] > 0) {
        // Update existing setting
        $sql = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $value, $key);
    } else {
        // Insert new setting
        $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $key, $value);
    }
    
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        // If there's still a duplicate key error, force update
        if ($e->getCode() == 1062) {
            $sql = "UPDATE system_settings SET setting_value = ? WHERE setting_key = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
        } else {
            throw $e;
        }
    }
    
    $stmt->close();
}

// Function to generate SQL dump
// Function to generate SQL dump as Word document
// Test function - replace your generateSqlDump with this temporarily
// In your generateSqlDump function, modify the HTML output to include print functionality
function generatePrintableDocument($conn) {
    // Get all tables
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];
    
    if ($tables_result && $tables_result->num_rows > 0) {
        while ($row = $tables_result->fetch_array()) {
            $tables[] = $row[0];
        }
    }
    
    // Start output buffering to capture the print content
    ob_start();
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Orphanfare Database Documentation</title>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-content, .print-content * {
                visibility: visible;
            }
            .print-content {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        .print-content {
            display: none;
            background: white;
            color: black;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .print-content h1 { 
            color: #2c3e50; 
            border-bottom: 2px solid #3498db; 
            padding-bottom: 10px; 
            text-align: center;
        }
        .print-content h2 { 
            color: #34495e; 
            background: #ecf0f1; 
            padding: 8px; 
            margin-top: 30px; 
        }
        .print-content h3 { 
            color: #16a085; 
            margin-top: 20px; 
        }
        .print-content table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0; 
            border: 1px solid #ddd; 
            font-size: 12px;
        }
        .print-content th { 
            background: #3498db; 
            color: white; 
            padding: 10px; 
            text-align: left; 
            border: 1px solid #ddd; 
        }
        .print-content td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            vertical-align: top;
        }
        .print-content .sql-code { 
            background: #f8f9fa; 
            border: 1px solid #dee2e6; 
            padding: 10px; 
            margin: 10px 0; 
            font-family: "Courier New", monospace; 
            font-size: 11px;
            white-space: pre-wrap;
        }
        .print-content .timestamp { 
            color: #7f8c8d; 
            font-style: italic; 
            margin-bottom: 20px;
            text-align: center;
        }
        .print-content .section { 
            margin-bottom: 30px; 
            page-break-inside: avoid; 
        }
    </style>
</head>
<body>
    <div class="print-content">
        <h1>Orphanfare Database Documentation</h1>
        <div class="timestamp">
            Generated on: <?php echo date('F j, Y \a\t g:i A'); ?> | 
            Database: <?php echo $conn->query("SELECT DATABASE()")->fetch_row()[0] ?? 'Unknown'; ?> | 
            PHP: <?php echo phpversion(); ?> | MySQL: <?php echo $conn->server_info ?? 'Unknown'; ?>
        </div>

        <?php if (count($tables) > 0): ?>
            <div class="section">
                <h2>Database Summary</h2>
                <p><strong>Total Tables:</strong> <?php echo count($tables); ?></p>
                
                <h3>Table List</h3>
                <ul>
                    <?php foreach ($tables as $table): ?>
                        <li><?php echo htmlspecialchars($table); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php foreach ($tables as $table): ?>
                <div class="section">
                    <h2>Table: <?php echo htmlspecialchars($table); ?></h2>
                    
                    <h3>Table Structure</h3>
                    <?php
                    $create_result = $conn->query("SHOW CREATE TABLE `$table`");
                    if ($create_result && $create_row = $create_result->fetch_assoc()): ?>
                        <div class="sql-code"><?php echo htmlspecialchars($create_row['Create Table']); ?>;</div>
                    <?php endif; ?>
                    
                    <h3>Columns Information</h3>
                    <?php
                    $columns_result = $conn->query("DESCRIBE `$table`");
                    if ($columns_result && $columns_result->num_rows > 0): ?>
                        <table>
                            <tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>
                            <?php while ($col = $columns_result->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($col['Field']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($col['Type']); ?></td>
                                    <td><?php echo htmlspecialchars($col['Null']); ?></td>
                                    <td><?php echo htmlspecialchars($col['Key']); ?></td>
                                    <td><?php echo ($col['Default'] === null) ? 'NULL' : htmlspecialchars($col['Default']); ?></td>
                                    <td><?php echo htmlspecialchars($col['Extra']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php endif; ?>
                    
                    <h3>Data Records</h3>
                    <?php
                    $data_result = $conn->query("SELECT * FROM `$table` LIMIT 5");
                    if ($data_result && $data_result->num_rows > 0):
                        $total_count_result = $conn->query("SELECT COUNT(*) as total FROM `$table`");
                        $total_count = $total_count_result ? $total_count_result->fetch_assoc()['total'] : 0;
                        ?>
                        <p><strong>Total Records:</strong> <?php echo $total_count; ?></p>
                        <p><strong>Sample Data (first 5 records):</strong></p>
                        
                        <?php
                        $data_result->data_seek(0);
                        $first_row = $data_result->fetch_assoc();
                        $columns = array_keys($first_row);
                        ?>
                        <table>
                            <tr>
                                <?php foreach ($columns as $col): ?>
                                    <th><?php echo htmlspecialchars($col); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            
                            <?php
                            $data_result->data_seek(0);
                            while ($row = $data_result->fetch_assoc()): ?>
                                <tr>
                                    <?php foreach ($row as $value): ?>
                                        <td>
                                            <?php 
                                            $display_value = $value === null ? 'NULL' : htmlspecialchars(substr($value, 0, 100));
                                            if (strlen($value) > 100) {
                                                $display_value .= '...';
                                            }
                                            echo $display_value;
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php else: ?>
                        <p><em>Table is empty</em></p>
                    <?php endif; ?>
                </div>
                <div style="border-bottom: 1px solid #eee; margin: 20px 0;"></div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tables found in database.</p>
        <?php endif; ?>
    </div>

    <script>
        // Show and print the content
        document.addEventListener('DOMContentLoaded', function() {
            var printContent = document.querySelector('.print-content');
            if (printContent) {
                printContent.style.display = 'block';
                window.print();
                
                // Go back to the previous page after printing
                setTimeout(function() {
                    window.history.back();
                }, 100);
            }
        });
    </script>
</body>
</html>
    <?php
    $print_content = ob_get_clean();
    echo $print_content;
    exit;
}

// Function to generate system backup
function generateSystemBackup($conn) {
    $backupData = [
        'export_info' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'export_type' => 'system_backup',
            'warning' => 'CONFIDENTIAL: This file contains sensitive system data'
        ],
        'users' => [],
        'system_settings' => [],
        'tables_structure' => []
    ];

    try {
        // Get all users (without passwords)
        $usersStmt = $conn->prepare("
            SELECT id, username, email, role, created_at, last_login, 
                   two_factor_enabled, failed_attempts, status
            FROM users 
            ORDER BY created_at DESC
        ");
        $usersStmt->execute();
        $usersResult = $usersStmt->get_result();
        
        while ($usersResult && $row = $usersResult->fetch_assoc()) {
            $backupData['users'][] = $row;
        }
        $usersStmt->close();

        // Get all system settings
        $settingsStmt = $conn->prepare("SELECT setting_key, setting_value FROM system_settings");
        $settingsStmt->execute();
        $settingsResult = $settingsStmt->get_result();
        
        while ($settingsResult && $row = $settingsResult->fetch_assoc()) {
            $backupData['system_settings'][$row['setting_key']] = $row['setting_value'];
        }
        $settingsStmt->close();

        // Get database structure info
        $tablesResult = $conn->query("SHOW TABLES");
        while ($tablesResult && $row = $tablesResult->fetch_array()) {
            $tableName = $row[0];
            $backupData['tables_structure'][$tableName] = [
                'row_count' => 0,
                'columns' => []
            ];

            // Get row count
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
            if ($countResult) {
                $countRow = $countResult->fetch_assoc();
                $backupData['tables_structure'][$tableName]['row_count'] = $countRow['count'];
            }

            // Get column info
            $columnsResult = $conn->query("DESCRIBE `$tableName`");
            while ($columnsResult && $colRow = $columnsResult->fetch_assoc()) {
                $backupData['tables_structure'][$tableName]['columns'][] = $colRow;
            }
        }

    } catch (Exception $e) {
        error_log("Error generating system backup: " . $e->getMessage());
    }

    return $backupData;
}

// Function to force file download
function downloadJsonFile($data, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($data));
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $data;
    exit;
}

// Get current user session info
$loginTime = $_SESSION['login_time'] ?? time();
$sessionDuration = time() - $loginTime;
$sessionMinutes = floor($sessionDuration / 60);

// Get security settings from database
$securityInfo = [
    'failed_attempts' => 0,
    'last_login' => null,
    'last_password_change' => null
];

try {
    $selectColumns = ['username', 'email'];
    $columns = [];
    
    try {
        $columnStmt = $conn->prepare("SHOW COLUMNS FROM users");
        $columnStmt->execute();
        $result = $columnStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    } catch (Exception $e) {
        // Silently fail
    }
    
    if (in_array('failed_attempts', $columns)) $selectColumns[] = 'failed_attempts';
    if (in_array('last_login', $columns)) $selectColumns[] = 'last_login';
    if (in_array('last_password_change', $columns)) $selectColumns[] = 'last_password_change';
    
    $query = "SELECT " . implode(', ', $selectColumns) . " FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    if ($userData) {
        $securityInfo = array_merge($securityInfo, $userData);
    }
    
} catch (Exception $e) {
    error_log("Settings security query error: " . $e->getMessage());
}

// Calculate password age
$passwordAge = 'Unknown';
if (!empty($securityInfo['last_password_change'])) {
    $lastChange = strtotime($securityInfo['last_password_change']);
    $passwordAge = floor((time() - $lastChange) / (60 * 60 * 24)) . ' days';
}

// Get current 2FA status for superadmin
$twoFactorStatus = $twoFactorAuth ? $twoFactorAuth->get2FAStatus($_SESSION['user_id']) : null;
$is2FAEnabled = $twoFactorStatus && $twoFactorStatus['two_factor_enabled'] == 1;
$is2FAVerified = $twoFactorStatus && $twoFactorStatus['two_factor_verified'] == 1;

// Get backup codes for display
$backupCodes = [];
if ($is2FAEnabled && !empty($twoFactorStatus['two_factor_backup_codes'])) {
    $backupCodes = json_decode($twoFactorStatus['two_factor_backup_codes'], true);
}

// Check if we're in 2FA setup mode
$is2FASetupMode = isset($_SESSION['superadmin_2fa_setup_secret']);
$setupSecret = $_SESSION['superadmin_2fa_setup_secret'] ?? '';
$newBackupCodes = $_SESSION['superadmin_2fa_new_backup_codes'] ?? null;

// Check if superadmin 2FA is required by system policy
$superadmin2FARequired = ($settings['superadmin_2fa_required'] ?? '0') == '1';
?>

<div class="page-active">
    <div class="page-header">
        <h1 class="page-title">System Configuration</h1>
    </div>

    <?php if (isset($message)): ?>
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

    <!-- Super Admin Two-Factor Authentication Section -->
    <div class="registry-section">
        <div class="section-title">Super Admin Two-Factor Authentication</div>
        
        <?php if ($superadmin2FARequired): ?>
            <div class="enable-warning">
                <h5>🔒 System Policy: 2FA Required</h5>
                <p>System policy requires all Super Admin accounts to have Two-Factor Authentication enabled for enhanced security.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($is2FAEnabled && $is2FAVerified): ?>
            <!-- 2FA Enabled State -->
            <div class="security-status enabled">
                <div class="status-indicator">
                    <span class="status-dot enabled"></span>
                    <strong class="enabled">Two-Factor Authentication is Enabled</strong>
                </div>
                <p class="description">Your superadmin account is protected with an extra layer of security.</p>
                
                <div class="security-actions">
                    <?php if (!$superadmin2FARequired): ?>
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="disable_superadmin_2fa" class="btn-cancel" onclick="return confirm('Are you sure you want to disable Two-Factor Authentication?')">
                                Disable 2FA
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display: inline;">
                        <button type="submit" name="regenerate_superadmin_backup_codes" class="btn-submit">
                            Regenerate Backup Codes
                        </button>
                    </form>
                </div>

                <!-- Backup Codes -->
                <div class="backup-codes-section">
                    <h4 class="enabled">Backup Codes</h4>
                    <p class="description">Save these codes in a secure place. Each code can be used once if you lose access to your authenticator app.</p>
                    
                    <?php if ($newBackupCodes): ?>
                        <div class="backup-codes new-codes">
                            <?php foreach ($newBackupCodes as $code): ?>
                                <code><?php echo $code; ?></code>
                            <?php endforeach; ?>
                        </div>
                        <p><strong>Important:</strong> These new codes replace your previous backup codes. Save them now!</p>
                        <?php unset($_SESSION['superadmin_2fa_new_backup_codes']); ?>
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
                <h4 class="setup">Setup Two-Factor Authentication</h4>
                
                <div class="setup-steps">
                    <div class="setup-step">
                        <p class="manual-setup">Or enter this code manually: <code><?php echo chunk_split($setupSecret, 4, ' '); ?></code></p>
                    </div>

                    <div class="setup-step">
                        <h5>Step 2: Enter Verification Code</h5>
                        <p>Enter the 6-digit code from your authenticator app to verify setup:</p>
                        
                        <form method="POST" class="verification-form">
                            <div class="form-group">
                                <input type="text" name="superadmin_verification_code" class="form-input" 
                                       placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}" required
                                       style="text-align: center; font-size: 18px; letter-spacing: 5px;">
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="verify_superadmin_2fa" class="btn-submit">Verify & Enable 2FA</button>
                                
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
                    <strong class="two-factor">Two-Factor Authentication is Disabled</strong>
                </div>
                <p class="add-extra">Add an extra layer of security to your superadmin account by enabling two-factor authentication.</p>
                
                <?php if ($superadmin2FARequired): ?>
                    <div class="enable-warning">
                        <p><strong>Required:</strong> You must enable 2FA to comply with system security policy.</p>
                    </div>
                <?php endif; ?>
                
                <?php if ($twoFactorAvailable && $twoFactorAuth): ?>
                    <form method="POST">
                        <button type="submit" name="enable_superadmin_2fa" class="btn-submit">
                            Enable Two-Factor Authentication
                        </button>
                    </form>
                <?php else: ?>
                    <div class="enable-warning" style="background: rgba(239, 68, 68, 0.1);">
                        <p><strong>Error:</strong> Two-Factor Authentication system is not available. Please check if the TwoFactorAuth class exists in admin/includes/TwoFactorAuth.php</p>
                    </div>
                <?php endif; ?>
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
                    <input type="text" class="form-input" name="organization_name" value="<?php echo htmlspecialchars($settings['organization_name']); ?>" placeholder="Enter organization name">
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" class="form-input" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email']); ?>" placeholder="Enter contact email">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-input" name="phone_number" value="<?php echo htmlspecialchars($settings['phone_number']); ?>" placeholder="Enter phone number">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_general" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- Security Policy -->
    <div class="registry-section">
        <div class="section-title">Security Policy</div>
        <form method="POST" id="password-policy-form">

            <div class="button-group">
                <div class="button-info">
                    <h4>Require Special Characters</h4>
                    <p>Passwords must include special characters (!@#$%^&* etc.)</p>
                </div>
                <div class="button-controls">
                    <button type="button" class="btn-toggle <?php echo $settings['require_special_chars'] == '1' ? 'active' : ''; ?>" 
                            onclick="toggleButton(this, 'require_special_chars')">
                        <?php echo $settings['require_special_chars'] == '1' ? 'Enabled' : 'Disabled'; ?>
                    </button>
                    <input type="hidden" name="require_special_chars" value="<?php echo $settings['require_special_chars']; ?>">
                </div>
            </div>

            <div class="button-group">
                <div class="button-info">
                    <h4>Require Numbers</h4>
                    <p>Passwords must include at least one number (0-9)</p>
                </div>
                <div class="button-controls">
                    <button type="button" class="btn-toggle <?php echo $settings['require_numbers'] == '1' ? 'active' : ''; ?>" 
                            onclick="toggleButton(this, 'require_numbers')">
                        <?php echo $settings['require_numbers'] == '1' ? 'Enabled' : 'Disabled'; ?>
                    </button>
                    <input type="hidden" name="require_numbers" value="<?php echo $settings['require_numbers']; ?>">
                </div>
            </div>

            <div class="button-group">
                <div class="button-info">
                    <h4>Require Uppercase Letters</h4>
                    <p>Passwords must include at least one uppercase letter (A-Z)</p>
                </div>
                <div class="button-controls">
                    <button type="button" class="btn-toggle <?php echo ($settings['require_uppercase'] ?? '0') == '1' ? 'active' : ''; ?>" 
                            onclick="toggleButton(this, 'require_uppercase')">
                        <?php echo ($settings['require_uppercase'] ?? '0') == '1' ? 'Enabled' : 'Disabled'; ?>
                    </button>
                    <input type="hidden" name="require_uppercase" value="<?php echo $settings['require_uppercase'] ?? '0'; ?>">
                </div>
            </div>

            <div class="button-group">
                <div class="button-info">
                    <h4>Require Lowercase Letters</h4>
                    <p>Passwords must include at least one lowercase letter (a-z)</p>
                </div>
                <div class="button-controls">
                    <button type="button" class="btn-toggle <?php echo ($settings['require_lowercase'] ?? '1') == '1' ? 'active' : ''; ?>" 
                            onclick="toggleButton(this, 'require_lowercase')">
                        <?php echo ($settings['require_lowercase'] ?? '1') == '1' ? 'Enabled' : 'Disabled'; ?>
                    </button>
                    <input type="hidden" name="require_lowercase" value="<?php echo $settings['require_lowercase'] ?? '1'; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Max Login Attempts Before Warning</label>
                <input type="number" class="form-input" name="max_login_attempts" 
                    value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? '3'); ?>" 
                    min="1" max="10" required>
                <small class="form-help">Show contact admin message after this many failed attempts</small>
            </div>

            <div class="form-group">
                <label class="form-label">Lockout After Attempts</label>
                <input type="number" class="form-input" name="lockout_attempts" 
                    value="<?php echo htmlspecialchars($settings['lockout_attempts'] ?? '5'); ?>" 
                    min="1" max="10" required>
                <small class="form-help">Lock account and notify admin after this many failed attempts</small>
            </div>
            <div class="form-group">
                <label class="form-label">Minimum Password Length</label>
                <input type="number" class="form-input" name="min_password_length" 
                       value="<?php echo htmlspecialchars($settings['min_password_length']); ?>" 
                       min="6" max="20" required>
                <small class="form-help">Minimum number of characters required (6-20)</small>
            </div>

            <div class="form-actions">
                <button type="submit" name="save_password_policy" class="btn-submit">Save Security Policy</button>
            </div>

        </form>
    </div>

        <!-- Policy Preview -->
        <div class="registry-section">
            <div class="section-title">Current Policy Preview</div>
            <div class="policy-preview">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>Minimum <?php echo htmlspecialchars($settings['min_password_length']); ?> characters</li>
                    <?php if (($settings['require_special_chars'] ?? '0') == '1'): ?>
                        <li>At least one special character (!@#$%^&* etc.)</li>
                    <?php endif; ?>
                    <?php if (($settings['require_numbers'] ?? '0') == '1'): ?>
                        <li>At least one number (0-9)</li>
                    <?php endif; ?>
                    <?php if (($settings['require_uppercase'] ?? '0') == '1'): ?>
                        <li>At least one uppercase letter (A-Z)</li>
                    <?php endif; ?>
                    <?php if (($settings['require_lowercase'] ?? '1') == '1'): ?>
                        <li>At least one lowercase letter (a-z)</li>
                    <?php endif; ?>
                </ul>
                
                <h4>Security Settings:</h4>
                <ul>
                    <li>Super Admin 2FA: <strong><?php echo ($settings['superadmin_2fa_required'] ?? '0') == '1' ? 'REQUIRED' : 'Optional'; ?></strong></li>
                    <li>Session Timeout: <strong><?php echo htmlspecialchars($settings['session_timeout'] ?? '30 minutes'); ?></strong></li>
                    <li>Max Login Attempts: <strong><?php echo htmlspecialchars($settings['max_login_attempts'] ?? '3'); ?></strong></li>
                    <li>Lockout After: <strong><?php echo htmlspecialchars($settings['lockout_attempts'] ?? '5'); ?> attempts</strong></li>
                </ul>
            </div>
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
</div>

<style>
.light-theme .enabled {
    color: black;
}
.two-factor {
    color: #ff6b6b;
}

.add-extra {
    color: #007bff;
    margin-bottom: 15px;
}

/* Data Management Styles */
.download-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.download-option {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
}

.download-option h4 {
    color: #b8c5ff;
    margin-bottom: 8px;
    font-size: 16px;
}

.download-option p {
    color: #999;
    font-size: 14px;
    margin-bottom: 15px;
}

.download-meta {
    background: #2a2a2a;
    padding: 10px 15px;
    border-radius: 6px;
    margin-top: 10px;
    font-size: 12px;
    color: #888;
}

.download-meta strong {
    color: #b8c5ff;
}

/* Light theme adjustments */
.light-theme .download-option {
    background: #f8fafc;
    border-color: #e2e8f0;
}

.light-theme .download-option h4 {
    color: #18338c;
}

.light-theme .download-meta {
    background: #e2e8f0;
}

.registry-section {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid #3a3a3a;
}

.section-title {
    color: #b8c5ff;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #404040;
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
}

.form-input, .form-select {
    width: 100%;
    padding: 10px 12px;
    background-color: #1a1a1a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.form-input:focus, .form-select:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-help {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #888;
}

.button-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #333;
}

.button-group:last-child {
    border-bottom: none;
}

.button-info h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #ffffff;
}

.button-info p {
    font-size: 12px;
    color: #999;
}

.button-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-toggle {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    min-width: 80px;
    transition: all 0.3s;
}

.btn-toggle.active {
    background: #28a745;
}

.btn-toggle:hover {
    opacity: 0.9;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-submit {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit:hover {
    background: #2563eb;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel:hover {
    background: #5a6268;
}

.confidentiality-alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: white;
    font-weight: 500;
}

.policy-preview {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

.policy-preview h4 {
    color: #b8c5ff;
    margin-bottom: 10px;
    font-size: 16px;
}

.policy-preview ul {
    margin-left: 20px;
    margin-bottom: 20px;
}

.policy-preview li {
    color: #ffffff;
    margin-bottom: 5px;
    font-size: 14px;
}

.text-success {
    color: #22c55e !important;
}

.text-warning {
    color: #f59e0b !important;
}

.security-value.text-success,
.security-value.text-warning {
    font-weight: 600;
}

.password-requirements {
    background: #1a1a1a;
    padding: 10px;
    border-radius: 6px;
    margin-top: 8px;
    border-left: 3px solid #3b82f6;
    font-size: 12px;
    line-height: 1.4;
}

.light-theme .password-requirements {
    background: #f8fafc;
    border-left-color: #3b82f6;
}

.system-status-info {
    margin-top: 15px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
}

.system-status-info h4 {
    margin-bottom: 10px;
    color: #b8c5ff;
}

.system-status-info ul {
    margin-left: 20px;
    margin-bottom: 15px;
}

.system-status-info li {
    color: #ffffff;
    margin-bottom: 5px;
    font-size: 14px;
}

.system-stats {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #404040;
}

.system-stats h5 {
    margin-bottom: 15px;
    color: #b8c5ff;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.stat-item {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 6px;
    text-align: center;
    border: 1px solid #3a3a3a;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #3b82f6;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #999;
}

.enable-warning {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
}

.enable-warning h5 {
    color: #f59e0b;
    margin-bottom: 10px;
}

.security-status.enabled .system-status-info {
    border-left: 4px solid #22c55e;
}

.security-status.disabled .system-status-info {
    border-left: 4px solid #6c757d;
}

/* 2FA Specific Styles */
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

.light-theme .setup{
    color: black;
}

.light-theme .manual-setup {
    margin-top: 15px;
    font-size: 14px;
    color: black;
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
    background: rgba(58, 50, 50, 0.05);
}

.light-theme .manual-setup code,
.light-theme .backup-codes code {
    background: #929292ff;
    border-color: #e2e8f0;
}

.light-theme .backup-codes.new-codes code {
    background: rgba(34, 197, 94, 0.2);
    border-color: #22c55e;
}
</style>

<script>
function showDownloadLoading(button, message = 'Preparing Download...') {
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ ' + message;
    button.disabled = true;
    
    // Re-enable after 10 seconds in case download fails
    setTimeout(function() {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 10000);
}

function clearSecurityForm() {
    document.getElementById('security-form').reset();
    document.getElementById('password-requirements').style.display = 'none';
}

// Enhanced button toggle functionality
function toggleButton(button, fieldName) {
    const isActive = button.classList.contains('active');
    const hiddenInput = button.parentNode.querySelector('input[type="hidden"]');
    
    if (fieldName === 'two_factor_auth' && !isActive) {
        if (!confirm('⚠️ WARNING: Enabling system-wide 2FA will require ALL users to set up Two-Factor Authentication on their next login.\n\nThis affects all active users in the system.\n\nAre you sure you want to continue?')) {
            return;
        }
    }
    
    if (isActive) {
        button.classList.remove('active');
        button.textContent = 'Disabled';
        hiddenInput.value = '0';
    } else {
        button.classList.add('active');
        button.textContent = 'Enabled';
        hiddenInput.value = '1';
    }
}

// Live password validation
function validatePasswordLive(password) {
    const requirementsDiv = document.getElementById('password-requirements');
    
    if (password.length === 0) {
        requirementsDiv.style.display = 'none';
        return;
    }
    
    const minLength = parseInt(document.querySelector('input[name="min_password_length"]').value) || 8;
    const requireSpecial = document.querySelector('input[name="require_special_chars"]').value === '1';
    const requireNumbers = document.querySelector('input[name="require_numbers"]').value === '1';
    const requireUppercase = document.querySelector('input[name="require_uppercase"]').value === '1';
    
    const errors = [];
    const successes = [];
    
    if (password.length >= minLength) {
        successes.push(`✓ Minimum ${minLength} characters`);
    } else {
        errors.push(`✗ Minimum ${minLength} characters (currently ${password.length})`);
    }
    
    if (requireSpecial) {
        if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
            successes.push('✓ Special character');
        } else {
            errors.push('✗ Special character required');
        }
    }
    
    if (requireNumbers) {
        if (/[0-9]/.test(password)) {
            successes.push('✓ Number');
        } else {
            errors.push('✗ Number required');
        }
    }
    
    if (requireUppercase) {
        if (/[A-Z]/.test(password)) {
            successes.push('✓ Uppercase letter');
        } else {
            errors.push('✗ Uppercase letter required');
        }
    }
    
    let html = '<strong>Password Requirements:</strong><br>';
    
    if (successes.length > 0) {
        html += '<div style="color: #22c55e;">' + successes.join('<br>') + '</div>';
    }
    
    if (errors.length > 0) {
        html += '<div style="color: #ef4444;">' + errors.join('<br>') + '</div>';
    }
    
    if (errors.length === 0 && successes.length > 0) {
        html = '<div style="color: #22c55e;"><strong>✓ Password meets all requirements</strong></div>';
    }
    
    requirementsDiv.innerHTML = html;
    requirementsDiv.style.display = 'block';
}

function printDatabaseDocumentation() {
    if (confirm('This will print the database documentation. Continue?')) {
        // Create a form to submit
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = 'system-configuration.php';
        
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'print_sql_dump';
        input.value = '1';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
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
            
            if (newPassword && !confirm('Are you sure you want to change your password?')) {
                e.preventDefault();
                return;
            }
        }
        
        // Show loading for download operations
        if (submitBtn.name === 'download_sql_dump' || submitBtn.name === 'download_system_backup') {
            submitBtn.innerHTML = '⏳ Preparing Download...';
            submitBtn.disabled = true;
        }
    });
});

function clearSecurityForm() {
    document.getElementById('security-form').reset();
}

// Button toggle functionality
function toggleButton(button, fieldName) {
    const isActive = button.classList.contains('active');
    const hiddenInput = button.parentNode.querySelector('input[type="hidden"]');
    
    if (fieldName === 'two_factor_auth' && !isActive) {
        // Special confirmation for enabling system-wide 2FA
        if (!confirm('⚠️ WARNING: Enabling system-wide 2FA will require ALL users to set up Two-Factor Authentication on their next login.\n\nThis affects all active users in the system.\n\nAre you sure you want to continue?')) {
            return;
        }
    }
    
    if (isActive) {
        button.classList.remove('active');
        button.textContent = 'Disabled';
        hiddenInput.value = '0';
    } else {
        button.classList.add('active');
        button.textContent = 'Enabled';
        hiddenInput.value = '1';
    }
}

// Live password validation
function validatePasswordLive(password) {
    const requirementsDiv = document.getElementById('password-requirements');
    
    if (password.length === 0) {
        requirementsDiv.style.display = 'none';
        return;
    }
    
    // Get current policy settings from the form
    const minLength = parseInt(document.querySelector('input[name="min_password_length"]').value) || 8;
    const requireSpecial = document.querySelector('input[name="require_special_chars"]').value === '1';
    const requireNumbers = document.querySelector('input[name="require_numbers"]').value === '1';
    const requireUppercase = document.querySelector('input[name="require_uppercase"]').value === '1';
    
    const errors = [];
    const successes = [];
    
    if (password.length >= minLength) {
        successes.push(`✓ Minimum ${minLength} characters`);
    } else {
        errors.push(`✗ Minimum ${minLength} characters (currently ${password.length})`);
    }
    
    if (requireSpecial) {
        if (/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
            successes.push('✓ Special character');
        } else {
            errors.push('✗ Special character required');
        }
    }
    
    if (requireNumbers) {
        if (/[0-9]/.test(password)) {
            successes.push('✓ Number');
        } else {
            errors.push('✗ Number required');
        }
    }
    
    if (requireUppercase) {
        if (/[A-Z]/.test(password)) {
            successes.push('✓ Uppercase letter');
        } else {
            errors.push('✗ Uppercase letter required');
        }
    }
    
    let html = '<strong>Password Requirements:</strong><br>';
    
    if (successes.length > 0) {
        html += '<div style="color: #22c55e;">' + successes.join('<br>') + '</div>';
    }
    
    if (errors.length > 0) {
        html += '<div style="color: #ef4444;">' + errors.join('<br>') + '</div>';
    }
    
    if (errors.length === 0 && successes.length > 0) {
        html = '<div style="color: #22c55e;"><strong>✓ Password meets all requirements</strong></div>';
    }
    
    requirementsDiv.innerHTML = html;
    requirementsDiv.style.display = 'block';
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
            
            if (newPassword && !confirm('Are you sure you want to change your password?')) {
                e.preventDefault();
                return;
            }
        }
    });
});

// Auto-advance verification code input
document.addEventListener('DOMContentLoaded', function() {
    const verificationInput = document.querySelector('input[name="superadmin_verification_code"]');
    if (verificationInput) {
        verificationInput.addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.querySelector('button[type="submit"]').focus();
            }
        });
    }
});
</script>

<?php require_once 'includes/superfooter.php'; ?>