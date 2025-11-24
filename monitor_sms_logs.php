<?php

/**
 * SMS Log Monitor
 * 
 * This script monitors the Laravel log file for SMS-related activity
 * and displays it in real-time to help track SMS functionality.
 * 
 * Usage: php monitor_sms_logs.php
 */

echo "📱 SMS Log Monitor\n";
echo "=================\n\n";

echo "Monitoring SMS-related logs in real-time...\n";
echo "Press Ctrl+C to stop monitoring\n\n";

$logFile = __DIR__ . '/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "❌ Log file not found: {$logFile}\n";
    echo "Make sure Laravel is properly set up and logging is enabled.\n";
    exit(1);
}

echo "✅ Log file found: {$logFile}\n";
echo "Monitoring for SMS-related entries...\n\n";

// Function to check for SMS-related log entries
function isSmsRelated($line) {
    $smsKeywords = [
        'SMS', 'Phone', 'Booking', 'Event', 'SendBooking', 'SendPayment',
        'MimSms', 'SmsTemplate', 'PhoneNumber', 'validation', 'confirmation'
    ];
    
    foreach ($smsKeywords as $keyword) {
        if (stripos($line, $keyword) !== false) {
            return true;
        }
    }
    
    return false;
}

// Function to format log entry
function formatLogEntry($line) {
    // Extract timestamp and message
    if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)$/', $line, $matches)) {
        $timestamp = $matches[1];
        $level = strtoupper($matches[2]);
        $channel = $matches[3];
        $message = $matches[4];
        
        // Color coding based on log level
        $color = '';
        switch (strtolower($level)) {
            case 'error':
                $color = "\033[31m"; // Red
                break;
            case 'warning':
                $color = "\033[33m"; // Yellow
                break;
            case 'info':
                $color = "\033[32m"; // Green
                break;
            case 'debug':
                $color = "\033[36m"; // Cyan
                break;
            default:
                $color = "\033[0m"; // Default
        }
        
        $reset = "\033[0m";
        
        return "{$color}[{$timestamp}] {$level}: {$message}{$reset}\n";
    }
    
    return $line;
}

// Monitor the log file
$lastSize = filesize($logFile);
$lastPosition = $lastSize;

while (true) {
    clearstatcache();
    $currentSize = filesize($logFile);
    
    if ($currentSize > $lastSize) {
        $handle = fopen($logFile, 'r');
        fseek($handle, $lastPosition);
        
        while (($line = fgets($handle)) !== false) {
            if (isSmsRelated($line)) {
                echo formatLogEntry($line);
            }
        }
        
        $lastPosition = ftell($handle);
        fclose($handle);
    }
    
    $lastSize = $currentSize;
    usleep(100000); // Sleep for 100ms
}
