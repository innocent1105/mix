<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$ministry_id = $_SESSION['ministry_id'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Build query
$query = "
    SELECT dr.*, m1.name as from_ministry_name, m2.name as to_ministry_name 
    FROM data_requests dr 
    JOIN ministry m1 ON dr.from_ministry_id = m1.id 
    JOIN ministry m2 ON dr.to_ministry_id = m2.id 
    WHERE (dr.user_id = ? OR dr.to_ministry_id = ?)
";

$params = [$user_id, $ministry_id];

if (!empty($status_filter)) {
    $query .= " AND dr.status = ?";
    $params[] = $status_filter;
}

if (!empty($type_filter)) {
    $query .= " AND dr.data_type = ?";
    $params[] = $type_filter;
}

$query .= " ORDER BY dr.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Success message
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Count requests by status for stats
$status_counts = [
    'total' => count($requests),
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
    'completed' => 0
];

foreach ($requests as $request) {
    if (isset($status_counts[$request['status']])) {
        $status_counts[$request['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Data Requests</title>
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
        
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .filter-select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .table-row {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table-row:hover {
            background: #f8fafc;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            background: #f1f5f9;
            transform: scale(1.05);
        }
        
        .search-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px 10px 40px;
            width: 100%;
            transition: all 0.2s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
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
                    <a href="#" class="nav-link active flex items-center">
                        <i class="fas fa-exchange-alt mr-3"></i>
                        Data Requests
                    </a>
                    <!-- <a href="#" class="nav-link flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        Data Repository
                    </a> -->
                
                    <a href="../../ministries.php" class="nav-link flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        Ministries
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
                    <h1 class="text-2xl font-bold text-gray-900">Data Requests</h1>
                    <p class="text-gray-600 mt-1">Manage and track all data exchange requests</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="tasha.pdf" download class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-download mr-2"></i> Download PDF
                    </a>
                    <a href="create_request.php" class="btn-primary inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Request
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            <?php if (!empty($success)): ?>
                <div class="card p-4 mb-6 bg-green-50 border-green-200 fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo $success; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="stat-card card p-4 border-l-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exchange-alt text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Requests</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $status_counts['total']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock text-yellow-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $status_counts['pending']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Approved</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $status_counts['approved']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-red-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Rejected</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $status_counts['rejected']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card card p-4 border-l-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-double text-purple-500 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Completed</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $status_counts['completed']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Type</label>
                        <select name="type" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="demographic" <?php echo $type_filter == 'demographic' ? 'selected' : ''; ?>>Demographic</option>
                            <option value="financial" <?php echo $type_filter == 'financial' ? 'selected' : ''; ?>>Financial</option>
                            <option value="health" <?php echo $type_filter == 'health' ? 'selected' : ''; ?>>Health</option>
                            <option value="education" <?php echo $type_filter == 'education' ? 'selected' : ''; ?>>Education</option>
                            <option value="other" <?php echo $type_filter == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" placeholder="Search requests..." class="search-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">All Requests</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ministries</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($requests as $request): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $request['request_id']; ?></div>
                                    <div class="text-sm text-gray-600 mt-1 max-w-xs"><?php echo $request['purpose']; ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">From:</span> <?php echo $request['from_ministry_name']; ?>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <span class="font-medium">To:</span> <?php echo $request['to_ministry_name']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $status_class = '';
                                    $icon_class = '';
                                    switch($request['status']) {
                                        case 'pending': 
                                            $status_class = 'bg-yellow-100 text-yellow-800'; 
                                            $icon_class = 'fas fa-clock text-yellow-500';
                                            break;
                                        case 'approved': 
                                            $status_class = 'bg-green-100 text-green-800'; 
                                            $icon_class = 'fas fa-check text-green-500';
                                            break;
                                        case 'rejected': 
                                            $status_class = 'bg-red-100 text-red-800'; 
                                            $icon_class = 'fas fa-times text-red-500';
                                            break;
                                        case 'completed': 
                                            $status_class = 'bg-blue-100 text-blue-800'; 
                                            $icon_class = 'fas fa-check-double text-blue-500';
                                            break;
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="<?php echo $icon_class; ?> mr-1"></i>
                                        <span><?php echo ucfirst($request['status']); ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo date('M j, Y', strtotime($request['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="request_detail.php?id=<?php echo $request['id']; ?>" class="action-btn text-blue-600" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($request['status'] == 'pending' && $request['user_id'] == $user_id): ?>
                                        <a href="create_request.php?edit=<?php echo $request['id']; ?>" class="action-btn text-gray-600" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($request['to_ministry_id'] == $ministry_id && $request['status'] == 'pending'): ?>
                                        <a href="process_request.php?id=<?php echo $request['id']; ?>&action=approve" class="action-btn text-green-600" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="process_request.php?id=<?php echo $request['id']; ?>&action=reject" class="action-btn text-red-600" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($requests)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No data requests found</p>
                    <a href="create_request.php" class="btn-primary inline-block mt-4">
                        <i class="fas fa-plus mr-2"></i> Create Your First Request
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Simple search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            const tableRows = document.querySelectorAll('.table-row');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>