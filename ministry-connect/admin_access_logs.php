<?php

    session_start();
    require_once 'config/database.php';

    // requests

    $admin_email = $_SESSION['user_email'];
    $role = $_SESSION['user_role'];

    if($role !== "admin"){
        header("header: index.php");
    }


    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch();



    $stmt = $pdo->prepare("SELECT * FROM data_requests");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $number_of_requests = 0;
    foreach($requests as $request) {
        $number_of_requests++;
    }


    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE status = :status");
    $stmt->execute(['status' => 'pending']);
    $number_of_pending_requests = $stmt->rowCount();

    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE status = :status");
    $stmt->execute(['status' => 'approved']);
    $number_of_approved_requests = $stmt->rowCount();

    $stmt = $pdo->prepare("SELECT * FROM ministries WHERE status = :status");
    $stmt->execute(['status' => 'active']);
    $number_of_active_ministries = $stmt->rowCount();

    $notifications = [1,2,3,4];

    $stmt = $pdo->prepare("SELECT * FROM notification");
    $stmt->execute();
    $notifications = $stmt->fetchAll();


   

    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.role, u.is_active, m.name AS ministry
        FROM user u
        LEFT JOIN ministry m ON u.ministry_id = m.id
        ORDER BY u.id ASC
    ");

    $users = $stmt->fetchAll();


    // Handle delete request
    if (isset($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $pdo->prepare("DELETE FROM log WHERE id = ?");
        $stmt->execute([$delete_id]);
    }

    // Fetch logs with user names
    $stmt = $pdo->query("
        SELECT log.id, log.action, log.details, log.date, log.user_id, u.name AS user_name
        FROM log
        JOIN user u ON log.user_id = u.id
        ORDER BY log.date DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // =========================================

    // Fetch request counts by status
$chart_statusStmt = $pdo->query("
    SELECT status, COUNT(*) as count
    FROM data_requests
    GROUP BY status
");
$chart_statusData = $chart_statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch monthly request counts (last 6 months)
$chart_monthlyStmt = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count
    FROM data_requests
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY MIN(created_at)
");
$chart_monthlyData = $chart_monthlyStmt->fetchAll(PDO::FETCH_KEY_PAIR);


    

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background-color: #e5e7eb;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease-in-out;
        }
        
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        
        .ministry-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        
        .ministry-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .ministry-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .ministry-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .ministry-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .ministry-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        
        .trend-up {
            color: #10b981;
        }
        
        .trend-down {
            color: #ef4444;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .dashboard-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        
        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(to right, #f9fafb, #ffffff);
        }
        
        .chart-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
        }
        
        .stat-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .filter-btn {
            transition: all 0.2s ease;
        }
        
        .filter-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center cursor-pointer">
                        <i class="fas fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <div class="cursor-pointer">
                        <h1 class="text-xl font-bold text-gray-900">MinistryLink</h1>
                        <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <!-- <a href="#" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Dashboard</a> -->
                </div>

                   <div class="flex items-center space-x-4">

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="relative text-gray-700 hover:text-blue-600 transition-colors duration-200 p-2">
                        <i class="fas fa-bell text-xl"></i>
                        <?php if (!empty($notifications)): ?>
                            <span class="notification-dot absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                        <?php endif; ?>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50 max-h-96 overflow-y-auto">
                        
                        <div class="px-4 py-2 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Notifications</h3>
                        </div>

                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                                    <p class="text-sm text-gray-800"><?= htmlspecialchars($notification['message']) ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?= date('M j, Y g:i A', strtotime($notification['created_at'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                            <div class="px-4 py-2 text-center">
                                <a href="#" class="text-blue-600 text-sm hover:text-blue-800 font-medium">View All</a>
                            </div>
                        <?php else: ?>
                            <div class="px-4 py-4 text-center">
                                <p class="text-gray-500 text-sm">No new notifications</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                        <span class="hidden sm:block font-medium"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator') ?></span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                        <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                            <p class="font-medium"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator') ?></p>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($_SESSION['user_role'] ?? 'Admin') ?></p>
                        </div>
                
                        <a href="includes/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>

            </div>

                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-blue-600 transition-colors duration-200" id="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="hidden md:hidden bg-white border-t border-gray-200" id="mobile-menu">
            <div class="px-4 py-3 space-y-3">
                <!-- <a href="#" class="block text-blue-600 font-medium">Dashboard</a> -->

                <hr class="my-2 border-gray-200">
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors duration-200">Profile</a>
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors duration-200">Settings</a>
                <a href="#" class="block text-red-600 hover:text-red-800 transition-colors duration-200">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
       

      


<div class="mt-10 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-clipboard-list text-blue-600 mr-3"></i>
                Audit Logs
            </h2>
            <p class="text-gray-500 mt-1">Track every critical system action performed by users</p>
        </div>

        <div class="mt-3 sm:mt-0">
            <button class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-sync-alt mr-2"></i> Refresh Logs
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm">
        <table class="min-w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Details</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-center">Delete</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900"><?= $log['id'] ?></td>
                        <td class="px-4 py-3"><?= htmlspecialchars($log['user_name']) ?></td>
                        <td class="px-4 py-3 text-blue-700 font-semibold"><?= htmlspecialchars($log['action']) ?></td>
                        <td class="px-4 py-3 text-gray-600"><?= htmlspecialchars($log['details']) ?></td>
                        <td class="px-4 py-3 text-gray-500"><?= $log['date'] ?></td>
                        <td class="px-4 py-3 text-center">
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this log?');" class="inline-block">
                                <input type="hidden" name="delete_id" value="<?= $log['id'] ?>">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium shadow-sm hover:shadow-md transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500 italic">No audit logs available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center mt-6 text-sm text-gray-500">
        <p>Showing <?= count($logs) ?> logs</p>
        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">View full history →</a>
    </div>
</div>





   <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 my-2">
            <!-- Request Status Chart -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Request Status Distribution</h3>
                    <div class="flex space-x-2 mt-2 sm:mt-0">
                        <button class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg font-medium filter-btn">Status</button>
                        <button class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg font-medium filter-btn">Priority</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>



            <!-- Monthly Request Trend -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Monthly Request Trend</h3>
                    <div class="flex space-x-2 mt-2 sm:mt-0">
                        <button class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg font-medium filter-btn">6 Months</button>
                        <button class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg font-medium filter-btn">1 Year</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>







 

  
      
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <div class="w-8 h-8 gradient-bg rounded-lg flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">MinistryLink</p>
                        <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-600">
                    <a href="#" class="hover:text-blue-600 transition-colors duration-200">Privacy Policy</a>
                    <a href="#" class="hover:text-blue-600 transition-colors duration-200">Terms of Service</a>
                    <a href="#" class="hover:text-blue-600 transition-colors duration-200">Support</a>
                    <span>© 2023 Government Portal</span>
                </div>
            </div>
        </div>
    </footer>


    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const chart_statusCtx = document.getElementById('statusChart').getContext('2d');
    const chart_status = new Chart(chart_statusCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($chart_statusData)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($chart_statusData)) ?>,
                backgroundColor: [
                    'rgba(255, 209, 69, 0.46)',   // pending
                    'rgba(10, 176, 101, 0.8)',   // approved
                    'rgba(220, 53, 69, 0.8)',   // rejected
                    'rgba(2, 104, 212, 0.7)'    // completed
                ],
                borderColor: [
                    'rgba(255, 205, 54, 0.64)',
                    'rgba(4, 174, 118, 1)',
                    'rgb(220, 53, 69)',
                    'rgba(0, 96, 239, 0.73)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true } }
            }
        }
    });

    // Monthly Trend Chart
    const chart_monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const chart_monthly = new Chart(chart_monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($chart_monthlyData)) ?>,
            datasets: [{
                label: 'Requests',
                data: <?= json_encode(array_values($chart_monthlyData)) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: 'rgba(0, 123, 255, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 10 }, grid: { drawBorder: false } },
                x: { grid: { display: false } }
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>








