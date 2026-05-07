<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!$_SESSION['is_admin']) {
    header("Location: ../dashboard.php?error=Access denied. Admin privileges required.");
    exit();
}

// Get filter parameters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

// Build query based on log type
if ($type === 'admin') {
    $query = "
        SELECT al.*, u.name as user_name 
        FROM admin_log al 
        JOIN user u ON al.admin_id = u.id 
        WHERE 1=1
    ";
} else {
    // Check if timestamp column exists, otherwise use created_at
    try {
        $test_stmt = $pdo->query("SELECT timestamp FROM log LIMIT 1");
        $timestamp_column = 'timestamp';
    } catch (PDOException $e) {
        $timestamp_column = 'created_at';
    }
    
    $query = "
        SELECT l.*, u.name as user_name, m.name as ministry_name, dr.request_id
        FROM log l
        JOIN user u ON l.user_id = u.id
        JOIN ministry m ON u.ministry_id = m.id
        LEFT JOIN data_requests dr ON l.request_id = dr.id
        WHERE 1=1
    ";
}

$params = [];

if (!empty($action_filter)) {
    $query .= " AND action = ?";
    $params[] = $action_filter;
}

if (!empty($date_from)) {
    $date_column = ($type === 'admin') ? 'al.created_at' : "l.$timestamp_column";
    $query .= " AND DATE($date_column) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $date_column = ($type === 'admin') ? 'al.created_at' : "l.$timestamp_column";
    $query .= " AND DATE($date_column) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY " . (($type === 'admin') ? 'al.created_at' : "l.$timestamp_column") . " DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get distinct actions for filter dropdown
if ($type === 'admin') {
    $actions = $pdo->query("SELECT DISTINCT action FROM admin_log ORDER BY action")->fetchAll();
} else {
    $actions = $pdo->query("SELECT DISTINCT action FROM log ORDER BY action")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - System Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <?php include '../includes/admin_header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">System Logs</h2>
                    <p class="text-gray-600">Monitor all system activities and admin actions</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Log Type</label>
                        <select name="type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" onchange="this.form.submit()">
                            <option value="all" <?php echo $type == 'all' ? 'selected' : ''; ?>>All Logs</option>
                            <option value="admin" <?php echo $type == 'admin' ? 'selected' : ''; ?>>Admin Actions</option>
                            <option value="system" <?php echo $type == 'system' ? 'selected' : ''; ?>>System Logs</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                        <select name="action" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">All Actions</option>
                            <?php foreach ($actions as $action): ?>
                                <option value="<?php echo $action['action']; ?>" <?php echo $action_filter == $action['action'] ? 'selected' : ''; ?>>
                                    <?php echo str_replace('_', ' ', $action['action']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?php echo $date_from; ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" value="<?php echo $date_to; ?>">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors w-full">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <?php echo $type == 'admin' ? 'Admin Activities' : ($type == 'system' ? 'System Activities' : 'All Activities'); ?>
                    </h3>
                    <p class="text-sm text-gray-600 mt-2 sm:mt-0"><?php echo count($logs); ?> log entries found</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <?php if ($type !== 'admin'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ministry</th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <?php if ($type === 'admin'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                            <?php else: ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                            <?php endif; ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <?php if ($type === 'admin'): ?>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php 
                                    $date_column = isset($log['timestamp']) ? $log['timestamp'] : (isset($log['created_at']) ? $log['created_at'] : '');
                                    echo date('M j, Y g:i A', strtotime($date_column)); 
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $log['user_name']; ?></td>
                                <?php if ($type !== 'admin'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $log['ministry_name']; ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo str_replace('_', ' ', $log['action']); ?></td>
                                <?php if ($type === 'admin'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $log['target_type']; ?>
                                    <?php if ($log['target_id']): ?>
                                        <span class="text-gray-400">(#<?php echo $log['target_id']; ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <?php else: ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php if (!empty($log['request_id'])): ?>
                                        <a href="../requests/request_detail.php?id=<?php echo $log['request_id']; ?>" class="text-blue-600 hover:text-blue-900 transition-colors">
                                            <?php echo $log['request_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-6 py-4 text-sm text-gray-600"><?php echo $log['details']; ?></td>
                                <?php if ($type === 'admin'): ?>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo $log['ip_address']; ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $type === 'admin' ? '7' : '6'; ?>" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No log entries found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>