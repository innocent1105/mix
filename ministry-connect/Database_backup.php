<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (!$_SESSION['is_admin']) {
    header("Location: ../dashboard.php?error=Access denied. Admin privileges required.");
    exit();
}

// Handle backup actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();
        
        switch ($action) {
            case 'backup':
                // Simple backup implementation (in real application, use proper backup tools)
                $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                $backup_content = "";
                
                // Get all tables
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($tables as $table) {
                    // Get table structure
                    $create_table = $pdo->query("SHOW CREATE TABLE $table")->fetchColumn(1);
                    $backup_content .= "$create_table;\n\n";
                    
                    // Get table data
                    $rows = $pdo->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        $values = array_map(function($value) use ($pdo) {
                            return $value === null ? 'NULL' : $pdo->quote($value);
                        }, $row);
                        
                        $backup_content .= "INSERT INTO $table VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $backup_content .= "\n";
                }
                
                // Save backup file
                file_put_contents("../backups/$backup_file", $backup_content);
                $message = "Backup created successfully: $backup_file";
                break;
        }
        
        // Log admin action
        $log_stmt = $pdo->prepare("INSERT INTO admin_log (admin_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $log_stmt->execute([
            $_SESSION['user_id'],
            $action,
            'system',
            null,
            "Performed $action operation",
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $pdo->commit();
        header("Location: database_backup.php?success=" . urlencode($message));
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error performing action: " . $e->getMessage();
    }
}

// Get existing backups
$backups = [];
$backup_dir = '../backups/';
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'modified' => filemtime($backup_dir . $file)
            ];
        }
    }
    
    // Sort by modification time (newest first)
    usort($backups, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Database Backup</title>
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
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Database Backup</h2>
                    <p class="text-gray-600">Backup and restore system database</p>
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

        <!-- Backup Actions -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Backup Operations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Create Backup</h4>
                    <p class="text-gray-600 text-sm mb-4">Create a complete database backup</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="backup">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors w-full">
                            <i class="fas fa-download mr-2"></i>Create Backup
                        </button>
                    </form>
                </div>
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-medium text-gray-900 mb-2">Restore Backup</h4>
                    <p class="text-gray-600 text-sm mb-4">Restore from a previous backup</p>
                    <button onclick="alert('Restore functionality would be implemented here.')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors w-full">
                        <i class="fas fa-upload mr-2"></i>Restore Backup
                    </button>
                </div>
            </div>
        </div>

        <!-- Existing Backups -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Existing Backups (<?php echo count($backups); ?>)</h3>
                    <p class="text-sm text-gray-600 mt-2 sm:mt-0">Total size: <?php echo round(array_sum(array_column($backups, 'size')) / 1024 / 1024, 2); ?> MB</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Backup File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($backups) > 0): ?>
                            <?php foreach ($backups as $backup): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $backup['name']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo round($backup['size'] / 1024, 2); ?> KB</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('M j, Y g:i A', $backup['modified']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="../backups/<?php echo $backup['name']; ?>" download class="text-blue-600 hover:text-blue-900 transition-colors" title="Download Backup">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="confirmDelete('<?php echo $backup['name']; ?>')" class="text-red-600 hover:text-red-900 transition-colors" title="Delete Backup">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No backup files found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Backup Information -->
        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 overflow-hidden mt-8">
            <div class="px-6 py-4 border-b border-blue-200">
                <h3 class="text-lg font-semibold text-blue-900">Backup Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-900">Backup Location</h4>
                            <p class="text-blue-700 text-sm">Backups are stored in: <code class="bg-blue-100 px-1 py-0.5 rounded">/backups/</code> directory</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-shield-alt text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-900">Security Note</h4>
                            <p class="text-blue-700 text-sm">Ensure backup directory is not publicly accessible. Backups contain sensitive data.</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-history text-blue-600"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-900">Automated Backups</h4>
                            <p class="text-blue-700 text-sm">Consider setting up automated backups using cron jobs or server scheduling tools.</p>
                        </div>
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

        function confirmDelete(filename) {
            if (confirm('Are you sure you want to delete backup: ' + filename + '?')) {
                // Implement delete functionality here
                alert('Delete functionality for ' + filename + ' would be implemented here.');
            }
        }
    </script>
</body>
</html>