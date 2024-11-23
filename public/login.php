<?php
// Enforce secure session settings before starting a session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_secure', true);
    session_start();
}

require_once '../src/db.php'; // Use require_once to avoid multiple inclusions

// Enforce HTTPS and secure cookies
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    // Allow HTTP during local development
    if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        // Do nothing: Allow HTTP for local development
    } else {
        // Enforce HTTPS in production
        die("Secure connection required.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = "Invalid username or password.";
    } else {
        try {
            // Secure query to fetch user
            $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:username) AND status = 'active'");
            $stmt->execute(['username' => strtolower($username)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    $_SESSION['is_admin'] = true;
                    header("Location: admin/dashboard.php"); // Redirect admin to the admin dashboard
                } else {
                    header("Location: index.php"); // Redirect regular users to the home page
                }
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline';">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <form method="POST" action="login.php">
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
