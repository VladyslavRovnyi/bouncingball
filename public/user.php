<?php
session_start();
include '../src/db.php';

// Intentionally insecure query to simulate SQL Injection vulnerability
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch user data
try {
    // Vulnerable query (intentionally not parameterized for honeypot purposes)
    $stmt = $conn->query("SELECT * FROM users WHERE id = $id");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
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
        <h1>Welcome, <?= htmlspecialchars($user['username']) ?></h1>
        <img src="<?= htmlspecialchars($user['avatar_url'] ?? 'uploads/default-avatar.png') ?>" alt="Avatar" style="width:100px;height:100px;">
        <p>Email: <?= htmlspecialchars($user['email']) ?></p>
        <p>Status: <?= htmlspecialchars($user['status']) ?></p>
    <?php else: ?>
        <h1>User Not Found</h1>
        <p>The user ID you requested does not exist or is invalid.</p>
    <?php endif; ?>
</div>
</body>
</html>
