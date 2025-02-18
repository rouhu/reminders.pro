<?php
declare(strict_types=1);

class ReminderNotificationService {
    private string $supabaseUrl = 'https://your-project.supabase.co';  // Replace with your Supabase URL
    private string $supabaseKey = 'your-supabase-service-key';         // Replace with your Supabase service key
    private string $fromEmail = 'your-app@example.com';                // Replace with your sender email

    public function __construct() {
        // Verify required extensions
        if (!extension_loaded('curl')) {
            throw new RuntimeException('CURL extension is required');
        }
        if (!extension_loaded('json')) {
            throw new RuntimeException('JSON extension is required');
        }
        if (version_compare(PHP_VERSION, '8.3.0', '<')) {
            throw new RuntimeException('PHP 8.3 or higher is required');
        }
    }

    public function processScheduledReminders(): void {
        try {
            $reminders = $this->fetchScheduledReminders();
            foreach ($reminders as $reminder) {
                $this->processReminder($reminder);
            }
        } catch (Exception $e) {
            error_log("Error processing reminders: " . $e->getMessage());
        }
    }

    private function fetchScheduledReminders(): array {
        $currentGmtTime = new DateTime('now', new DateTimeZone('GMT'));
        
        // Fetch scheduled reminders
        $endpoint = "{$this->supabaseUrl}/rest/v1/reminders";
        
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->supabaseKey,
                'Authorization: Bearer ' . $this->supabaseKey,
                'Content-Type: application/json',
                'Prefer: return=representation'
            ],
            CURLOPT_HTTPGET => true
        ]);

        // Add query parameters for filtering scheduled status
        curl_setopt($ch, CURLOPT_URL, $endpoint . '?select=*&status=eq.scheduled');

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        $reminders = json_decode($response, true);
        if (json_last_error()) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        // Filter reminders that are due
        return array_filter($reminders, function($reminder) use ($currentGmtTime) {
            try {
                // Create DateTime object in reminder's timezone
                $reminderDateTime = new DateTime(
                    "{$reminder['date']} {$reminder['time']}", 
                    new DateTimeZone($reminder['timezone'])
                );
                
                // Convert to GMT for comparison
                $reminderDateTime->setTimezone(new DateTimeZone('GMT'));
                
                // Return true if reminder time is in the past
                return $reminderDateTime <= $currentGmtTime;
            } catch (Exception $e) {
                error_log("Error processing reminder {$reminder['id']}: " . $e->getMessage());
                return false;
            }
        });
    }

    private function processReminder(array $reminder): void {
        try {
            // Send email notification
            $success = $this->sendEmail($reminder);
            
            // Update reminder status
            $status = $success ? 'sent' : 'failed';
            $this->updateReminderStatus($reminder['id'], $status);

            // Log the result
            $result = $success ? 'successfully' : 'with failure';
            error_log("Processed reminder {$reminder['id']} $result");
        } catch (Exception $e) {
            error_log("Error processing reminder {$reminder['id']}: " . $e->getMessage());
            $this->updateReminderStatus($reminder['id'], 'failed');
        }
    }

    private function sendEmail(array $reminder): bool {
        // Configure email headers
        $subject = "Reminder: {$reminder['title']}";
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->fromEmail
        ];

        // Create email body
        $body = $this->createEmailBody($reminder);

        // Send to all recipients
        $success = true;
        foreach ($reminder['recipients'] as $recipient) {
            if (!mail($recipient, $subject, $body, implode("\r\n", $headers))) {
                error_log("Failed to send email to: {$recipient}");
                $success = false;
            }
        }

        return $success;
    }

    private function createEmailBody(array $reminder): string {
        $dateTime = new DateTime(
            "{$reminder['date']} {$reminder['time']}", 
            new DateTimeZone($reminder['timezone'])
        );

        return "
            <html>
            <body>
                <h2>{$reminder['title']}</h2>
                <p><strong>Type:</strong> {$reminder['type']}</p>
                <p><strong>Date:</strong> {$dateTime->format('F j, Y')}</p>
                <p><strong>Time:</strong> {$dateTime->format('g:i A')} {$reminder['timezone']}</p>
            </body>
            </html>
        ";
    }

    private function updateReminderStatus(string $id, string $status): void {
        $endpoint = "{$this->supabaseUrl}/rest/v1/reminders";
        
        $ch = curl_init($endpoint . "?id=eq." . urlencode($id));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode(['status' => $status]),
            CURLOPT_HTTPHEADER => [
                'apikey: ' . $this->supabaseKey,
                'Authorization: Bearer ' . $this->supabaseKey,
                'Content-Type: application/json',
                'Prefer: return=minimal'
            ]
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
    }
}

// Run the service
try {
    $service = new ReminderNotificationService();
    $service->processScheduledReminders();
} catch (Exception $e) {
    error_log("Fatal error: " . $e->getMessage());
    exit(1);
}
