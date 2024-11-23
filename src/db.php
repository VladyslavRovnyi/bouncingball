<?php
// Database connection settings (secure sensitive data using environment variables)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'rolex_honeypot');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

try {
    // Use PDO for database connection with secure attributes
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Use exceptions for errors
        PDO::ATTR_EMULATE_PREPARES => false,          // Disable emulated prepared statements
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Fetch associative arrays by default
    ]);
} catch (PDOException $e) {
    // Log error securely and show a generic error message to the user
    error_log("Database connection failed: " . $e->getMessage(), 0);
    die("An error occurred. Please contact support.");
}
?>
