<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php';

// Handle bus addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bus_number'])) {
    $bus_number = $_POST['bus_number'];
    $capacity = $_POST['capacity'];
    $route_id = $_POST['route_id'];
    $driver_id = $_POST['driver_id'];

    $stmt = $conn->prepare("INSERT INTO buses (bus_number, capacity, route_id, driver_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siii", $bus_number, $capacity, $route_id, $driver_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_buses.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM buses WHERE bus_id = $id");
    header("Location: manage_buses.php");
    exit;
}

// Fetch related data
$buses = $conn->query("SELECT b.*, r.route_name, d.name AS driver_name FROM buses b 
                       LEFT JOIN routes r ON b.route_id = r.route_id
                       LEFT JOIN drivers d ON b.driver_id = d.driver_id");
$routes = $conn->query("SELECT route_id, route_name FROM routes");
$drivers = $conn->query("SELECT driver_id, name FROM drivers WHERE status = 'Active'");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Buses</h1>

    <!-- Add Bus Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Bus</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="bus_number" placeholder="Bus Number" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            
            <input type="number" name="capacity" placeholder="Capacity" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            
            <select name="route_id" required
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                <option value="">Select Route</option>
                <?php while ($r = $routes->fetch_assoc()): ?>
                    <option value="<?= $r['route_id'] ?>"><?= htmlspecialchars($r['route_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="driver_id" required
                    class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
                <option value="">Select Driver</option>
                <?php while ($d = $drivers->fetch_assoc()): ?>
                    <option value="<?= $d['driver_id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Bus</button>
        </form>
    </div>

    <!-- Bus List -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">All Buses</h2>
        <table class="w-full text-left table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Number</th>
                    <th class="p-2 border">Capacity</th>
                    <th class="p-2 border">Route</th>
                    <th class="p-2 border">Driver</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($bus = $buses->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border"><?= $bus['bus_id'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($bus['bus_number']) ?></td>
                        <td class="p-2 border"><?= $bus['capacity'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($bus['route_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($bus['driver_name']) ?></td>
                        <td class="p-2 border">
                            <a href="?delete=<?= $bus['bus_id'] ?>" class="text-red-600 hover:underline"
                               onclick="return confirm('Delete this bus?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
