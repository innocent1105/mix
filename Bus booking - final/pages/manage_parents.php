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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['password'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert into parents
    $stmt = $conn->prepare("INSERT INTO parents (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $password);
    $stmt->execute();
    $parent_id = $stmt->insert_id; // get inserted parent's ID
    $stmt->close();

    // Insert into users
    $stmt2 = $conn->prepare("INSERT INTO users (email, password, role, related_id) VALUES (?, ?, 'parent', ?)");
    $stmt2->bind_param("ssi", $email, $password, $parent_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: manage_parents.php");
    exit;
}


// Handle deletion
if (isset($_GET['delete'])) {
    $parent_id = $_GET['delete'];
    $conn->query("DELETE FROM parents WHERE parent_id = $parent_id");
    header("Location: manage_parents.php");
    exit;
}

// Fetch all parents
$parents = $conn->query("SELECT * FROM parents");
?>

<div class="ml-64 p-6">
    <h1 class="text-2xl font-bold mb-6">Manage Parents</h1>

    <!-- Add Parent Form -->
    <div class="bg-white p-6 rounded-xl shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Add New Parent</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="name" placeholder="Full Name" class=" border-2 p-2 rounded" required class="input" />
            <input type="email" name="email" placeholder="Email" class=" border-2 p-2 rounded" required class="input" />
            <input type="text" name="phone" placeholder="Phone Number" class=" border-2 p-2 rounded" required class="input" />
            <input type="password" name="password" placeholder="Password" class=" border-2 p-2 rounded" required class="input" />
            <div class="col-span-full">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Add Parent
                </button>
            </div>
        </form>
    </div>

    <!-- Parents Table -->
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-semibold mb-4">Parent List</h2>
        <div class="overflow-x-auto">
            <table class="w-full table-auto border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Name</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Phone</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($parent = $parents->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2"><?= $parent['parent_id'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($parent['name']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($parent['email']) ?></td>
                            <td class="border p-2"><?= htmlspecialchars($parent['phone']) ?></td>
                            <td class="border p-2">
                                <a href="?delete=<?= $parent['parent_id'] ?>" class="text-red-600 hover:underline"
                                   onclick="return confirm('Delete this parent?');">Delete</a>
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
