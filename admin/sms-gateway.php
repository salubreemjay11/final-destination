<?php
// admin/includes/sms-gateway.php

class SMSGateway {
    private $api_key;
    private $sender_id;
    private $provider;
    
    public function __construct($provider = 'semaphore') {
        $this->provider = $provider;
        $this->loadConfig();
    }
    
    private function loadConfig() {
        // You can store these in your database or config file
        $config = [
            'semaphore' => [
                'api_key' => 'YOUR_SEMAPHORE_API_KEY',
                'sender_id' => 'Orphanfare',
                'endpoint' => 'https://api.semaphore.co/api/v4/messages'
            ],
            'twilio' => [
                'account_sid' => 'YOUR_TWILIO_SID',
                'auth_token' => 'YOUR_TWILIO_TOKEN',
                'from_number' => 'YOUR_TWILIO_NUMBER',
                'endpoint' => 'https://api.twilio.com/2010-04-01/Accounts/'
            ]
        ];
        
        $this->config = $config[$this->provider] ?? $config['semaphore'];
    }
    
    /**
     * Send SMS using Semaphore API (Philippines-based, reliable for PH numbers)
     */
    public function sendSMS($number, $message) {
        // Clean the phone number (remove spaces, dashes, etc.)
        $number = $this->cleanPhoneNumber($number);
        
        if (!$this->isValidPhilippineNumber($number)) {
            error_log("Invalid Philippine number: $number");
            return false;
        }
        
        switch ($this->provider) {
            case 'semaphore':
                return $this->sendViaSemaphore($number, $message);
            case 'twilio':
                return $this->sendViaTwilio($number, $message);
            default:
                return $this->sendViaSemaphore($number, $message);
        }
    }
    
    private function sendViaSemaphore($number, $message) {
        $data = [
            'apikey' => $this->config['api_key'],
            'number' => $number,
            'message' => $message,
            'sendername' => $this->config['sender_id']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['endpoint']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($http_code === 200 && isset($result[0]['status']) && $result[0]['status'] === 'Pending') {
            error_log("SMS sent successfully to $number");
            return true;
        } else {
            error_log("SMS failed to $number: " . ($response ?: 'Unknown error'));
            return false;
        }
    }
    
    private function sendViaTwilio($number, $message) {
        $url = $this->config['endpoint'] . $this->config['account_sid'] . '/Messages.json';
        
        $data = [
            'To' => $number,
            'From' => $this->config['from_number'],
            'Body' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['account_sid'] . ':' . $this->config['auth_token']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 201) {
            error_log("Twilio SMS sent successfully to $number");
            return true;
        } else {
            error_log("Twilio SMS failed to $number: " . $response);
            return false;
        }
    }
    
    private function cleanPhoneNumber($number) {
        // Remove all non-digit characters except +
        $number = preg_replace('/[^\d+]/', '', $number);
        
        // If it starts with 0, convert to +63 format
        if (substr($number, 0, 1) === '0') {
            $number = '+63' . substr($number, 1);
        }
        
        // If it doesn't have country code, assume Philippines
        if (substr($number, 0, 1) !== '+') {
            $number = '+63' . $number;
        }
        
        return $number;
    }
    
    private function isValidPhilippineNumber($number) {
        // Philippine numbers should be +63 followed by 10 digits
        return preg_match('/^\+63\d{10}$/', $number);
    }
    
    /**
     * Send meeting reminder to multiple recipients
     */
    public function sendMeetingReminder($recipients, $event_details) {
        $success_count = 0;
        $failed_count = 0;
        
        foreach ($recipients as $recipient) {
            $message = $this->formatMeetingMessage($event_details, $recipient['name']);
            
            if ($this->sendSMS($recipient['phone'], $message)) {
                $success_count++;
                
                // Log the SMS sent
                $this->logSMSNotification($recipient['phone'], $event_details['event_id'], $message);
            } else {
                $failed_count++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        return [
            'success' => $success_count,
            'failed' => $failed_count,
            'total' => count($recipients)
        ];
    }
    
    private function formatMeetingMessage($event, $recipient_name = '') {
        $date = date('F j, Y', strtotime($event['event_date']));
        $time = date('g:i A', strtotime($event['event_time']));
        
        $greeting = $recipient_name ? "Hi $recipient_name," : "Hello,";
        
        $message = "$greeting\n\n";
        $message .= "📅 MEETING REMINDER\n";
        $message .= "Event: {$event['title']}\n";
        $message .= "Date: $date\n";
        $message .= "Time: $time\n";
        
        if (!empty($event['location'])) {
            $message .= "Location: {$event['location']}\n";
        }
        
        if (!empty($event['description'])) {
            $desc = substr($event['description'], 0, 100);
            if (strlen($event['description']) > 100) {
                $desc .= '...';
            }
            $message .= "Details: $desc\n";
        }
        
        $message .= "\nPlease be on time.\n";
        $message .= "- Orphanfare Team";
        
        return $message;
    }
    
    private function logSMSNotification($phone, $event_id, $message) {
        global $pdo;
        
        try {
            $query = "INSERT INTO sms_logs (phone_number, event_id, message, sent_at) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$phone, $event_id, $message]);
        } catch (Exception $e) {
            error_log("Failed to log SMS: " . $e->getMessage());
        }
    }
}

// Helper function to send meeting reminder
function sendMeetingReminders($event_id, $recipients) {
    global $pdo;
    
    try {
        // Get event details
        $query = "SELECT * FROM events WHERE event_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            return ['error' => 'Event not found'];
        }
        
        $sms = new SMSGateway();
        $result = $sms->sendMeetingReminder($recipients, $event);
        
        // Log the bulk SMS activity
        logActivity($_SESSION['user_id'] ?? 1, 'SMS Reminder Sent', 'events', $event_id);
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error sending meeting reminders: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}
?>