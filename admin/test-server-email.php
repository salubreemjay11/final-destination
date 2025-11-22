<?php
// test-server-email.php - Test server's built-in mail function
$to = "salubreemjay@gmail.com";
$subject = "Server Email Test";
$message = "This is a test email from your server.";
$headers = "From: webmaster@yoursite.com\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Server mail() function works!";
} else {
    echo "❌ Server mail() function failed!";
}
?>