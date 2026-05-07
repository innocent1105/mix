<?php
include 'config/database.php';
// include 'admin_header.php';

$error = '';
$success = '';

if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $head = trim($_POST['head']);
    $contact = trim($_POST['contact']);

    if ($name === '') {
        $error = "Ministry name is required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO ministry (name, description, contact_info, created_at) VALUES (?, ?, ?, NOW())");
            if ($stmt->execute([$name, $description, $contact])) {
                $success = "Ministry added successfully.";
            } else {
                $error = "Failed to add ministry. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM ministry WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Ministry deleted successfully.";
    } catch (PDOException $e) {
        $error = "Error deleting ministry: " . $e->getMessage();
    }
}

// Ministry performance stats
try {
    $mp_ministriesStmt = $pdo->query("SELECT id, name, status FROM ministries ORDER BY name");
    $mp_ministries = $mp_ministriesStmt->fetchAll(PDO::FETCH_ASSOC);

    $mp_stats = [];
    foreach ($mp_ministries as $mp_min) {
        $min_id = $mp_min['id'];

        // Total requests
        $stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM data_requests WHERE from_ministry_id = ?");
        $stmt_total->execute([$min_id]);
        $mp_total = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Approved requests
        $stmt_approved = $pdo->prepare("SELECT COUNT(*) as approved FROM data_requests WHERE from_ministry_id = ? AND status='approved'");
        $stmt_approved->execute([$min_id]);
        $mp_approved = $stmt_approved->fetch(PDO::FETCH_ASSOC)['approved'] ?? 0;

        // Average response time (hours)
        $stmt_avg = $pdo->prepare("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours
            FROM data_requests
            WHERE from_ministry_id = ? AND updated_at IS NOT NULL
        ");
        $stmt_avg->execute([$min_id]);
        $mp_avgResponse = round($stmt_avg->fetch(PDO::FETCH_ASSOC)['avg_hours'] ?? 0);

        // Approval rate
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
} catch (PDOException $e) {
    $error = "Failed to load ministry stats: " . $e->getMessage();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Ministry Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Ministry Management</h2>
                <p class="text-gray-600">Manage and maintain ministries in the system</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="admin_dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Ministry List -->
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





</main>

<?php include 'includes/footer.php'; ?>

<script>
    lucide.createIcons();
</script>
</body>
</html>
