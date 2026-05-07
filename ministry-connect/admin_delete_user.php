<?php
    session_start();
    require_once 'config/database.php';

    // requests

    $admin_email = $_SESSION['user_email'];
    $role = $_SESSION['user_role'];

    if($role !== "admin"){
        header("header: index.php");
    }

    $id = $_GET['id'] ?? null;
    if (!$id) {
        die("No user specified.");
    }

    $stmt = $pdo->prepare("UPDATE `user` SET `is_active` = 0 WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_dashboard.php"); 
    exit;


?>