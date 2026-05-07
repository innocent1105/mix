<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$today = date('Y-m-d');

// Check today's attendance
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND work_date = ?");
$stmt->execute([$userId, $today]);
$attendance = $stmt->fetch();

// Handle sign-in
if (isset($_POST['sign_in']) && !$attendance) {
    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, sign_in_time, work_date, status) VALUES (?, NOW(), ?, 'Signed In')");
    $stmt->execute([$userId, $today]);
    header("Location: attendance.php");
    exit();
}

// Handle sign-out
if (isset($_POST['sign_out']) && $attendance && !$attendance['sign_out_time']) {
    $stmt = $pdo->prepare("UPDATE attendance SET sign_out_time = NOW(), status = 'Signed Out' WHERE id = ?");
    $stmt->execute([$attendance['id']]);
    header("Location: attendance.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Attendance | EMS</title>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow">
        <h2 class="mb-4 text-2xl font-bold text-center">Attendance</h2>

        <?php if (!$attendance): ?>
            <form method="POST">
                <button name="sign_in" class="w-full px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">
                    Sign In
                </button>
            </form>
        <?php elseif ($attendance && !$attendance['sign_out_time']): ?>
            <p class="mb-4 text-center text-green-600">Signed in at: <?php echo date('h:i A', strtotime($attendance['sign_in_time'])); ?></p>
            <form method="POST">
                <button name="sign_out" class="w-full px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">
                    Sign Out
                </button>
            </form>
        <?php else: ?>
            <div class="text-center text-blue-600">
                <p>Signed in at: <?php echo date('h:i A', strtotime($attendance['sign_in_time'])); ?></p>
                <p>Signed out at: <?php echo date('h:i A', strtotime($attendance['sign_out_time'])); ?></p>
                <p class="mt-2 font-semibold">You're done for today 🎉</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
