<?php
// test-email.php - Proper email test
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Check if required files exist
    $config_path = '../config/database.php';
    $email_gateway_path = 'includes/email-gateway.php';
    
    if (!file_exists($config_path)) {
        throw new Exception("Config file not found: " . realpath($config_path));
    }
    
    if (!file_exists($email_gateway_path)) {
        throw new Exception("Email gateway not found: " . realpath($email_gateway_path));
    }

    // Include required files
    require_once $config_path;
    require_once $email_gateway_path;

    // Check if class exists
    if (!class_exists('EmailNotification')) {
        throw new Exception("EmailNotification class not found after including files");
    }

    // Test email sending
    $email = new EmailNotification();
    $result = $email->testEmail('salubreemjay@gmail.com');
    
    // Return detailed result
    echo json_encode([
        'success' => $result['success'] > 0,
        'message' => $result['success'] > 0 ? 
            '✅ Email sent successfully! Check your inbox and spam folder.' : 
            '❌ Failed to send email.',
        'details' => $result,
        'debug' => [
            'config_exists' => file_exists($config_path),
            'email_gateway_exists' => file_exists($email_gateway_path),
            'class_exists' => class_exists('EmailNotification')
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '❌ Error: ' . $e->getMessage(),
        'debug' => [
            'config_exists' => file_exists($config_path ?? ''),
            'email_gateway_exists' => file_exists($email_gateway_path ?? ''),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine()
        ]
    ]);
}
?>