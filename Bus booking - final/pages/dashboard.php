<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Welcome, <?= ucfirst($_SESSION['role']) ?>!</h1>

    <?php
    $role = $_SESSION['role'];
    if ($role === 'admin') {
        include 'admin_dashboard.php';
    } elseif ($role === 'driver') {
        include 'driver_dashboard.php';
    } elseif ($role === 'parent') {
        include 'parent_dashboard.php';
    } else {
        echo "<p>Invalid role.</p>";
    }
    ?>
</div>

<?php include '../includes/footer.php'; ?>
