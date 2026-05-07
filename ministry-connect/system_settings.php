<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!$_SESSION['is_admin']) {
    header("Location: ../dashboard.php?error=Access denied. Admin privileges required.");
    exit();
}

// Get all system settings
$stmt = $pdo->prepare("SELECT * FROM system_settings");
$stmt->execute();
$settings = $stmt->fetchAll();

// Handle setting updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $setting_key = $_POST['setting_key'];
    $setting_value = $_POST['setting_value'];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = ?");
        $stmt->execute([$setting_value, $_SESSION['user_id'], $setting_key]);
        
        // Log admin action
        $log_stmt = $pdo->prepare("INSERT INTO admin_log (admin_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $log_stmt->execute([
            $_SESSION['user_id'],
            'update_setting',
            'system_setting',
            null,
            "Updated system setting: $setting_key to $setting_value",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $pdo->commit();
        header("Location: system_settings.php?success=Setting updated successfully");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error updating setting: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - System Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <?php include '../includes/admin_header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">System Settings</h2>
                    <p class="text-gray-600">Configure global system settings and preferences</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Error/Success Messages -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?php echo $_GET['success']; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Settings Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">System Configuration</h3>
            </div>
            <div class="px-6 py-4">
                <form method="POST" action="">
                    <div class="space-y-6">
                        <?php foreach ($settings as $setting): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2"><?php echo ucfirst(str_replace('_', ' ', $setting['setting_key'])); ?></h4>
                            <p class="text-gray-600 text-sm mb-3"><?php echo $setting['description']; ?></p>
                            <div class="flex items-center space-x-3">
                                <input type="hidden" name="setting_key" value="<?php echo $setting['setting_key']; ?>">
                                <input type="text" name="setting_value" value="<?php echo $setting['setting_value']; ?>" class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Update
                                </button>
                            </div>
                            <?php if ($setting['updated_at']): ?>
                            <p class="text-xs text-gray-500 mt-2">
                                Last updated: <?php echo date('M j, Y g:i A', strtotime($setting['updated_at'])); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">PHP Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP Version:</span>
                                <span class="font-medium"><?php echo phpversion(); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Server Software:</span>
                                <span class="font-medium"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max Execution Time:</span>
                                <span class="font-medium"><?php echo ini_get('max_execution_time'); ?> seconds</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Database Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Driver:</span>
                                <span class="font-medium">MySQL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Name:</span>
                                <span class="font-medium"><?php echo $pdo->query("SELECT DATABASE()")->fetchColumn(); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Version:</span>
                                <span class="font-medium"><?php echo $pdo->query("SELECT VERSION()")->fetchColumn(); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-red-200">
                <h3 class="text-lg font-semibold text-red-900">Danger Zone</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-red-900">Clear All Logs</h4>
                            <p class="text-red-700 text-sm">Permanently delete all system and admin logs</p>
                        </div>
                        <button onclick="confirmAction('clear_logs', 'Are you sure you want to clear all logs? This action cannot be undone.')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Clear Logs
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-medium text-red-900">System Maintenance Mode</h4>
                            <p class="text-red-700 text-sm">Put the system in maintenance mode</p>
                        </div>
                        <button onclick="confirmAction('maintenance_mode', 'Enable maintenance mode? Users will not be able to access the system.')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Enable Maintenance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        function confirmAction(action, message) {
            if (confirm(message)) {
                // Implement the action here
                alert('Action: ' + action + ' would be performed here.');
                // You would typically submit a form or make an AJAX request
            }
        }
    </script>
</body>
</html>