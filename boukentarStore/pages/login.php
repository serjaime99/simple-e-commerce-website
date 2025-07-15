<?php
$hide_categories_filter = true; // Set flag to hide categories in header
require_once '../includes/header.php';

// The session is already started in header.php
// The database connection is already established in header.php via db_connect.php

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "<div class='alert alert-danger'>Email and password are required.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Clear any existing admin session data
                if (isset($_SESSION['admin_id'])) {
                    unset($_SESSION['admin_id']);
                }

                $_SESSION['user_id'] = $user['id'];
                header("Location: ../index.php");
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Invalid email or password.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Welcome back!</h2>
        <p class="auth-subtitle">Log in to your account.</p>
        <?php echo $message; ?>
        <form action="login.php" method="post">
            <div class="mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary auth-btn">Login</button>
            <p class="mt-3 auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
