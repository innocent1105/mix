<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

checkAuthentication();

$user_id = $_SESSION['user_id'];
$user = getUserById($pdo, $user_id);
$ministry_id = $user['ministry_id'];

// Get ministries (excluding current user's ministry)
$ministries = $pdo->query("SELECT * FROM ministry WHERE id != $ministry_id AND is_active = 1 ORDER BY name")->fetchAll();

// Get record types
$record_types = $pdo->query("SELECT * FROM record_type ORDER BY name")->fetchAll();

// Check if editing existing request
$edit_mode = false;
$editing_request = null;
if (isset($_GET['edit'])) {
    $request_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM request WHERE id = ? AND requester_ministry_id = ?");
    $stmt->execute([$request_id, $ministry_id]);
    $editing_request = $stmt->fetch();
    
    if ($editing_request) {
        $edit_mode = true;
    }
}

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
    <title><?php echo $edit_mode ? 'Edit' : 'New'; ?> Request - Inter-Ministry Data Exchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/header.php'; ?>
    
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php echo $edit_mode ? 'Edit' : 'New'; ?> Data Request</h2>
                    <p class="text-gray-600"><?php echo $edit_mode ? 'Update your data exchange request' : 'Submit a new inter-ministry data exchange request'; ?></p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="requests.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Requests
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

        <!-- Request Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="../process/create-request.php">
                <?php if ($edit_mode): ?>
                <input type="hidden" name="request_id" value="<?php echo $editing_request['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Ministry</label>
                        <div class="p-3 bg-gray-100 rounded-lg border border-gray-300">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 <?php echo 'bg-' . getMinistryColor($user['ministry_abbr']) . '-100'; ?>">
                                    <span class="text-xs font-semibold <?php echo 'text-' . getMinistryColor($user['ministry_abbr']) . '-600'; ?>"><?php echo $user['ministry_abbr']; ?></span>
                                </div>
                                <span class="text-gray-900"><?php echo $user['ministry_name']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Ministry <span class="text-red-500">*</span></label>
                        <select name="target_ministry_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Select Ministry</option>
                            <?php foreach ($ministries as $ministry): ?>
                            <option value="<?php echo $ministry['id']; ?>" 
                                <?php echo ($edit_mode && $editing_request['target_ministry_id'] == $ministry['id']) ? 'selected' : ''; ?>>
                                <?php echo $ministry['name']; ?> (<?php echo $ministry['abbreviation']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Type <span class="text-red-500">*</span></label>
                        <select name="request_type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Select Data Type</option>
                            <?php foreach ($record_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                <?php echo ($edit_mode && $editing_request['request_type'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo $type['name']; ?> (<?php echo ucfirst($type['required_clearance']); ?> clearance)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-red-500">*</span></label>
                        <input type="text" name="purpose" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                               placeholder="Brief purpose of the data request" 
                               value="<?php echo $edit_mode ? htmlspecialchars($editing_request['purpose']) : ''; ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Record ID (Optional)</label>
                        <input type="text" name="record_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                               placeholder="Specific record ID if applicable" 
                               value="<?php echo $edit_mode ? htmlspecialchars($editing_request['record_id']) : ''; ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                                  placeholder="Detailed description of the data needed and its intended use"><?php echo $edit_mode ? htmlspecialchars($editing_request['description']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-4">
                    <a href="requests.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-sm">
                        <?php echo $edit_mode ? 'Update Request' : 'Submit Request'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Data Sensitivity Notice -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0 mt-0.5">
                    <i class="fas fa-shield-alt text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-medium text-blue-900 mb-1">Data Sensitivity Notice</h3>
                    <p class="text-blue-700 text-sm">All data exchanges are subject to government security protocols. Ensure you have the proper clearance level for the data you're requesting and comply with all data protection regulations.</p>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>