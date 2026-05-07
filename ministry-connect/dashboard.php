<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth_check.php';

// Get statistics for dashboard
$user_id = $_SESSION['user_id'];
$ministry_id = $_SESSION['ministry_id'];

// Total requests
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM data_requests WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_requests = $stmt->fetch()['total'];

// Pending requests
$stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM data_requests WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending_requests = $stmt->fetch()['pending'];

// Requests to approve
$stmt = $pdo->prepare("SELECT COUNT(*) as to_approve FROM data_requests WHERE to_ministry_id = ? AND status = 'pending'");
$stmt->execute([$ministry_id]);
$to_approve = $stmt->fetch()['to_approve'];

// Recent requests
$stmt = $pdo->prepare("
    SELECT dr.*, m1.name as from_ministry_name, m2.name as to_ministry_name 
    FROM data_requests dr 
    JOIN ministry m1 ON dr.from_ministry_id = m1.id 
    JOIN ministry m2 ON dr.to_ministry_id = m2.id 
    WHERE dr.user_id = ? 
    ORDER BY dr.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_requests = $stmt->fetchAll();

// Get active ministries count - handle case where status column might not exist
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_ministries FROM ministry WHERE status = 'active'");
    $stmt->execute();
    $active_ministries = $stmt->fetch()['active_ministries'];
} catch (PDOException $e) {
    // If status column doesn't exist, count all ministries
    $stmt = $pdo->prepare("SELECT COUNT(*) as active_ministries FROM ministry");
    $stmt->execute();
    $active_ministries = $stmt->fetch()['active_ministries'];
}
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
  
    <style>
        /* Include your styles from try.html or link to external CSS */
        <?php include_once 'assets/css/style.css'; ?>
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center cursor-pointer" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <div class="cursor-pointer" onclick="window.location.href='dashboard.php'">
                        <h1 class="text-xl font-bold text-gray-900">MinistryLink</h1>
                        <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="dashboard.php" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Dashboard</a>
                    <a href="modules/requests/view_requests.php" class="text-gray-600 hover:text-blue-600 transition-colors">Requests</a>
                    <a href="modules/logs/view_logs.php" class="text-gray-600 hover:text-blue-600 transition-colors">Audit Logs</a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-blue-600 text-sm"></i>
                            </div>
                            <span class="hidden sm:block font-medium"><?php echo $_SESSION['user_name']; ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                <p class="font-medium"><?php echo $_SESSION['user_name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></p>
                            </div>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-user-circle mr-2"></i>Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-cog mr-2"></i>Settings
                            </a>
                            <hr class="my-2 border-gray-200">
                            <a href="includes/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-blue-600" id="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="hidden md:hidden bg-white border-t border-gray-200" id="mobile-menu">
            <div class="px-4 py-3 space-y-3">
                <a href="dashboard.php" class="block text-blue-600 font-medium">Dashboard</a>
                <a href="modules/requests/view_requests.php" class="block text-gray-600 hover:text-blue-600 transition-colors">Requests</a>
                <a href="modules/logs/view_logs.php" class="block text-gray-600 hover:text-blue-600 transition-colors">Audit Logs</a>
                <hr class="my-2 border-gray-200">
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors">Profile</a>
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors">Settings</a>
                <a href="includes/logout.php" class="block text-red-600 hover:text-red-800 transition-colors">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Overview</h2>
                    <p class="text-gray-600">Monitor and manage inter-ministry data exchange requests</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="tasha.pdf" download class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i> Download PDF
                    </a>
                    <a href="modules/requests/create_request.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="fas fa-plus mr-2"></i>New Request
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Requests</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $total_requests; ?></p>
                        <p class="text-green-600 text-sm mt-2">
                            <i class="fas fa-arrow-up mr-1"></i>12% from last month
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Pending Approval</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $pending_requests; ?></p>
                        <p class="text-orange-600 text-sm mt-2">
                            <i class="fas fa-clock mr-1"></i>Requires attention
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-hourglass-half text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">To Approve</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $to_approve; ?></p>
                        <p class="text-green-600 text-sm mt-2">
                            <i class="fas fa-check mr-1"></i>93% success rate
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Active Ministries</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $active_ministries; ?></p>
                        <p class="text-blue-600 text-sm mt-2">
                            <i class="fas fa-building mr-1"></i>All connected
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Requests Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Requests</h3>
                    <a href="modules/requests/view_requests.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Ministry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($recent_requests) > 0): ?>
                            <?php foreach ($recent_requests as $request): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $request['request_id']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $request['to_ministry_name']; ?></td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate"><?php echo $request['purpose']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $status_class = '';
                                    switch($request['status']) {
                                        case 'pending': $status_class = 'bg-yellow-100 text-yellow-800'; break;
                                        case 'approved': $status_class = 'bg-green-100 text-green-800'; break;
                                        case 'rejected': $status_class = 'bg-red-100 text-red-800'; break;
                                        case 'completed': $status_class = 'bg-blue-100 text-blue-800'; break;
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                        <i class="fas mr-1 
                                            <?php 
                                            switch($request['status']) {
                                                case 'pending': echo 'fa-clock'; break;
                                                case 'approved': echo 'fa-check'; break;
                                                case 'rejected': echo 'fa-times'; break;
                                                case 'completed': echo 'fa-check-double'; break;
                                            }
                                            ?>
                                        "></i>
                                        <span><?php echo ucfirst($request['status']); ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="modules/requests/request_detail.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-900 transition-colors mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($request['status'] == 'pending'): ?>
                                    <a href="modules/requests/create_request.php?edit=<?php echo $request['id']; ?>" class="text-gray-600 hover:text-gray-900 transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No requests found. <a href="modules/requests/create_request.php" class="text-blue-600 hover:text-blue-800">Create your first request</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($recent_requests) > 0): ?>
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p class="text-sm text-gray-600">Showing <?php echo count($recent_requests); ?> of <?php echo $total_requests; ?> results</p>
                <div class="flex items-center space-x-2">
                    <button class="px-3 py-1 text-sm text-gray-600 hover:text-blue-600 transition-colors">Previous</button>
                    <button class="px-3 py-1 text-sm bg-blue-600 text-white rounded">1</button>
                    <button class="px-3 py-1 text-sm text-gray-600 hover:text-blue-600 transition-colors">Next</button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Quick Request</h3>
                    <i class="fas fa-plus-circle text-2xl opacity-80"></i>
                </div>
                <p class="text-blue-100 mb-4">Submit a new data exchange request quickly</p>
                <a href="modules/requests/create_request.php" class="inline-block bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors">
                    Create Request
                </a>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Ministry Directory</h3>
                    <i class="fas fa-building text-2xl opacity-80"></i>
                </div>
                <p class="text-green-100 mb-4">Browse all connected ministries and contacts</p>
                <a href="ministries.php" class="inline-block bg-white text-green-600 px-4 py-2 rounded-lg font-medium hover:bg-green-50 transition-colors">
                    View Directory
                </a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">MinistryLink</p>
                        <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 text-sm text-gray-600">
                    <a href="#" class="hover:text-blue-600 transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-blue-600 transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-blue-600 transition-colors">Support</a>
                    <span>© <?php echo date('Y'); ?> Government Portal</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            var mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Alpine.js for dropdown functionality
        document.addEventListener('alpine:init', () => {
            Alpine.data('dropdown', () => ({
                open: false,
                toggle() {
                    this.open = !this.open;
                }
            }));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>