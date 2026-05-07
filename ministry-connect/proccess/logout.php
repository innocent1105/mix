<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (isset($_SESSION['user_id'])) {
    // Log the logout activity
    logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out successfully');
}

// Destroy all session variables
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../index.php");
exit();
?>