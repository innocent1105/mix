<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['class'], $_POST['contact'], $_POST['route_id'], $_POST['stop_id'], $_POST['parent_id'])) {
    $name = $_POST['name'];
    $class = $_POST['class'];
    $contact = $_POST['contact'];
    $route_id = $_POST['route_id'];
    $stop_id = $_POST['stop_id'];
    $parent_id = $_POST['parent_id'];

    $stmt = $conn->prepare("INSERT INTO students (name, class, contact, route_id, stop_id, parent_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiii", $name, $class, $contact, $route_id, $stop_id, $parent_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_students.php");
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE student_id = $student_id");
    header("Location: manage_students.php");
    exit;
}

// Fetch students with related info
$students = $conn->query("SELECT students.*, routes.route_name, stops.stop_name, parents.name AS parent_name 
                          FROM students 
                          LEFT JOIN routes ON students.route_id = routes.route_id 
                          LEFT JOIN stops ON students.stop_id = stops.stop_id 
                          LEFT JOIN parents ON students.parent_id = parents.parent_id");

$routes = $conn->query("SELECT * FROM routes");
$stops = $conn->query("SELECT * FROM stops");
$parents = $conn->query("SELECT * FROM parents");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-6">Manage Students</h1>

    <!-- Add Student Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Student</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="name" placeholder="Full Name" class="border-2 p-2 rounded" required class="input" />
            <input type="text" name="class" placeholder="Class" class="border-2 p-2 rounded" required class="input" />
            <input type="text" name="contact" placeholder="Contact" class="border-2 p-2 rounded" required class="input" />

            <select name="route_id" required class="border-2 p-2 rounded">
                <option value="">Select Route</option>
                <?php while ($r = $routes->fetch_assoc()): ?>
                    <option value="<?= $r['route_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="stop_id" required class="border-2 p-2 rounded">
                <option value="">Select Stop</option>
                <?php while ($s = $stops->fetch_assoc()): ?>
                    <option value="<?= $s['stop_id'] ?>"><?= htmlspecialchars($s['stop_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="parent_id" required class="border-2 p-2 rounded">
                <option value="">Select Parent</option>
                <?php while ($p = $parents->fetch_assoc()): ?>
                    <option value="<?= $p['parent_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <div class="col-span-full">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Add Student
                </button>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Student List</h2>
        <div class="overflow-x-auto">
            <table class="w-full table-auto border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Class</th>
                        <th class="border p-2">Contact</th>
                        <th class="border p-2">Route</th>
                        <th class="border p-2">Stop</th>
                        <th class="border p-2">Parent</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $students->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2"><?= $student['student_id'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($student['name']) ?></td>
                            <td class="border p-2"><?= $student['class'] ?></td>
                            <td class="border p-2"><?= $student['contact'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($student['route_name']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($student['stop_name']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($student['parent_name']) ?></td>
                            <td class="border p-2">
                                <a href="?delete=<?= $student['student_id'] ?>" class="text-red-600 hover:underline"
                                   onclick="return confirm('Delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.input {
    @apply w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300;
}
</style>

<?php include '../includes/footer.php'; ?>
