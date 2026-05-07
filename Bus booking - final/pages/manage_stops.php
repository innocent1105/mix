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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stop_name'], $_POST['route_id'], $_POST['arrival_time'])) {
    $stop_name = $_POST['stop_name'];
    $route_id = $_POST['route_id'];
    $arrival_time = $_POST['arrival_time'];

    $stmt = $conn->prepare("INSERT INTO stops (stop_name, route_id, arrival_time) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $stop_name, $route_id, $arrival_time);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_stops.php");
    exit;
}

// Delete stop
if (isset($_GET['delete'])) {
    $stop_id = $_GET['delete'];
    $conn->query("DELETE FROM stops WHERE stop_id = $stop_id");
    header("Location: manage_stops.php");
    exit;
}

// Fetch all stops with route names
$stops = $conn->query("SELECT stops.*, routes.route_name FROM stops JOIN routes ON stops.route_id = routes.route_id");
$routes = $conn->query("SELECT * FROM routes");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Stops</h1>

    <!-- Add Stop Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Stop</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="stop_name" placeholder="Stop Name" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">

            <select name="route_id" required
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                <option value="">Select Route</option>
                <?php while ($r = $routes->fetch_assoc()): ?>
                    <option value="<?= $r['route_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <input type="time" name="arrival_time" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">

            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Stop</button>
        </form>
    </div>

    <!-- Stop List -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Stop List</h2>
        <table class="w-full text-left table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Stop Name</th>
                    <th class="p-2 border">Route</th>
                    <th class="p-2 border">Arrival Time</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($s = $stops->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border"><?= $s['stop_id'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($s['stop_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($s['route_name']) ?></td>
                        <td class="p-2 border"><?= $s['arrival_time'] ?></td>
                        <td class="p-2 border">
                            <a href="?delete=<?= $s['stop_id'] ?>" class="text-red-600 hover:underline"
                               onclick="return confirm('Are you sure you want to delete this stop?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
