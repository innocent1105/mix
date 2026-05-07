<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: ../index.php");
    exit();
}

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center cursor-pointer" onclick="window.location.href='admin_dashboard.php'">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
                <div class="cursor-pointer" onclick="window.location.href='admin_dashboard.php'">
                    <h1 class="text-xl font-bold text-gray-900">MinistryLink Admin</h1>
                    <p class="text-xs text-gray-500">System Administration</p>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="admin_dashboard.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'admin_dashboard.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Dashboard</a>
                <a href="user_management.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'user_management.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Users</a>
                <a href="ministry_management.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'ministry_management.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Ministries</a>
                <a href="system_logs.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'system_logs.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">System Logs</a>
                <a href="system_settings.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'system_settings.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Settings</a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-shield text-blue-600 text-sm"></i>
                        </div>
                        <span class="hidden sm:block font-medium"><?php echo $_SESSION['user_name']; ?> (Admin)</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    
                    <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 hidden">
                        <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                            <p class="font-medium"><?php echo $_SESSION['user_name']; ?></p>
                            <p class="text-xs text-gray-500">Administrator</p>
                        </div>
                        <a href="../dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i>User Dashboard
                        </a>
                        <a href="system_settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-cog mr-2"></i>System Settings
                        </a>
                        <hr class="my-2 border-gray-200">
                        <a href="../includes/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
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
            <a href="admin_dashboard.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'admin_dashboard.php' ? 'text-blue-600 font-medium' : ''; ?>">Dashboard</a>
            <a href="user_management.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'user_management.php' ? 'text-blue-600 font-medium' : ''; ?>">Users</a>
            <a href="ministry_management.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'ministry_management.php' ? 'text-blue-600 font-medium' : ''; ?>">Ministries</a>
            <a href="system_logs.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'system_logs.php' ? 'text-blue-600 font-medium' : ''; ?>">System Logs</a>
            <a href="system_settings.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'system_settings.php' ? 'text-blue-600 font-medium' : ''; ?>">Settings</a>
            <hr class="my-2 border-gray-200">
            <a href="../dashboard.php" class="block text-gray-600 hover:text-blue-600 transition-colors">User Dashboard</a>
            <a href="../includes/logout.php" class="block text-red-600 hover:text-red-800 transition-colors">Logout</a>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        var mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });

    // User dropdown menu toggle
    document.getElementById('user-menu-button').addEventListener('click', function(e) {
        e.stopPropagation();
        var userMenu = document.getElementById('user-menu');
        userMenu.classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var userMenu = document.getElementById('user-menu');
        var userMenuButton = document.getElementById('user-menu-button');
        
        if (userMenu && !userMenu.contains(e.target) && e.target !== userMenuButton && !userMenuButton.contains(e.target)) {
            userMenu.classList.add('hidden');
        }
    });

    // Close dropdown when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var userMenu = document.getElementById('user-menu');
            if (userMenu) {
                userMenu.classList.add('hidden');
            }
        }
    });
</script>

<style>
    /* Smooth transitions for dropdown */
    #user-menu {
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
        opacity: 0;
        transform: translateY(-10px);
        pointer-events: none;
    }
    
    #user-menu:not(.hidden) {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
</style>