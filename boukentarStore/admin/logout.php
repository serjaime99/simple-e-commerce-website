<?php
session_start();

// Unset only the admin session variable
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
}

// Redirect to the public homepage
header("Location: /boukentarStore/index.php");
exit();
?>
