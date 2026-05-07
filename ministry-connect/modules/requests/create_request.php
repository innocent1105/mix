<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

$error = '';
$success = '';

// Get ministries for dropdown
$ministries = $pdo->query("SELECT * FROM ministry ORDER BY name")->fetchAll();

// If editing existing request
if (isset($_GET['edit'])) {
    $request_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE id = ? AND user_id = ?");
    $stmt->execute([$request_id, $_SESSION['user_id']]);
    $request = $stmt->fetch();
    
    if (!$request) {
        header("Location: view_requests.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_ministry_id = $_SESSION['ministry_id'];
    $to_ministry_id = $_POST['to_ministry_id'];
    $purpose = trim($_POST['purpose']);
    $description = trim($_POST['description']);
    $data_type = $_POST['data_type'];
    $urgency = $_POST['urgency'];
    
    if (empty($purpose) || empty($to_ministry_id)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            $pdo->beginTransaction(); // Start transaction
            
            if (isset($_POST['request_id']) && !empty($_POST['request_id'])) {
                // Update existing request
                $stmt = $pdo->prepare("UPDATE data_requests SET to_ministry_id = ?, purpose = ?, description = ?, data_type = ?, urgency = ? WHERE id = ? AND user_id = ?");
                $stmt->execute([$to_ministry_id, $purpose, $description, $data_type, $urgency, $_POST['request_id'], $_SESSION['user_id']]);
                $success = "Request updated successfully";
            } else {
                // Create new request
                // Generate unique request ID
                $request_id = 'REQ-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $stmt = $pdo->prepare("INSERT INTO data_requests (request_id, from_ministry_id, to_ministry_id, user_id, purpose, description, data_type, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$request_id, $from_ministry_id, $to_ministry_id, $_SESSION['user_id'], $purpose, $description, $data_type, $urgency]);
                
                $new_request_id = $pdo->lastInsertId();
                
                // Log the action - handle potential foreign key constraint issues
                try {
                    $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details, request_id) VALUES (?, ?, ?, ?)");
                    $log_stmt->execute([$_SESSION['user_id'], 'request_created', 'Created new data request: ' . $request_id, $new_request_id]);
                } catch (PDOException $e) {
                    // If logging fails due to foreign key constraint, insert without request_id
                    if ($e->getCode() == '23000') {
                        $log_stmt = $pdo->prepare("INSERT INTO log (user_id, action, details) VALUES (?, ?, ?)");
                        $log_stmt->execute([$_SESSION['user_id'], 'request_created', 'Created new data request: ' . $request_id]);
                    } else {
                        throw $e; // Re-throw if it's a different error
                    }
                }
                
                $success = "Request created successfully";
            }
            
            $pdo->commit(); // Commit transaction
            
            header("Location: view_requests.php?success=" . urlencode($success));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback transaction on error
            $error = "Error creating request: " . $e->getMessage();
        }
    }
}

// Get ministry name for display
$ministry_name = "Your Ministry";
if (isset($_SESSION['ministry_name'])) {
    $ministry_name = $_SESSION['ministry_name'];
} else {
    // Fallback: get ministry name from database
    try {
        $stmt = $pdo->prepare("SELECT name FROM ministry WHERE id = ?");
        $stmt->execute([$_SESSION['ministry_id']]);
        $ministry = $stmt->fetch();
        if ($ministry) {
            $ministry_name = $ministry['name'];
            $_SESSION['ministry_name'] = $ministry_name; // Store in session for future use
        }
    } catch (PDOException $e) {
        // If we can't get the ministry name, use a generic fallback
        $ministry_name = "Your Ministry";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - <?php echo isset($request) ? 'Edit' : 'New'; ?> Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #334155;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }
        
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        
        .form-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            width: 100%;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }
        
        .required-field::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }
        
        .nav-link {
            color: #64748b;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        
        .urgency-high {
            border-left: 4px solid #ef4444;
        }
        
        .urgency-critical {
            border-left: 4px solid #dc2626;
        }
        
        .urgency-normal {
            border-left: 4px solid #3b82f6;
        }
        
        .urgency-low {
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 min-h-screen bg-white border-r border-gray-200 hidden md:block">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">MinistryLink</h2>
                <nav class="space-y-1">
                    <a href="../requests/view_requests.php" class="nav-link flex items-center">
                        <i class="fas fa-exchange-alt mr-3"></i>
                        Data Requests
                    </a>
                    <a href="../logs/view_logs.php" class="nav-link flex items-center">
                        <i class="fas fa-clipboard-list mr-3"></i>
                        Audit Logs
                    </a>
                    <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        Data Repository
                    </a>
                    <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-chart-bar mr-3"></i>
                        Analytics
                    </a>
                    <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-cog mr-3"></i>
                        Settings
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main content area -->
        <main class="flex-1 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo isset($request) ? 'Edit' : 'New'; ?> Data Request</h1>
                    <p class="text-gray-600 mt-1"><?php echo isset($request) ? 'Update your data exchange request' : 'Submit a new inter-ministry data exchange request'; ?></p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="view_requests.php" class="btn-secondary inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Requests
                    </a>
                </div>
            </div>

            <!-- Error/Success Messages -->
            <?php if (!empty($error)): ?>
                <div class="card p-4 mb-6 bg-red-50 border-red-200 fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Request Form -->
            <div class="card p-6">
                <form method="POST" action="">
                    <?php if (isset($request)): ?>
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 gap-6 mb-6">
                        <!-- From Ministry -->
                        <div>
                            <label class="form-label required-field">From Ministry</label>
                            <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <i class="fas fa-building text-gray-500 mr-3"></i>
                                <span class="text-gray-700"><?php echo htmlspecialchars($ministry_name); ?></span>
                            </div>
                            <input type="hidden" name="from_ministry_id" value="<?php echo $_SESSION['ministry_id']; ?>">
                        </div>
                        
                        <!-- To Ministry -->
                        <div>
                            <label class="form-label required-field">To Ministry</label>
                            <div class="relative">
                                <select class="form-input appearance-none cursor-pointer" name="to_ministry_id" required>
                                    <option value="">Select Ministry</option>
                                    <?php foreach ($ministries as $ministry): 
                                        if ($ministry['id'] == $_SESSION['ministry_id']) continue;
                                    ?>
                                        <option value="<?php echo $ministry['id']; ?>" 
                                            <?php 
                                            if (isset($request) && $request['to_ministry_id'] == $ministry['id']) echo 'selected';
                                            ?>>
                                            <?php echo htmlspecialchars($ministry['name']); ?> (<?php echo htmlspecialchars($ministry['abbreviation']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Purpose -->
                        <div>
                            <label class="form-label required-field">Purpose</label>
                            <input type="text" class="form-input" name="purpose" placeholder="Brief purpose of the data request" value="<?php echo isset($request) ? htmlspecialchars($request['purpose']) : ''; ?>" required>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label class="form-label">Description</label>
                            <textarea class="form-input" name="description" rows="4" placeholder="Detailed description of the data needed and its intended use"><?php echo isset($request) ? htmlspecialchars($request['description']) : ''; ?></textarea>
                        </div>
                        
                        <!-- Data Type and Urgency -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Data Type</label>
                                <div class="relative">
                                    <select class="form-input appearance-none cursor-pointer" name="data_type">
                                        <option value="demographic" <?php echo (isset($request) && $request['data_type'] == 'demographic') ? 'selected' : ''; ?>>Demographic Data</option>
                                        <option value="financial" <?php echo (isset($request) && $request['data_type'] == 'financial') ? 'selected' : ''; ?>>Financial Data</option>
                                        <option value="health" <?php echo (isset($request) && $request['data_type'] == 'health') ? 'selected' : ''; ?>>Health Data</option>
                                        <option value="education" <?php echo (isset($request) && $request['data_type'] == 'education') ? 'selected' : ''; ?>>Education Data</option>
                                        <option value="other" <?php echo (isset($request) && $request['data_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Urgency</label>
                                <div class="relative">
                                    <select class="form-input appearance-none cursor-pointer" name="urgency" id="urgency-select">
                                        <option value="low" <?php echo (isset($request) && $request['urgency'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                        <option value="normal" <?php echo (isset($request) && $request['urgency'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                                        <option value="high" <?php echo (isset($request) && $request['urgency'] == 'high') ? 'selected' : ''; ?>>High</option>
                                        <option value="critical" <?php echo (isset($request) && $request['urgency'] == 'critical') ? 'selected' : ''; ?>>Critical</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-100">
                        <a href="view_requests.php" class="btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn-primary inline-flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <?php echo isset($request) ? 'Update' : 'Submit'; ?> Request
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Help Section -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="card p-4 bg-blue-50 border-blue-100">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Purpose Tips</h3>
                            <p class="text-xs text-blue-600 mt-1">Be specific about what data you need and why you need it.</p>
                        </div>
                    </div>
                </div>
                
                <div class="card p-4 bg-green-50 border-green-100">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-lightbulb text-green-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Description Help</h3>
                            <p class="text-xs text-green-600 mt-1">Include details about format, timeframe, and specific data points needed.</p>
                        </div>
                    </div>
                </div>
                
                <div class="card p-4 bg-purple-50 border-purple-100">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-purple-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-purple-800">Urgency Guidance</h3>
                            <p class="text-xs text-purple-600 mt-1">Select appropriate urgency level to help ministries prioritize requests.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Update form style based on urgency selection
        const urgencySelect = document.getElementById('urgency-select');
        const formCard = document.querySelector('.card');
        
        function updateUrgencyStyle() {
            // Remove all urgency classes
            formCard.classList.remove('urgency-low', 'urgency-normal', 'urgency-high', 'urgency-critical');
            
            // Add appropriate class based on selection
            if (urgencySelect.value === 'low') {
                formCard.classList.add('urgency-low');
            } else if (urgencySelect.value === 'normal') {
                formCard.classList.add('urgency-normal');
            } else if (urgencySelect.value === 'high') {
                formCard.classList.add('urgency-high');
            } else if (urgencySelect.value === 'critical') {
                formCard.classList.add('urgency-critical');
            }
        }
        
        // Set initial style
        updateUrgencyStyle();
        
        // Update style when selection changes
        urgencySelect.addEventListener('change', updateUrgencyStyle);
    </script>
</body>
</html>