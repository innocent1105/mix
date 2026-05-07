<?php
// includes/auth_check.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if user session is still valid
$session_timeout = 3600; // 1 hour in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Last activity was more than 1 hour ago
    session_unset();
    session_destroy();
    header("Location: ../index.php?error=Session expired. Please login again.");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check if user still exists in database
// Use absolute path to avoid issues with different include contexts
$configPath = __DIR__ . '/../config/database.php';
if (file_exists($configPath)) {
    require_once $configPath;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM user WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            // User doesn't exist in database anymore
            session_unset();
            session_destroy();
            header("Location: ../index.php?error=User account not found.");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error but don't break the application
        error_log("Database error in auth_check: " . $e->getMessage());
    }
} else {
    // Log config file error but don't break authentication
    error_log("Database configuration file not found: " . $configPath);
}

// Function to check if user has specific role
function hasRole($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    // Define role hierarchy (admin has access to everything)
    $role_hierarchy = [
        'user' => 1,
        'manager' => 2,
        'admin' => 3
    ];
    
    $user_role_level = isset($role_hierarchy[$_SESSION['user_role']]) ? $role_hierarchy[$_SESSION['user_role']] : 0;
    $required_role_level = isset($role_hierarchy[$required_role]) ? $role_hierarchy[$required_role] : 0;
    
    return $user_role_level >= $required_role_level;
}

// Function to check if user belongs to specific ministry
function belongsToMinistry($ministry_id) {
    return isset($_SESSION['ministry_id']) && $_SESSION['ministry_id'] == $ministry_id;
}
?>