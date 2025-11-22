<?php
class PasswordPolicy {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getSystemSettings() {
        $settings = [
            'min_password_length' => '8',
            'require_special_chars' => '1',
            'require_numbers' => '1',
            'require_uppercase' => '1',
            'require_lowercase' => '1',
            'max_login_attempts' => '3',
            'lockout_attempts' => '5'
        ];
        
        $table_check = $this->conn->query("SHOW TABLES LIKE 'system_settings'");
        if ($table_check && $table_check->num_rows > 0) {
            $sql = "SELECT setting_key, setting_value FROM system_settings 
                    WHERE setting_key IN ('min_password_length', 'require_special_chars', 'require_numbers', 'require_uppercase', 'require_lowercase', 'max_login_attempts', 'lockout_attempts')";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
            }
        }
        
        return $settings;
    }
    
    public function validatePassword($password) {
        $settings = $this->getSystemSettings();
        $errors = [];
        
        // Check minimum length
        $minLength = intval($settings['min_password_length']);
        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long";
        }
        
        // Check for lowercase letters
        if ($settings['require_lowercase'] == '1') {
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = "Password must contain at least one lowercase letter";
            }
        }
        
        // Check for uppercase letters
        if ($settings['require_uppercase'] == '1') {
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            }
        }
        
        // Check for numbers
        if ($settings['require_numbers'] == '1') {
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = "Password must contain at least one number";
            }
        }
        
        // Check for special characters
        if ($settings['require_special_chars'] == '1') {
            if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
                $errors[] = "Password must contain at least one special character (!@#$%^&* etc.)";
            }
        }
        
        return $errors;
    }
    
    public function getPasswordRequirements() {
        $settings = $this->getSystemSettings();
        $requirements = [];
        $minLength = intval($settings['min_password_length']);
        
        $requirements[] = "Minimum {$minLength} characters";
        
        if ($settings['require_lowercase'] == '1') {
            $requirements[] = "At least one lowercase letter (a-z)";
        }
        
        if ($settings['require_uppercase'] == '1') {
            $requirements[] = "At least one uppercase letter (A-Z)";
        }
        
        if ($settings['require_numbers'] == '1') {
            $requirements[] = "At least one number (0-9)";
        }
        
        if ($settings['require_special_chars'] == '1') {
            $requirements[] = "At least one special character (!@#$%^&* etc.)";
        }
        
        return $requirements;
    }
    
    public function getLoginAttemptSettings() {
        $settings = $this->getSystemSettings();
        return [
            'max_attempts' => intval($settings['max_login_attempts']),
            'lockout_attempts' => intval($settings['lockout_attempts'])
        ];
    }
}
?>