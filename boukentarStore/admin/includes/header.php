<?php
session_start();
// Protect this page from unauthorized access
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            position: fixed;
            height: 100%;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            transition: all 0.3s;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="sidebar p-3">
    <h4 class="text-center">Admin Panel</h4>
    <hr>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_products.php"><i class="fas fa-box me-2"></i>Manage Products</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="manage_categories.php"><i class="fas fa-tags me-2"></i>Manage Categories</a>
        </li>
        <li class="nav-item mt-auto">
            <a class="nav-link" href="/boukentarStore/index.php"><i class="fas fa-home me-2"></i>Public Homepage</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
        </li>
    </ul>
</div>
<div class="main-content">
    <button class="btn btn-primary d-md-none mb-3" id="sidebar-toggle">Toggle Menu</button>
