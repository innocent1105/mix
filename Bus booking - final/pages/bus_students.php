<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Fetch all buses
$buses = $conn->query("SELECT buses.bus_id, buses.bus_number, routes.route_name 
                       FROM buses 
                       JOIN routes ON buses.route_id = routes.route_id");

// If a bus is selected
$students = [];
if (isset($_GET['bus_id'])) {
    $bus_id = (int)$_GET['bus_id'];

    $stmt = $conn->prepare("SELECT students.name, students.class, students.contact, 
                                   stops.stop_name, stops.arrival_time
                            FROM students
                            JOIN stops ON students.stop_id = stops.stop_id
                            JOIN buses ON students.route_id = buses.route_id
                            WHERE buses.bus_id = ?");
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $students = $stmt->get_result();
}
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-6">View Students by Bus</h1>

    <form method="GET" class="mb-6">
        <label for="bus_id" class="block mb-2 font-semibold">Select Bus:</label>
        <select name="bus_id" id="bus_id" onchange="this.form.submit()" class="w-full md:w-1/2 p-2 border rounded">
            <option value="">-- Select a Bus --</option>
            <?php while ($bus = $buses->fetch_assoc()): ?>
                <option value="<?= $bus['bus_id'] ?>" <?= (isset($_GET['bus_id']) && $_GET['bus_id'] == $bus['bus_id']) ? 'selected' : '' ?>>
                    Bus <?= htmlspecialchars($bus['bus_number']) ?> - <?= htmlspecialchars($bus['route_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (isset($_GET['bus_id'])): ?>
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Students Assigned to This Bus</h2>
            <?php if ($students->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto border-collapse">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Name</th>
                                <th class="border p-2">Class</th>
                                <th class="border p-2">Contact</th>
                                <th class="border p-2">Stop</th>
                                <th class="border p-2">Arrival Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2"><?= htmlspecialchars($student['name']) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars($student['class']) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars($student['contact']) ?></td>
                                    <td class="border p-2"><?= htmlspecialchars($student['stop_name']) ?></td>
                                    <td class="border p-2"><?= date('h:i A', strtotime($student['arrival_time'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No students found for this bus.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
