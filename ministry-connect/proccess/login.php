<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['ministry_id'] = $user['ministry_id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Log the login activity
            $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details) VALUES (?, ?, ?)");
            $log_stmt->execute([$user['id'], 'login', 'User logged in successfully']);
            
            header("Location: ../dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: ../index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please fill in all fields";
        header("Location: ../index.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>