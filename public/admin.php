<?php
session_start();
include '../src/db.php';

// Vulnerability: Broken Access Control
// Allow access to admin.php if `?bypass=true` is in the URL (intentionally insecure)
if (isset($_GET['bypass'])) {
    $_SESSION['is_admin'] = true;
}

// Check if the user is an admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    echo "You are not authorized to access this page! <a href='login.php'>Login</a>";
    exit;
}

// Fetch all users from the database
$stmt = $conn->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle enabling/disabling users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = $_POST['user_id'];
    $new_status = ($_POST['action'] === 'disable') ? 'disabled' : 'active';

    $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $new_status, 'id' => $user_id]);

    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Admin Panel</h1>
    <p><strong>Note:</strong> Broken Access Control vulnerability enabled. Use <code>?bypass=true</code> in the URL to access without logging in.</p>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['status']) ?></td>
                <td>
                    <?php if ($user['status'] === 'active'): ?>
                        <form action="admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="action" value="disable">
                            <button type="submit">Disable</button>
                        </form>
                    <?php else: ?>
                        <form action="admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="action" value="enable">
                            <button type="submit">Enable</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
