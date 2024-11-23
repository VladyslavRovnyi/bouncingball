<?php
// Enforce secure session settings before starting the session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', true);
    ini_set('session.cookie_secure', true);
    session_start();
}

include '../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize email
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$email || empty($password)) {
        $error = "Invalid email or password.";
    } else {
        try {
            // Fetch admin user from the database
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' AND role = 'admin'");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify user and password
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Prevent session fixation attacks
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_email'] = $user['email'];
                header("Location: admin/dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
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
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h1>Admin Login</h1>
    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="admin_login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
