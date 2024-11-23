<?php
// Secure session settings before starting the session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_secure', true);
    session_start();
}

include '../../src/db.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="logs.php">View Logs</a>
        <a href="users.php">Manage Users</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>
<main>
    <p>Welcome, <?= htmlspecialchars($_SESSION['admin_email'], ENT_QUOTES, 'UTF-8') ?>. Use the links above to manage the application.</p>
</main>
</body>
</html>
