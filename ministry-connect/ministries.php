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
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Ministries</h2>
                    <p class="text-gray-600">Registered ministries</p>
                </div>
               
            </div>
        </div>


        <!-- Ministries Table -->

        <!-- Ministries Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
            <i class="fas fa-university text-blue-600 mr-2"></i>
            Registered Ministries
        </h3>

        <div class="mt-3 sm:mt-0 flex items-center space-x-3">
            <div class="relative">
                <input type="text" placeholder="Search ministries..." 
                    class="pl-10 pr-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm w-64 transition" 
                    id="searchInput">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
            </div>

            <button class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add Ministry
            </button>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-100">
        <table class="min-w-full text-sm text-left text-gray-600">
            <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold border-b">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Ministry Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Contact</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                $stmt = $pdo->prepare("SELECT * FROM ministry ORDER BY id DESC");
                $stmt->execute();
                $ministries = $stmt->fetchAll();

                if ($ministries):
                    foreach ($ministries as $index => $ministry): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900"><?= $index + 1 ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($ministry['name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($ministry['email'] ?? '—') ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($ministry['contact_person'] ?? '—') ?></td>
                            <td class="px-4 py-3">
                                <?php if (($ministry['status'] ?? 'active') === 'active'): ?>
                                    <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Active</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">Inactive</span>
                                <?php endif; ?>
                            </td>
                          
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500 italic">No ministries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-6 text-sm text-gray-500">
        <p>Showing <?= count($ministries) ?> ministries</p>
        <a href="#" class="text-blue-600 hover:text-blue-700 font-medium">View details →</a>
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
        // Basic search filter
        document.getElementById('searchInput').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>


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