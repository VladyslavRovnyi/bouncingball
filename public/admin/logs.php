<?php
session_start();
include '../../src/db.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin_login.php");
    exit;
}

// Secure session settings
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_secure', true);

// Path to logs directory (ensure this is properly secured)
$log_file_path = __DIR__ . "/../../logs/activity.log";
$security_log_file_path = __DIR__ . "/../../logs/security.log";

// Read and display logs securely
function read_logs($file_path) {
    if (file_exists($file_path) && is_readable($file_path)) {
        return htmlspecialchars(file_get_contents($file_path));
    }
    return "Unable to read log file.";
}

$activity_logs = read_logs($log_file_path);
$security_logs = read_logs($security_log_file_path);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>Admin Logs</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1>View Logs</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>
<main>
    <h2>Activity Logs</h2>
    <pre><?= $activity_logs ?></pre>

    <h2>Security Logs</h2>
    <pre><?= $security_logs ?></pre>
</main>
</body>
</html>
