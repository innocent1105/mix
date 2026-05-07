<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Determine the correct path to index.php based on current location
    $path_to_index = (strpos($_SERVER['PHP_SELF'], 'modules/') !== false) ? '../../index.php' : '../index.php';
    header("Location: " . $path_to_index);
    exit();
}

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Determine the correct base path for links based on current location
$is_in_modules = (strpos($_SERVER['PHP_SELF'], 'modules/') !== false);
$base_path = $is_in_modules ? '../../' : '../';

// Check if user is admin
$is_admin = ($_SESSION['user_role'] === 'Administrator');
?>

<nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center cursor-pointer" onclick="window.location.href='<?php echo $base_path; ?>dashboard.php'">
                    <i class="fas fa-exchange-alt text-white text-lg"></i>
                </div>
                <div class="cursor-pointer" onclick="window.location.href='<?php echo $base_path; ?>dashboard.php'">
                    <h1 class="text-xl font-bold text-gray-900">MinistryLink</h1>
                    <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
              
                <a href="<?php echo $base_path; ?>modules/requests/view_requests.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'view_requests.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Requests</a>
                <a href="<?php echo $base_path; ?>modules/logs/view_logs.php" class="text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'view_logs.php' ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">Audit Logs</a>
                
                <!-- Admin Links - Only visible to administrators -->
                <?php if ($is_admin): ?>
                <div class="relative group">
                    <button class="text-gray-600 hover:text-blue-600 transition-colors flex items-center <?php echo (in_array($current_page, ['user_management.php', 'system_settings.php', 'data_sources.php'])) ? 'text-blue-600 font-medium border-b-2 border-blue-600 pb-1' : ''; ?>">
                        <span>Admin</span>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <a href="<?php echo $base_path; ?>modules/admin/user_management.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors <?php echo $current_page == 'user_management.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-users mr-2"></i>User Management
                        </a>
                        <a href="<?php echo $base_path; ?>modules/admin/system_settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors <?php echo $current_page == 'system_settings.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-cog mr-2"></i>System Settings
                        </a>
                        <a href="<?php echo $base_path; ?>modules/admin/data_sources.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors <?php echo $current_page == 'data_sources.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                            <i class="fas fa-database mr-2"></i>Data Sources
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-blue-600 text-sm"></i>
                        </div>
                        <span class="hidden sm:block font-medium"><?php echo $_SESSION['user_name']; ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    
                    <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50 hidden">
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
                        
                        <!-- Admin Links in User Dropdown - Only visible to administrators -->
                        <?php if ($is_admin): ?>
                        <hr class="my-2 border-gray-200">
                        <div class="px-3 py-1 text-xs font-semibold text-gray-400">Admin Panel</div>
                        <a href="<?php echo $base_path; ?>modules/admin/user_management.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-users mr-2"></i>User Management
                        </a>
                        <a href="<?php echo $base_path; ?>modules/admin/system_settings.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-cog mr-2"></i>System Settings
                        </a>
                        <a href="<?php echo $base_path; ?>modules/admin/data_sources.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-database mr-2"></i>Data Sources
                        </a>
                        <?php endif; ?>
                        
                        <hr class="my-2 border-gray-200">
                        <a href="<?php echo $base_path; ?>includes/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
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
            <a href="<?php echo $base_path; ?>dashboard.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'dashboard.php' ? 'text-blue-600 font-medium' : ''; ?>">Dashboard</a>
            <a href="<?php echo $base_path; ?>modules/requests/view_requests.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'view_requests.php' ? 'text-blue-600 font-medium' : ''; ?>">Requests</a>
            <a href="<?php echo $base_path; ?>modules/logs/view_logs.php" class="block text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'view_logs.php' ? 'text-blue-600 font-medium' : ''; ?>">Audit Logs</a>
            
            <!-- Admin Links in Mobile Menu - Only visible to administrators -->
            <?php if ($is_admin): ?>
            <div class="pt-2 pb-1 border-t border-gray-200">
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin Panel</p>
                <a href="<?php echo $base_path; ?>modules/admin/user_management.php" class="block px-4 py-2 text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'user_management.php' ? 'text-blue-600 font-medium' : ''; ?>">
                    <i class="fas fa-users mr-2"></i>User Management
                </a>
                <a href="<?php echo $base_path; ?>modules/admin/system_settings.php" class="block px-4 py-2 text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'system_settings.php' ? 'text-blue-600 font-medium' : ''; ?>">
                    <i class="fas fa-cog mr-2"></i>System Settings
                </a>
                <a href="<?php echo $base_path; ?>modules/admin/data_sources.php" class="block px-4 py-2 text-gray-600 hover:text-blue-600 transition-colors <?php echo $current_page == 'data_sources.php' ? 'text-blue-600 font-medium' : ''; ?>">
                    <i class="fas fa-database mr-2"></i>Data Sources
                </a>
            </div>
            <?php endif; ?>
            
            <hr class="my-2 border-gray-200">
            <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors">Profile</a>
            <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors">Settings</a>
            <a href="<?php echo $base_path; ?>includes/logout.php" class="block text-red-600 hover:text-red-800 transition-colors">Logout</a>
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
    
    /* Admin dropdown styling */
    .group:hover .absolute {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .group .absolute {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
    }
</style>