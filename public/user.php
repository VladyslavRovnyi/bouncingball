<?php
session_start();
include '../src/db.php';

// Validate and sanitize the user ID from the GET parameter
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : null;

if ($id === null) {
    die("Error: No valid user ID provided.");
}

// Fetch user data securely
try {
    // Use a parameterized query to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log the error and provide a generic error message
    error_log("Database error: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <?php if ($user): ?>
        <h1>Welcome, <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></h1>
        <img src="<?= htmlspecialchars($user['avatar_url'] ?? 'uploads/default-avatar.png', ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" style="width:100px;height:100px;">
        <p>Email: <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></p>
        <p>Status: <?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <h1>User Not Found</h1>
        <p>The user ID you requested does not exist or is invalid.</p>
    <?php endif; ?>
</div>
</body>
</html>
