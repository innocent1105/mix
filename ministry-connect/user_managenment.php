<?php
include 'config/database.php'; 
// include 'admin_header.php';

$error = '';
$success = '';

/* ===========================
   ADD NEW USER
   =========================== */
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $ministry_id = intval($_POST['ministry_id']);

    if ($name === '' || $email === '' || $password === '') {
        $error = "All fields are required.";
    } else {
        try {
            // Check if user exists
            $check = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) {
                $error = "A user with this email already exists.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO user (ministry_id, name, email, password_hash, role, is_active)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$ministry_id, $name, $email, $password_hash, $role]);
                $success = "User added successfully.";
            }
        } catch (PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    }
}

/* ===========================
   TOGGLE USER STATUS
   =========================== */
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    try {
        $pdo->query("UPDATE user SET is_active = 1 - is_active WHERE id = $id");
        $success = "User status updated successfully.";
    } catch (PDOException $e) {
        $error = "Error toggling status: " . $e->getMessage();
    }
}

/* ===========================
   UPDATE USER ROLE
   =========================== */
if (isset($_POST['update_role'])) {
    $id = intval($_POST['user_id']);
    $role = $_POST['role'];
    try {
        $stmt = $pdo->prepare("UPDATE user SET role = ? WHERE id = ?");
        $stmt->execute([$role, $id]);
        $success = "User role updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating role: " . $e->getMessage();
    }
}

/* ===========================
   LOAD MINISTRIES & USERS
   =========================== */
$ministries = $pdo->query("SELECT id, name FROM ministries ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$usersStmt = $pdo->query("
    SELECT u.id, u.name, u.email, u.role, u.is_active, u.last_login, m.name AS ministry_name
    FROM user u
    JOIN ministries m ON u.ministry_id = m.id
    ORDER BY u.created_at DESC
");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - MinistryLink</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600">Manage users, roles, and account access.</p>
        </div>
        <a href="admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add New User</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="hidden" name="action" value="add_user">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="admin">Admin</option>
                    <option value="approver">Approver</option>
                    <option value="requester" selected>Requester</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ministry</label>
                <select name="ministry_id" class="w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select Ministry</option>
                    <?php foreach ($ministries as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Add User
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">User List</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Ministry</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($user['ministry_name']) ?></td>
                            <td class="px-4 py-3">
                                <form method="POST" class="flex items-center space-x-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" class="border-gray-300 rounded-lg text-sm p-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="approver" <?= $user['role'] === 'approver' ? 'selected' : '' ?>>Approver</option>
                                        <option value="requester" <?= $user['role'] === 'requester' ? 'selected' : '' ?>>Requester</option>
                                    </select>
                                    <button type="submit" name="update_role" class="text-blue-600 hover:text-blue-800 text-xs">
                                        Update
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($user['is_active']): ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="?toggle=<?= $user['id'] ?>" class="text-sm text-blue-600 hover:underline">
                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
