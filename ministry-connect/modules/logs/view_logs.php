<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$ministry_id = $_SESSION['ministry_id'];

// Get filter parameters
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query - check if timestamp column exists, otherwise use created_at
try {
    // Test if timestamp column exists
    $test_stmt = $pdo->query("SELECT timestamp FROM log LIMIT 1");
    $timestamp_column = 'timestamp';
} catch (PDOException $e) {
    // If timestamp column doesn't exist, use created_at instead
    $timestamp_column = 'created_at';
}

$query = "
    SELECT l.*, u.name as user_name, m.name as ministry_name, dr.request_id
    FROM log l
    JOIN user u ON l.user_id = u.id
    JOIN ministry m ON u.ministry_id = m.id
    LEFT JOIN data_requests dr ON l.request_id = dr.id
    WHERE u.ministry_id = ?
";

$params = [$ministry_id];

if (!empty($action_filter)) {
    $query .= " AND l.action = ?";
    $params[] = $action_filter;
}

if (!empty($date_from)) {
    $query .= " AND DATE(l.$timestamp_column) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(l.$timestamp_column) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY l.$timestamp_column DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Get distinct actions for filter dropdown
$actions = $pdo->query("SELECT DISTINCT action FROM log ORDER BY action")->fetchAll();

// Count logs by action type for statistics - FIXED THE ERROR
$action_counts = [];
foreach ($actions as $action) {
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM log WHERE action = ? AND user_id IN (SELECT id FROM user WHERE ministry_id = ?)");
    $count_stmt->execute([$action['action'], $ministry_id]);
    $action_counts[$action['action']] = $count_stmt->fetchColumn();
}

// Get total counts for statistics
$total_logs = count($logs);
$login_count = $action_counts['login'] ?? 0;
$request_count = ($action_counts['request_created'] ?? 0) + ($action_counts['request_updated'] ?? 0);
$security_count = ($action_counts['password_reset'] ?? 0) + ($action_counts['access_denied'] ?? 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Audit Logs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #334155;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        
        .filter-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .table-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table-row:hover {
            background: #f8fafc;
        }
        
        .action-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .nav-link {
            color: #64748b;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        .log-details {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        @media (max-width: 768px) {
            .log-details {
                max-width: 150px;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-white border-r border-gray-200 hidden md:block">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">MinistryLink</h2>
                <nav class="space-y-1">
                    <a href="../requests/view_requests.php" class="nav-link flex items-center">
                        <i class="fas fa-exchange-alt mr-3"></i>
                        Data Requests
                    </a>
                    <a href="#" class="nav-link active flex items-center">
                        <i class="fas fa-clipboard-list mr-3"></i>
                        Audit Logs
                    </a>
                    <!-- <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        Data Repository
                    </a> -->
                    <a href="../../ministries.php" class="nav-link flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        Ministries
                    </a>
                
                    <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-cog mr-3"></i>
                        Settings
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="flex-1 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
                    <p class="text-gray-600 mt-1">Monitor all system activities and data access</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button class="btn-secondary inline-flex items-center" onclick="window.print()">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <button class="btn-secondary inline-flex items-center" onclick="exportLogs()">
                        <i class="fas fa-download mr-2"></i> Export
                    </button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stat-card card p-4 border-l-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clipboard-list text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Logs</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $total_logs; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-check text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Login Activities</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $login_count; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exchange-alt text-purple-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Request Activities</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $request_count; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-orange-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-key text-orange-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Security Actions</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $security_count; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Logs</h3>
                <form method="GET" action="">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Action Type</label>
                            <select name="action" class="filter-select">
                                <option value="">All Actions</option>
                                <?php foreach ($actions as $action): ?>
                                    <option value="<?php echo $action['action']; ?>" <?php echo $action_filter == $action['action'] ? 'selected' : ''; ?>>
                                        <?php echo ucwords(str_replace('_', ' ', $action['action'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from" class="filter-select" value="<?php echo $date_from; ?>">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to" class="filter-select" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit" class="btn-primary w-full">
                                Apply Filters
                            </button>
                            <a href="view_logs.php" class="btn-secondary w-full text-center">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Activity Log</h3>
                    <p class="text-sm text-gray-600 mt-2 sm:mt-0"><?php echo $total_logs; ?> log entries found</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                <tr class="table-row">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php 
                                        $date_column = isset($log['timestamp']) ? $log['timestamp'] : $log['created_at'];
                                        echo date('M j, Y', strtotime($date_column)); 
                                        ?>
                                        <div class="text-xs text-gray-400">
                                            <?php echo date('g:i A', strtotime($date_column)); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $log['user_name']; ?></div>
                                        <div class="text-xs text-gray-500"><?php echo $log['ministry_name']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $action_class = '';
                                        $action_icon = '';
                                        switch($log['action']) {
                                            case 'login':
                                                $action_class = 'bg-green-100 text-green-800';
                                                $action_icon = 'fas fa-sign-in-alt';
                                                break;
                                            case 'logout':
                                                $action_class = 'bg-gray-100 text-gray-800';
                                                $action_icon = 'fas fa-sign-out-alt';
                                                break;
                                            case 'request_created':
                                            case 'request_updated':
                                                $action_class = 'bg-blue-100 text-blue-800';
                                                $action_icon = 'fas fa-exchange-alt';
                                                break;
                                            case 'password_reset':
                                                $action_class = 'bg-orange-100 text-orange-800';
                                                $action_icon = 'fas fa-key';
                                                break;
                                            case 'access_denied':
                                                $action_class = 'bg-red-100 text-red-800';
                                                $action_icon = 'fas fa-ban';
                                                break;
                                            default:
                                                $action_class = 'bg-gray-100 text-gray-800';
                                                $action_icon = 'fas fa-info-circle';
                                        }
                                        ?>
                                        <span class="action-badge <?php echo $action_class; ?>">
                                            <i class="<?php echo $action_icon; ?> mr-1"></i>
                                            <span><?php echo ucwords(str_replace('_', ' ', $log['action'])); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if (!empty($log['request_id'])): ?>
                                            <a href="../requests/request_detail.php?id=<?php echo $log['request_id']; ?>" class="text-blue-600 hover:text-blue-800 transition-colors inline-flex items-center">
                                                <?php echo $log['request_id']; ?>
                                                <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 log-details" title="<?php echo htmlspecialchars($log['details']); ?>">
                                        <?php echo htmlspecialchars($log['details']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                                            <p class="text-lg font-medium">No log entries found</p>
                                            <p class="text-sm mt-1">Try adjusting your filters or check back later</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination would go here -->
                <?php if (count($logs) > 0): ?>
                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span class="font-medium"><?php echo count($logs); ?></span> of <span class="font-medium"><?php echo count($logs); ?></span> results
                    </div>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 rounded-md bg-gray-100 text-gray-600 text-sm font-medium disabled:opacity-50" disabled>
                            Previous
                        </button>
                        <button class="px-3 py-1 rounded-md bg-gray-100 text-gray-600 text-sm font-medium disabled:opacity-50" disabled>
                            Next
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Export logs function (placeholder)
        function exportLogs() {
            alert('Export functionality would be implemented here. This would download the filtered logs as a CSV file.');
        }
    </script>
</body>
</html>