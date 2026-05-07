<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

if (!isset($_GET['id'])) {
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$ministry_id = $_SESSION['ministry_id'];

// Get request details
$stmt = $pdo->prepare("
    SELECT dr.*, m1.name as from_ministry_name, m2.name as to_ministry_name, 
           u.name as requester_name, u.email as requester_email
    FROM data_requests dr 
    JOIN ministry m1 ON dr.from_ministry_id = m1.id 
    JOIN ministry m2 ON dr.to_ministry_id = m2.id 
    JOIN user u ON dr.user_id = u.id
    WHERE dr.id = ? AND (dr.user_id = ? OR dr.to_ministry_id = ? OR dr.from_ministry_id = ?)
");
$stmt->execute([$request_id, $user_id, $ministry_id, $ministry_id]);
$request = $stmt->fetch();

if (!$request) {
    header("Location: view_requests.php");
    exit();
}

// Get responses
$stmt = $pdo->prepare("
    SELECT rr.*, u.name as responder_name, u.email as responder_email
    FROM request_responses rr
    JOIN user u ON rr.responding_user_id = u.id
    WHERE rr.request_id = ?
    ORDER BY rr.created_at DESC
");
$stmt->execute([$request_id]);
$responses = $stmt->fetchAll();

// Get logs
$stmt = $pdo->prepare("
    SELECT l.*, u.name as user_name
    FROM log l
    JOIN user u ON l.user_id = u.id
    WHERE l.request_id = ?
    ORDER BY l.timestamp DESC
");
$stmt->execute([$request_id]);
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Request Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Request Details</h2>
                    <p class="text-gray-600">View details of data exchange request <?php echo $request['request_id']; ?></p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="view_requests.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Requests
                    </a>
                </div>
            </div>
        </div>

        <!-- Request Details Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Request ID</p>
                            <p class="text-gray-900 font-medium"><?php echo $request['request_id']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">From Ministry</p>
                            <p class="text-gray-900 font-medium"><?php echo $request['from_ministry_name']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">To Ministry</p>
                            <p class="text-gray-900 font-medium"><?php echo $request['to_ministry_name']; ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Requester</p>
                            <p class="text-gray-900 font-medium"><?php echo $request['requester_name']; ?> (<?php echo $request['requester_email']; ?>)</p>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Details</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
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
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Data Type</p>
                            <p class="text-gray-900 font-medium"><?php echo ucfirst($request['data_type']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Urgency</p>
                            <p class="text-gray-900 font-medium"><?php echo ucfirst($request['urgency']); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Created On</p>
                            <p class="text-gray-900 font-medium"><?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Purpose</h3>
                <p class="text-gray-700"><?php echo $request['purpose']; ?></p>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                <p class="text-gray-700"><?php echo !empty($request['description']) ? $request['description'] : 'No additional description provided.'; ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <?php if ($request['to_ministry_id'] == $ministry_id && $request['status'] == 'pending'): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Respond to Request</h3>
            <div class="flex space-x-4">
                <a href="process_request.php?id=<?php echo $request['id']; ?>&action=approve" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    <i class="fas fa-check mr-2"></i>Approve Request
                </a>
                <a href="process_request.php?id=<?php echo $request['id']; ?>&action=reject" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                    <i class="fas fa-times mr-2"></i>Reject Request
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Responses -->
        <?php if (!empty($responses)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Responses</h3>
            <div class="space-y-4">
                <?php foreach ($responses as $response): ?>
                <div class="border-l-4 <?php echo $response['status'] == 'approved' ? 'border-green-500' : ($response['status'] == 'rejected' ? 'border-red-500' : 'border-blue-500'); ?> pl-4 py-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-gray-900"><?php echo $response['responder_name']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('M j, Y g:i A', strtotime($response['created_at'])); ?></p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $response['status'] == 'approved' ? 'bg-green-100 text-green-800' : ($response['status'] == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'); ?>">
                            <?php echo ucfirst($response['status']); ?>
                        </span>
                    </div>
                    <?php if (!empty($response['response'])): ?>
                    <p class="mt-2 text-gray-700"><?php echo $response['response']; ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Audit Logs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Audit Log</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $log['user_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo str_replace('_', ' ', $log['action']); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $log['details']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo date('M j, Y g:i A', strtotime($log['timestamp'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>