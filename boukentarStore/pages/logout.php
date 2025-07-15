<?php
session_start();

// Unset only the user session variable
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}

// Redirect to the public homepage
header("Location: /boukentarStore/index.php");
exit();
?>