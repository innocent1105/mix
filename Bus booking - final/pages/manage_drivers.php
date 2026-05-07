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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['phone'], $_POST['license_number'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $license = $_POST['license_number'];

    $stmt = $conn->prepare("INSERT INTO drivers (name, phone, license_number) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $license);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_drivers.php");
    exit;
}

// Delete driver
if (isset($_GET['delete'])) {
    $driver_id = $_GET['delete'];
    $conn->query("DELETE FROM drivers WHERE driver_id = $driver_id");
    header("Location: manage_drivers.php");
    exit;
}

// Fetch all drivers
$drivers = $conn->query("SELECT * FROM drivers");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Drivers</h1>

    <!-- Add Driver Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Driver</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="name" placeholder="Full Name" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            
            <input type="text" name="phone" placeholder="Phone Number" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">
            
            <input type="text" name="license_number" placeholder="License Number" required
                   class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">

            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Driver</button>
        </form>
    </div>

    <!-- Driver List -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Driver List</h2>
        <table class="w-full text-left table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Phone</th>
                    <th class="p-2 border">License</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = $drivers->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border"><?= $d['driver_id'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($d['name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($d['phone']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($d['license_number']) ?></td>
                        <td class="p-2 border"><?= $d['status'] ?></td>
                        <td class="p-2 border">
                            <a href="?delete=<?= $d['driver_id'] ?>" class="text-red-600 hover:underline"
                               onclick="return confirm('Are you sure you want to delete this driver?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
