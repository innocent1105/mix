<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/admin_check.php';

// Check if user is admin
if (!is_admin()) {
    header('Location: ../dashboard.php');
    exit();
}

// Get comprehensive admin analytics data

// System Overview Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user WHERE is_active = 1");
$stmt->execute();
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ministry WHERE status = 'active'");
$stmt->execute();
$total_ministries = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM data_requests");
$stmt->execute();
$total_requests = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM log WHERE DATE(timestamp) = CURDATE()");
$stmt->execute();
$today_activities = $stmt->fetch()['total'];

// Request Analytics
$stmt = $pdo->prepare("
    SELECT 
        status,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM data_requests)), 1) as percentage
    FROM data_requests 
    GROUP BY status
");
$stmt->execute();
$request_status = $stmt->fetchAll();

// Data Type Analytics
$stmt = $pdo->prepare("
    SELECT 
        data_type,
        COUNT(*) as count,
        ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM data_requests)), 1) as percentage
    FROM data_requests 
    GROUP BY data_type
    ORDER BY count DESC
");
$stmt->execute();
$data_types = $stmt->fetchAll();

// Monthly Trends (Last 6 months)
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM data_requests 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$stmt->execute();
$monthly_trends = $stmt->fetchAll();

// Ministry Performance Analytics
$stmt = $pdo->prepare("
    SELECT 
        m.name as ministry_name,
        COUNT(dr.id) as total_requests,
        SUM(CASE WHEN dr.status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN dr.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN dr.status = 'pending' THEN 1 ELSE 0 END) as pending,
        ROUND((SUM(CASE WHEN dr.status = 'approved' THEN 1 ELSE 0 END) * 100.0 / COUNT(dr.id)), 1) as success_rate
    FROM ministry m
    LEFT JOIN data_requests dr ON m.id = dr.from_ministry_id
    GROUP BY m.id, m.name
    HAVING total_requests > 0
    ORDER BY total_requests DESC
");
$stmt->execute();
$ministry_performance = $stmt->fetchAll();

// User Activity Analytics
$stmt = $pdo->prepare("
    SELECT 
        u.name as user_name,
        u.email,
        m.name as ministry_name,
        COUNT(DISTINCT dr.id) as requests_made,
        COUNT(DISTINCT l.id) as total_actions,
        MAX(l.timestamp) as last_activity,
        u.created_at as joined_date
    FROM user u
    LEFT JOIN ministry m ON u.ministry_id = m.id
    LEFT JOIN data_requests dr ON u.id = dr.user_id
    LEFT JOIN log l ON u.id = l.user_id
    WHERE u.is_active = 1
    GROUP BY u.id, u.name, u.email, m.name, u.created_at
    ORDER BY total_actions DESC
    LIMIT 15
");
$stmt->execute();
$user_analytics = $stmt->fetchAll();

// System Health Metrics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM log WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$stmt->execute();
$daily_logs = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute();
$active_users_week = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM data_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$stmt->execute();
$weekly_requests = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Admin Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Admin Layout -->
    <div class="flex h-screen">
        <!-- Include Sidebar -->
        <?php include 'admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Admin Analytics</h2>
                        <p class="text-gray-600">Comprehensive system analytics and performance metrics</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500">Last updated: <?php echo date('M j, Y g:i A'); ?></span>
                    </div>
                </div>
            </header>

            <!-- Analytics Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- System Health Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Active Users</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_users; ?></p>
                                <p class="text-green-600 text-sm mt-2">
                                    <i class="fas fa-user-check mr-1"></i><?php echo $active_users_week; ?> active this week
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Total Requests</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_requests; ?></p>
                                <p class="text-blue-600 text-sm mt-2">
                                    <i class="fas fa-chart-line mr-1"></i><?php echo $weekly_requests; ?> this week
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">System Activities</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $today_activities; ?></p>
                                <p class="text-purple-600 text-sm mt-2">
                                    <i class="fas fa-bolt mr-1"></i><?php echo $daily_logs; ?> in 24h
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-activity text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Connected Ministries</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_ministries; ?></p>
                                <p class="text-orange-600 text-sm mt-2">
                                    <i class="fas fa-network-wired mr-1"></i>All active
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Request Status Distribution -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Status Distribution</h3>
                        <div class="h-64">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>

                    <!-- Data Type Distribution -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Type Distribution</h3>
                        <div class="h-64">
                            <canvas id="dataTypeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trends -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Trends (Last 6 Months)</h3>
                    <div class="h-80">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>

                <!-- Ministry Performance -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ministry Performance Analytics</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ministry</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Requests</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success Rate</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($ministry_performance as $ministry): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($ministry['ministry_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $ministry['total_requests']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                        <?php echo $ministry['approved']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                                        <?php echo $ministry['rejected']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-orange-600 font-medium">
                                        <?php echo $ministry['pending']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            <?php echo $ministry['success_rate'] >= 80 ? 'bg-green-100 text-green-800' : 
                                                  ($ministry['success_rate'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo $ministry['success_rate']; ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- User Activity Analytics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Active Users</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ministry</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Actions</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Active</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($user_analytics as $user): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-user text-blue-600 text-sm"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['user_name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($user['ministry_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium"><?php echo $user['requests_made']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium"><?php echo $user['total_actions']; ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo $user['last_activity'] ? date('M j, Y', strtotime($user['last_activity'])) : 'Never'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo date('M j, Y', strtotime($user['joined_date'])); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Request Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?php foreach($request_status as $status): ?>'<?php echo ucfirst($status['status']); ?>',<?php endforeach; ?>],
                    datasets: [{
                        data: [<?php foreach($request_status as $status): ?><?php echo $status['count']; ?>,<?php endforeach; ?>],
                        backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Data Type Chart
            const dataTypeCtx = document.getElementById('dataTypeChart').getContext('2d');
            new Chart(dataTypeCtx, {
                type: 'pie',
                data: {
                    labels: [<?php foreach($data_types as $type): ?>'<?php echo ucfirst($type['data_type']); ?>',<?php endforeach; ?>],
                    datasets: [{
                        data: [<?php foreach($data_types as $type): ?><?php echo $type['count']; ?>,<?php endforeach; ?>],
                        backgroundColor: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            // Monthly Trends Chart
            const trendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: [<?php foreach(array_reverse($monthly_trends) as $trend): ?>'<?php echo date('M Y', strtotime($trend['month'] . '-01')); ?>',<?php endforeach; ?>],
                    datasets: [
                        {
                            label: 'Total Requests',
                            data: [<?php foreach(array_reverse($monthly_trends) as $trend): ?><?php echo $trend['total_requests']; ?>,<?php endforeach; ?>],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Approved',
                            data: [<?php foreach(array_reverse($monthly_trends) as $trend): ?><?php echo $trend['approved']; ?>,<?php endforeach; ?>],
                            borderColor: '#10b981',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</body>
</html>