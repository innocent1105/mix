<?php
session_start();
include '../includes/db.php';

$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
$stmt->bind_param("ss", $email, $role);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['related_id'] = $user['related_id'];
    header("Location: ../pages/dashboard.php");
} else {
    echo "<script>alert('Invalid login credentials'); window.location.href='../index.php';</script>";
}
?>
