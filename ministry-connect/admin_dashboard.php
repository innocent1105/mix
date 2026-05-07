<?php

    session_start();
    require_once 'config/database.php';

    // requests

    $admin_email = $_SESSION['user_email'];
    $role = $_SESSION['user_role'];

    if($role !== "admin"){
        header("header: index.php");
    }


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


    $mp_ministriesStmt = $pdo->query("SELECT id, name, status FROM ministries ORDER BY name");
    $mp_ministries = $mp_ministriesStmt->fetchAll(PDO::FETCH_ASSOC);


    $mp_stats = [];
foreach ($mp_ministries as $mp_min) {
    $min_id = $mp_min['id'];
    
    // Total requests from this ministry
    $stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM data_requests WHERE from_ministry_id = ?");
    $stmt_total->execute([$min_id]);
    $mp_total = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Approved requests
    $stmt_approved = $pdo->prepare("SELECT COUNT(*) as approved FROM data_requests WHERE from_ministry_id = ? AND status='approved'");
    $stmt_approved->execute([$min_id]);
    $mp_approved = $stmt_approved->fetch(PDO::FETCH_ASSOC)['approved'] ?? 0;
    
    // Average response time in hours
    $stmt_avg = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours
        FROM data_requests
        WHERE from_ministry_id = ? AND updated_at IS NOT NULL
    ");
    $stmt_avg->execute([$min_id]);
    $mp_avgResponse = round($stmt_avg->fetch(PDO::FETCH_ASSOC)['avg_hours'] ?? 0);
    
    // Approval percentage
    $mp_approvalRate = $mp_total ? round(($mp_approved / $mp_total) * 100) : 0;
    
    $mp_stats[] = [
        'name' => $mp_min['name'],
        'status' => $mp_min['status'],
        'total' => $mp_total,
        'approved' => $mp_approved,
        'approvalRate' => $mp_approvalRate,
        'avgResponse' => $mp_avgResponse
    ];
}



    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.role, u.is_active, m.name AS ministry
        FROM user u
        LEFT JOIN ministry m ON u.ministry_id = m.id
        ORDER BY u.id ASC
    ");

    $users = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE status = :status");
    $stmt->execute(['status' => 'approved']);
    $number_of_approved_requests = $stmt->rowCount();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .dashboard-bg {
            background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            transition: all 0.3s ease;
        }
        .main-content {
            margin-left: 260px;
            transition: all 0.3s ease;
        }
        .collapsed .sidebar {
            width: 80px;
        }
        .collapsed .main-content {
            margin-left: 80px;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar bg-white shadow-lg fixed h-full">
            <div class="p-5 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">MinistryLink</h1>
                        <p class="text-xs text-gray-500">Admin Portal</p>
                    </div>
                </div>
            </div>
            
            <nav class="mt-6">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Main
                </div>
                <a href="#" class="flex items-center px-6 py-3 text-blue-600 bg-blue-50 border-r-4 border-blue-600">
                    <i class="fas fa-home mr-4"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Management
                </div>
                <a href="user_managenment.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-users mr-4"></i>
                    <span>User Management</span>
                </a>
                <!-- <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-user-shield mr-4"></i>
                    <span>Roles & Permissions</span>
                </a> -->
                <a href="ministry_management.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-building mr-4"></i>
                    <span>Ministry Management</span>
                </a>
                
                <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    System
                </div>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-cog mr-4"></i>
                    <span>System Settings</span>
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-database mr-4"></i>
                    <span>Data Sources</span>
                </a>
                <a href="admin_access_logs.php" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-history mr-4"></i>
                    <span>Audit Logs</span>
                </a>
                <!-- <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-chart-line mr-4"></i>
                    <span>Analytics</span>
                </a>
                 -->
                <div class="px-4 py-2 mt-6 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Support
                </div>
                <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-question-circle mr-4"></i>
                    <span>Help & Support</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content w-full">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-6 py-4">
                    <div class="flex items-center">
                        <!-- <button id="sidebar-toggle" class="text-gray-500 mr-4 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button> -->
                        <h2 class="text-2xl font-semibold text-gray-800">Admin Dashboard</h2>
                    </div>
                    
                    
                    <div class=" flex flex-row justify-end gap-2">

                       <a href="tasha.pdf" download class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                            <i class="fas fa-download mr-2"></i> Download PDF
                        </a>
                        
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
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">
                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Approved requests</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">
                                    <?php echo  $number_of_approved_requests; ?>
                                </p>
                                <p class="text-green-600 text-sm mt-2">
                                    <i class="fas fa-arrow-up mr-1"></i>12.5% from last month
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Data Requests</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $number_of_requests; ?></p>
                                <p class="text-green-600 text-sm mt-2">
                                    <i class="fas fa-arrow-up mr-1"></i>8.3% from last month
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">System Uptime</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">99.98%</p>
                                <p class="text-green-600 text-sm mt-2">
                                    <i class="fas fa-check-circle mr-1"></i>All systems operational
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-server text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 card-hover">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-500 text-sm font-medium">Pending Approvals</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">
                                    <?php echo $number_of_pending_requests; ?>
                                </p>
                                <p class="text-red-600 text-sm mt-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Needs attention
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-orange-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Activity Row -->
                <div class=" flex flex-row justify-between mb-8">
                    <!-- Activity Chart -->





                    <div class="bg-white w-full rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Ministry Performance</h3>
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 sm:mt-0 filter-btn">
                                View Detailed Report <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ministry</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Rate</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Response</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach($mp_stats as $stat): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($stat['name']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?= $stat['total'] ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                                    <div class="bg-green-600 h-2 rounded-full" style="width: <?= $stat['approvalRate'] ?>%"></div>
                                                </div>
                                                <span><?= $stat['approvalRate'] ?>%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?= $stat['avgResponse'] ?> hrs</td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if($stat['status'] === 'active'): ?>
                                                <span class="status-badge rounded-full p-1 text-sm bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                            <?php else: ?>
                                                <span class="status-badge rounded-full bg-red-100 p-1 text-sm text-red-800"><i class="fas fa-times-circle mr-1"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>







                    
                 
                </div>

                <!-- User Management and System Health -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Recent Users -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Users</h3>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <?php 
                                foreach($users as $index => $user): ?>

                                <div class="px-6 py-4 flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                        <i class="fas fa-user text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($user['name']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($user['ministry'] ?? '-') ?></p>
                                    </div>
                                    <span class="<?= $user['is_active'] ? 'text-green-600' : 'text-red-600' ?> font-semibold">
                                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>


                                    <div class="p-3 text-center space-x-2">
                                        <a href="admin_edit_user.php?id=<?= $user['id'] ?>" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200">Edit</a>
                                        <?php 
                                            if($user['is_active'] != "0"){
                                        ?>
                                            <a href="admin_delete_user.php?id=<?= $user['id'] ?>" 
                                                class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded-md hover:bg-red-200">
                                                Deactivate
                                            </a>
                                        <?php
                                            }else{
                                        ?>
                                            <a href="#" 
                                                class="px-3 py-1 text-xs bg-gray-100 text-gray-400 cursor-default rounded-md hover:bg-gray-200">
                                                Deactivated
                                            </a>
                                        <?php
                                            }
                                        ?>
                                    

                                    </div>
                                </div>
                            
                            <?php endforeach; ?>
                                                    


                        </div>
                    </div>
                    
                    <!-- System Health -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-800">System Health</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">CPU Usage</span>
                                    <span class="text-sm font-medium text-gray-700">62%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: 62%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Memory Usage</span>
                                    <span class="text-sm font-medium text-gray-700">78%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-orange-500 h-2.5 rounded-full" style="width: 78%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Disk Space</span>
                                    <span class="text-sm font-medium text-gray-700">45%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 45%"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Network Traffic</span>
                                    <span class="text-sm font-medium text-gray-700">32%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-purple-600 h-2.5 rounded-full" style="width: 32%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Quick Actions</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="#" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2">
                                <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-800">Add User</span>
                        </a>
                        
                        <a href="#" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2">
                                <i class="fas fa-cog text-green-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-800">System Settings</span>
                        </a>
                        
                        <a href="#" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-2">
                                <i class="fas fa-database text-purple-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-800">Data Sources</span>
                        </a>
                        
                        <a href="#" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-2">
                                <i class="fas fa-file-export text-orange-600 text-xl"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-800">Generate Reports</span>
                        </a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.body.classList.toggle('collapsed');
        });

        // Toggle user menu
        document.getElementById('user-menu-button').addEventListener('click', function() {
            document.getElementById('user-menu').classList.toggle('hidden');
        });

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('user-menu');
            const userMenuButton = document.getElementById('user-menu-button');
            
            if (!userMenu.contains(e.target) && e.target !== userMenuButton && !userMenuButton.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });
    </script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>  