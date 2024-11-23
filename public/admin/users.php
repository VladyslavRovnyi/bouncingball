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

// Handle enable/disable actions
if (isset($_GET['action'], $_GET['id']) && in_array($_GET['action'], ['enable', 'disable'])) {
    $action = $_GET['action'];
    $user_id = (int) $_GET['id'];

    try {
        $status = $action === 'enable' ? 'active' : 'disabled';
        $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $user_id]);
        header("Location: users.php"); // Refresh the page
        exit;
    } catch (PDOException $e) {
        error_log("Error updating user status: " . $e->getMessage());
        $error = "An error occurred. Please try again.";
    }
}

// Fetch users from the database
try {
    $stmt = $conn->prepare("SELECT id, username, email, role, status FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    die("An error occurred. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
    <h1>Manage Users</h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logs.php">View Logs</a>
        <a href="../logout.php">Logout</a>
    </nav>
</header>
<main>
    <h2>User List</h2>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($user['status'] === 'active'): ?>
                        <a href="users.php?action=disable&id=<?= $user['id'] ?>">Disable</a>
                    <?php else: ?>
                        <a href="users.php?action=enable&id=<?= $user['id'] ?>">Enable</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>
</body>
</html>
