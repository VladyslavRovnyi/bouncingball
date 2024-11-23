<?php
include '../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $username = htmlspecialchars(trim($_POST['username']), ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!$email) {
        $error = "Invalid email address.";
    }

    // Handle avatar upload securely
    $avatar_dir = __DIR__ . "/uploads/";
    $avatar_url = "uploads/default-avatar.png"; // Default avatar

    if (!is_dir($avatar_dir)) {
        mkdir($avatar_dir, 0755, true); // Restrict directory permissions
    }

    if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES["avatar"]["tmp_name"];
        $file_name = bin2hex(random_bytes(8)) . '.' . pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
        $file_size = $_FILES["avatar"]["size"];
        $file_type = mime_content_type($file_tmp);

        // Validate file size and type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if ($file_size <= 2000000 && in_array($file_type, $allowed_types)) {
            $target_file = $avatar_dir . $file_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $avatar_url = "uploads/" . $file_name;
            }
        } else {
            $error = "Invalid file type or size. Only images (max 2MB) are allowed.";
        }
    }

    if (empty($error)) {
        // Insert user into database
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, avatar_url)
            VALUES (:username, :email, :password, :avatar_url)
        ");
        try {
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'avatar_url' => $avatar_url,
            ]);
            header("Location: login.php?success=1");
            exit;
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
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <form method="POST" action="register.php" enctype="multipart/form-data">
        <h2>Register</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <label for="avatar">Avatar (Optional)</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">
        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>
