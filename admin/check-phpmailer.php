<?php
// check-phpmailer.php
echo "<h2>PHPMailer Check</h2>";

// Check Composer autoload
$composer_autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composer_autoload)) {
    echo "✅ Composer autoload found<br>";
    require_once $composer_autoload;
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer class loaded via Composer<br>";
    } else {
        echo "❌ PHPMailer class NOT found via Composer<br>";
    }
} else {
    echo "❌ Composer autoload NOT found at: $composer_autoload<br>";
}

// Check manual PHPMailer
$manual_phpmailer = __DIR__ . '/../PHPMailer/src/PHPMailer.php';
if (file_exists($manual_phpmailer)) {
    echo "✅ Manual PHPMailer found<br>";
} else {
    echo "❌ Manual PHPMailer NOT found at: $manual_phpmailer<br>";
}

// Check email gateway
$email_gateway = __DIR__ . '/includes/email-gateway.php';
if (file_exists($email_gateway)) {
    echo "✅ Email gateway found<br>";
} else {
    echo "❌ Email gateway NOT found at: $email_gateway<br>";
}
?>