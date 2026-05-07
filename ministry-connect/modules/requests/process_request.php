<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: view_requests.php");
    exit();
}

$request_id = $_GET['id'];
$action = $_GET['action'];
$user_id = $_SESSION['user_id'];
$ministry_id = $_SESSION['ministry_id'];

// Get request details
$stmt = $pdo->prepare("SELECT * FROM data_requests WHERE id = ?");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request || $request['to_ministry_id'] != $ministry_id || $request['status'] != 'pending') {
    header("Location: view_requests.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response_text = trim($_POST['response']);
    $status = $action == 'approve' ? 'approved' : 'rejected';
    
    try {
        // Update request status
        $stmt = $pdo->prepare("UPDATE data_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $request_id]);
        
        // Add response
        $stmt = $pdo->prepare("INSERT INTO request_responses (request_id, responding_user_id, response, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$request_id, $user_id, $response_text, $status]);
        
        // Log the action
        $action_text = $action == 'approve' ? 'request_approved' : 'request_rejected';
        $details = ucfirst($action) . ' data request: ' . $request['request_id'];
        
        $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details, request_id) VALUES (?, ?, ?, ?)");
        $log_stmt->execute([$user_id, $action_text, $details, $request_id]);
        
        header("Location: view_requests.php?success=Request " . $action . "d successfully");
        exit();
    } catch (PDOException $e) {
        $error = "Error processing request: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - <?php echo ucfirst($action); ?> Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php echo ucfirst($action); ?> Request</h2>
            <p class="text-gray-600">You are about to <?php echo $action; ?> request <?php echo $request['request_id']; ?></p>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Request Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Information</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Request ID</p>
                    <p class="text-gray-900 font-medium"><?php echo $request['request_id']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Purpose</p>
                    <p class="text-gray-900 font-medium"><?php echo $request['purpose']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="text-gray-700"><?php echo !empty($request['description']) ? $request['description'] : 'No additional description provided.'; ?></p>
                </div>
            </div>
        </div>

        <!-- Response Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Response Message (Optional)</label>
                    <textarea class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" name="response" rows="4" placeholder="Add any comments or instructions regarding this request"></textarea>
                </div>
                <div class="flex justify-end space-x-4">
                    <a href="request_detail.php?id=<?php echo $request_id; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="<?php echo $action == 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'; ?> text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <?php echo $action == 'approve' ? 'Approve' : 'Reject'; ?> Request
                    </button>
                </div>
            </form>
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