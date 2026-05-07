<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAuthentication();

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);
$ministry_id = $user['ministry_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$ministry_filter = $_GET['ministry'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT r.*, 
          m1.name as from_ministry, m1.abbreviation as from_abbreviation,
          m2.name as to_ministry, m2.abbreviation as to_abbreviation,
          u.name as requester_name, rt.name as record_type_name
          FROM request r
          JOIN ministry m1 ON r.requester_ministry_id = m1.id
          JOIN ministry m2 ON r.target_ministry_id = m2.id
          JOIN user u ON r.requester_user_id = u.id
          JOIN record_type rt ON r.request_type = rt.id
          WHERE (r.requester_ministry_id = ? OR r.target_ministry_id = ?)";

$params = [$ministry_id, $ministry_id];

// Apply filters
if ($status_filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
}

if ($ministry_filter !== 'all') {
    $query .= " AND (r.requester_ministry_id = ? OR r.target_ministry_id = ?)";
    $params[] = $ministry_filter;
    $params[] = $ministry_filter;
}

if (!empty($search)) {
    $query .= " AND (r.purpose LIKE ? OR r.description LIKE ? OR r.record_id LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY r.date_create DESC";

// Get requests
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Get ministries for filter
$ministries = $pdo->query("SELECT * FROM ministry WHERE is_active = 1 ORDER BY name")->fetchAll();

// Display success/error messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Requests - Inter-Ministry Data Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/header.php'; ?>
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Data Requests</h2>
                    <p class="text-gray-600">View and manage all data exchange requests</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="new-request.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i>New Request
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?php echo $success; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span><?php echo $error; ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ministry</label>
                        <select name="ministry" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="all" <?php echo $ministry_filter === 'all' ? 'selected' : ''; ?>>All Ministries</option>
                            <?php foreach ($ministries as $ministry): ?>
                            <option value="<?php echo $ministry['id']; ?>" <?php echo $ministry_filter == $ministry['id'] ? 'selected' : ''; ?>>
                                <?php echo $ministry['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search requests..." class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors w-full">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">All Requests</h3>
                    <span class="text-sm text-gray-600 mt-2 sm:mt-0">
                        <?php echo count($requests); ?> request(s) found
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Ministry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Ministry</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No requests found matching your criteria.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $request['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 <?php echo 'bg-' . getMinistryColor($request['from_abbreviation']) . '-100'; ?>">
                                        <span class="text-xs font-semibold <?php echo 'text-' . getMinistryColor($request['from_abbreviation']) . '-600'; ?>"><?php echo $request['from_abbreviation']; ?></span>
                                    </div>
                                    <span class="text-sm text-gray-900"><?php echo $request['from_ministry']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 <?php echo 'bg-' . getMinistryColor($request['to_abbreviation']) . '-100'; ?>">
                                        <span class="text-xs font-semibold <?php echo'text-' . getMinistryColor($request['to_abbreviation']) . '-600'; ?>"><?php echo $request['to_abbreviation']; ?></span>
</div>
<span class="text-sm text-gray-900"><?php echo $request['to_ministry']; ?></span>
</div>
</td>
<td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($request['purpose']); ?>">
<?php echo htmlspecialchars($request['purpose']); ?>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
<?php echo htmlspecialchars($request['record_type_name']); ?>
</td>
<td class="px-6 py-4 whitespace-nowrap">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getStatusClass($request['status']); ?>">
<i class="fas mr-1 <?php echo getStatusIcon($request['status']); ?>"></i>
<span><?php echo ucfirst($request['status']); ?></span>
</span>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
<?php echo formatDate($request['date_create']); ?>
</td>
<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
<div class="flex items-center space-x-2">
<a href="request-detail.php?id=<?php echo $request['id']; ?>" class="text-blue-600 hover:text-blue-900 transition-colors" title="View Details">
<i class="fas fa-eye"></i>
</a>

text
                                <?php if ($request['status'] === 'pending' && $request['target_ministry_id'] == $ministry_id): ?>
                                <a href="../process/update-request.php?id=<?php echo $request['id']; ?>&status=approved" class="text-green-600 hover:text-green-900 transition-colors" title="Approve Request" onclick="return confirm('Are you sure you want to approve this request?')">
                                    <i class="fas fa-check"></i>
                                </a>
                                <a href="../process/update-request.php?id=<?php echo $request['id']; ?>&status=rejected" class="text-red-600 hover:text-red-900 transition-colors" title="Reject Request" onclick="return confirm('Are you sure you want to reject this request?')">
                                    <i class="fas fa-times"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($request['status'] === 'approved' && $request['requester_ministry_id'] == $ministry_id): ?>
                                <a href="#" class="text-purple-600 hover:text-purple-900 transition-colors" title="Download Data">
                                    <i class="fas fa-download"></i>
                                </a>
                                <?php endif; ?>
                                
                                <?php if ($request['status'] === 'rejected' && $request['requester_ministry_id'] == $ministry_id): ?>
                                <a href="new-request.php?edit=<?php echo $request['id']; ?>" class="text-orange-600 hover:text-orange-900 transition-colors" title="Resubmit Request">
                                    <i class="fas fa-redo"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination would go here for larger datasets -->
    </div>
</main>

<?php include '../includes/footer.php'; ?>
</body> </html>