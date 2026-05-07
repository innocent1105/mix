<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    header("Location: ../index.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

$parent_id = $_SESSION['related_id'];

// Fetch student details + bus name + stop name + arrival time
$stmt = $conn->prepare("
    SELECT s.student_id, s.name, s.class, 
           b.bus_id, b.bus_name, b.arrival_time,
           st.stop_name
    FROM students s
    LEFT JOIN buses b ON s.bus_id = b.bus_id
    LEFT JOIN stops st ON s.stop_id = st.stop_id
    WHERE s.parent_id = ?
");

$stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-6">Your Child(ren)’s Information</h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php while ($child = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($child['name']) ?></h2>
                    <p><strong>Class:</strong> <?= htmlspecialchars($child['class']) ?></p>
                    <p><strong>Bus:</strong> <?= htmlspecialchars($child['bus_name'] ?? 'Not Assigned') ?></p>
                    <p><strong>Arrival Time:</strong> <?= htmlspecialchars($child['arrival_time'] ?? 'Unknown') ?></p>
                    <p><strong>Stop:</strong> <?= htmlspecialchars($child['stop_name'] ?? 'N/A') ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-700">No child records found linked to your account.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
