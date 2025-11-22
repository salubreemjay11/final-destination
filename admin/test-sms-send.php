<?php
require_once 'config/database.php';
require_once 'includes/sms-gateway.php';

header('Content-Type: text/plain');

$test_number = '09468496351';
$test_message = "🔔 TEST from Orphanfare\nSystem: Schedule & Events\nTime: " . date('Y-m-d H:i:s') . "\nThis confirms SMS is working!";

try {
    $sms = new SMSGateway();
    
    // Test the number cleaning
    $cleaned_number = $sms->cleanPhoneNumber($test_number);
    echo "Original: $test_number\n";
    echo "Cleaned: $cleaned_number\n\n";
    
    // Send the SMS
    $result = $sms->sendSMS($test_number, $test_message);
    
    if ($result) {
        echo "✅ SMS sent successfully to $test_number!\n";
        echo "Message: $test_message";
    } else {
        echo "❌ SMS failed to send\n";
        echo "Please check:\n";
        echo "1. API credentials in sms-gateway.php\n";
        echo "2. Internet connection\n";
        echo "3. Semaphore account balance\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
}
?>