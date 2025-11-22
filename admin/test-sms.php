<?php
require_once 'config/database.php';
require_once 'includes/sms-gateway.php';

// Test SMS sending
$test_number = '09468496351';
$test_message = "🔔 TEST MESSAGE from Orphanfare\nThis is a test of the SMS notification system.\n- Orphanfare Team";

echo "Testing SMS to: $test_number\n";
echo "Message: $test_message\n\n";

try {
    $sms = new SMSGateway();
    $result = $sms->sendSMS($test_number, $test_message);
    
    if ($result) {
        echo "✅ SMS sent successfully!\n";
    } else {
        echo "❌ SMS failed to send\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>