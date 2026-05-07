<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/db.php'; // ← your DB connection file

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['route_name'])) {
    $route_name = $_POST['route_name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO routes (route_name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $route_name, $description);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_routes.php");
    exit;
}

// Delete route
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM routes WHERE route_id = $id");
    header("Location: manage_routes.php");
    exit;
}

// Fetch all routes
$result = $conn->query("SELECT * FROM routes");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-4">Manage Routes</h1>

    <!-- Add Route Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Route</h2>
        <form method="POST" class="space-y-4">
            <input type="text" name="route_name" placeholder="Route Name" required
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300">

            <textarea name="description" placeholder="Description"
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"></textarea>

            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Route</button>
        </form>
    </div>

    <!-- Route List -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">All Routes</h2>
        <table class="w-full text-left table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">ID</th>
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Description</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($route = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="p-2 border"><?= $route['route_id'] ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($route['route_name']) ?></td>
                        <td class="p-2 border"><?= htmlspecialchars($route['description']) ?></td>
                        <td class="p-2 border">
                            <a href="?delete=<?= $route['route_id'] ?>"
                               class="text-red-600 hover:underline"
                               onclick="return confirm('Are you sure you want to delete this route?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
