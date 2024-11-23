<?php

function logMessage($level, $message, $file = 'activity.log') {
    $log_dir = __DIR__ . '/../logs/';
    $log_file = $log_dir . $file;

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true); // Create logs directory if it doesn't exist
    }

    $timestamp = date('Y-m-d H:i:s');
    $formatted_message = "[$timestamp] [$level] $message" . PHP_EOL;

    file_put_contents($log_file, $formatted_message, FILE_APPEND | LOCK_EX);
}

// Example usage:
// logMessage('INFO', 'User logged in successfully.');
// logMessage('ERROR', 'Database connection failed.');
?>
