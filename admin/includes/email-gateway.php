<?php
// admin/includes/email-gateway.php

class EmailNotification {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'olivertabar.7@gmail.com'; // Your Gmail address
    private $smtp_password = 'jmyrujrnyzzlctee';    // Gmail App Password
    private $from_email = 'olivertabar.7@gmail.com';    // Your Gmail address
    private $from_name = 'Orphanfare System';
    
    public function __construct($config = []) {
        // You can override defaults with custom config
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    public function sendMeetingNotification($recipients, $event_details) {
    $success_count = 0;
    $failed_count = 0;
    $results = [];
    
    foreach ($recipients as $recipient) {
        $subject = $this->formatMeetingSubject($event_details);
        $message = $this->formatMeetingEmail($event_details, $recipient['name']);
        
        $result = $this->sendEmail($recipient['email'], $subject, $message);
        
        if ($result['success']) {
            $success_count++;
            $this->logEmail($recipient['email'], $event_details['event_id'], $subject);
        } else {
            $failed_count++;
        }
        
        $results[] = [
            'email' => $recipient['email'],
            'success' => $result['success'],
            'message' => $result['message']
        ];
    }
    
    return [
        'success' => $success_count,
        'failed' => $failed_count,
        'total' => count($recipients),
        'details' => $results
    ];
}
    
    private function sendEmail($to, $subject, $message) {
        try {
            // Always use PHPMailer for Gmail
            return $this->sendWithPHPMailer($to, $subject, $message);
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Email error: ' . $e->getMessage()
            ];
        }
    }
    
    private function sendWithPHPMailer($to, $subject, $message) {
        try {
            // Try multiple ways to load PHPMailer
            
            // Method 1: Composer autoload
            $composer_autoload = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($composer_autoload)) {
                require_once $composer_autoload;
            } 
            // Method 2: Manual PHPMailer
            else {
                $phpmailer_path = __DIR__ . '/../../PHPMailer/PHPMailer.php';
                if (file_exists($phpmailer_path)) {
                    require_once $phpmailer_path;
                    require_once __DIR__ . '/../../PHPMailer/SMTP.php';
                    require_once __DIR__ . '/../../PHPMailer/Exception.php';
                } else {
                    throw new Exception("PHPMailer not found. Please install via Composer or manually.");
                }
            }
            
            // Check if class exists
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                throw new Exception("PHPMailer class not available");
            }
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $this->smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtp_username;
            $mail->Password   = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->smtp_port;
            
            // Enable debugging (remove after testing)
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer: $str");
            };
            
            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            $mail->addReplyTo($this->from_email, $this->from_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $this->wrapEmailTemplate($message);
            $mail->AltBody = strip_tags($message);
            
            $mail->send();
            
            error_log("✅ Email sent successfully to: " . $to);
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (Exception $e) {
            $error_msg = "PHPMailer Error: " . $e->getMessage();
            error_log($error_msg);
            return ['success' => false, 'message' => $error_msg];
        }
    }
    
    // ... keep the rest of your methods (formatMeetingSubject, formatMeetingEmail, etc.)
    // ... [Your existing formatMeetingSubject, formatMeetingEmail, logEmail, testEmail methods remain the same]
    
    private function formatMeetingSubject($event) {
        $date = date('M j, Y', strtotime($event['event_date']));
        $time = date('g:i A', strtotime($event['event_time']));
        
        return "📅 Orphanfare Meeting: {$event['title']} - {$date} at {$time}";
    }
    
    private function formatMeetingEmail($event, $recipient_name = '') {
        $date = date('F j, Y', strtotime($event['event_date']));
        $time = date('g:i A', strtotime($event['event_time']));
        
        $greeting = $recipient_name ? "Hi {$recipient_name}," : "Hello,";
        
        $html = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;'>
                <h1 style='margin: 0;'>📅 Meeting Reminder</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Orphanfare Schedule System</p>
            </div>
            
            <div style='padding: 30px; background: #f8f9fa;'>
                <p>{$greeting}</p>
                
                <div style='background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0;'>
                    <h2 style='color: #333; margin-top: 0;'>{$event['title']}</h2>
                    
                    <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0;'>
                        <div style='display: flex; align-items: center;'>
                            <span style='background: #e3f2fd; padding: 8px; border-radius: 5px; margin-right: 10px;'>📅</span>
                            <div>
                                <strong>Date</strong><br>
                                {$date}
                            </div>
                        </div>
                        
                        <div style='display: flex; align-items: center;'>
                            <span style='background: #e3f2fd; padding: 8px; border-radius: 5px; margin-right: 10px;'>🕒</span>
                            <div>
                                <strong>Time</strong><br>
                                {$time}
                            </div>
                        </div>
                    </div>
        ";
        
        if (!empty($event['location'])) {
            $html .= "
                    <div style='display: flex; align-items: center; margin: 15px 0;'>
                        <span style='background: #e3f2fd; padding: 8px; border-radius: 5px; margin-right: 10px;'>📍</span>
                        <div>
                            <strong>Location</strong><br>
                            {$event['location']}
                        </div>
                    </div>
            ";
        }
        
        if (!empty($event['description'])) {
            $html .= "
                    <div style='margin: 20px 0;'>
                        <strong>Description:</strong><br>
                        <p style='color: #666; line-height: 1.6;'>{$event['description']}</p>
                    </div>
            ";
        }
        
        $html .= "
                </div>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <p style='color: #666; font-size: 14px;'>
                        This is an automated reminder from Orphanfare.<br>
                        Please make sure to be on time for the meeting.
                    </p>
                </div>
            </div>
            
            <div style='background: #333; color: white; padding: 20px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>Orphanfare Management System</p>
                <p style='margin: 5px 0 0 0; opacity: 0.7;'>
                    This email was sent automatically. Please do not reply.
                </p>
            </div>
        </div>
        ";
        
        return $html;
    }
    
    private function wrapEmailTemplate($content) {
        return $content; // Already wrapped in formatMeetingEmail
    }
    
    private function logEmail($email, $event_id, $subject) {
        global $pdo;
        
        try {
            $query = "INSERT INTO email_logs (email_address, event_id, subject, sent_at) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$email, $event_id, $subject]);
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    // Test function
    public function testEmail($to_email = 'olivertabar.7@gmail.com') {
        $test_event = [
            'event_id' => 'TEST-001',
            'title' => 'Test Meeting - System Check',
            'event_date' => date('Y-m-d'),
            'event_time' => date('H:i:s'),
            'location' => 'Virtual Meeting Room',
            'description' => 'This is a test email from the Orphanfare system to verify email notifications are working properly.'
        ];
        
        $recipients = [
            ['email' => $to_email, 'name' => 'Emjay']
        ];
        
        return $this->sendMeetingNotification($recipients, $test_event);
    }
}
?>