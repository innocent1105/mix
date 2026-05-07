<?php
// includes/logout.php

// Start session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: ../index.php?success=You have been logged out successfully.");
exit();
?>