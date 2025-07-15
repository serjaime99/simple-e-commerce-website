<?php
$hide_categories_filter = true; // Set flag to hide categories in header
require_once '../includes/header.php';

// The session is already started in header.php
// The database connection is already established in header.php via db_connect.php

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $shipping_address = trim($_POST['shipping_address']);
    $phone_number = trim($_POST['phone_number']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($shipping_address) || empty($phone_number)) {
        $message = "<div class='alert alert-danger'>All fields are required.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>Invalid email format.</div>";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $message = "<div class='alert alert-danger'>This email address is already registered.</div>";
            } else {
                // Insert new user
                $sql = "INSERT INTO users (full_name, email, password, shipping_address, phone_number) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$full_name, $email, $password, $shipping_address, $phone_number]);

                $message = "<div class='alert alert-success'>Registration successful! You can now <a href='login.php'>login</a>.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        }
    }
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h2 class="auth-title">Create your account</h2>
        <?php echo $message; ?>
        <form action="register.php" method="post">
            <div class="mb-3">
                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email address" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <div class="mb-3">
                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" placeholder="Shipping Address" required></textarea>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="Phone Number" required>
            </div>
            <button type="submit" class="btn btn-primary auth-btn">Register</button>
            <p class="mt-3 auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


