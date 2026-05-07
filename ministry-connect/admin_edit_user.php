<?php

    session_start();
    require_once 'config/database.php';

    // requests

    $admin_email = $_SESSION['user_email'];
    $role = $_SESSION['user_role'];

    if($role !== "admin"){
        header("header: index.php");
    }


    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch();



    $stmt = $pdo->prepare("SELECT * FROM data_requests");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $number_of_requests = 0;
    foreach($requests as $request) {
        $number_of_requests++;
    }


    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE status = :status");
    $stmt->execute(['status' => 'pending']);
    $number_of_pending_requests = $stmt->rowCount();

    $stmt = $pdo->prepare("SELECT * FROM data_requests WHERE status = :status");
    $stmt->execute(['status' => 'approved']);
    $number_of_approved_requests = $stmt->rowCount();

    $stmt = $pdo->prepare("SELECT * FROM ministries WHERE status = :status");
    $stmt->execute(['status' => 'active']);
    $number_of_active_ministries = $stmt->rowCount();

    $notifications = [1,2,3,4];

    $stmt = $pdo->prepare("SELECT * FROM notification");
    $stmt->execute();
    $notifications = $stmt->fetchAll();



    
// Get user ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("No user specified.");
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $update = $pdo->prepare("UPDATE user SET name = ?, email = ?, role = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
    $update->execute([$name, $email, $role, $is_active, $id]);

    header("Location: admin_dashboard.php"); 
    exit;
}

    // =========================================

    

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MinistryLink - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .progress-bar {
            height: 6px;
            border-radius: 3px;
            overflow: hidden;
            background-color: #e5e7eb;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease-in-out;
        }
        
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        
        .ministry-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
        }
        
        .ministry-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .ministry-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .ministry-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .ministry-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .ministry-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        
        .trend-up {
            color: #10b981;
        }
        
        .trend-down {
            color: #ef4444;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .dashboard-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
        }
        
        .section-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: linear-gradient(to right, #f9fafb, #ffffff);
        }
        
        .chart-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            padding: 1.5rem;
        }
        
        .stat-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
        }
        
        .filter-btn {
            transition: all 0.2s ease;
        }
        
        .filter-btn:hover {
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 gradient-bg rounded-lg flex items-center justify-center cursor-pointer">
                        <i class="fas fa-exchange-alt text-white text-lg"></i>
                    </div>
                    <div class="cursor-pointer">
                        <h1 class="text-xl font-bold text-gray-900">MinistryLink</h1>
                        <p class="text-xs text-gray-500">Inter-Ministry Data Exchange</p>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-8">
                    <!-- <a href="#" class="text-blue-600 font-medium border-b-2 border-blue-600 pb-1">Dashboard</a> -->
                </div>

                <!-- User Menu & Notifications -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button class="relative text-gray-700 hover:text-blue-600 transition-colors duration-200 p-2">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="notification-dot"></span>
                        </button>
                    </div>

                    <!-- User Menu -->
                    <div class="relative">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="hidden sm:block font-medium">Administrator</span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>
                    </div>

                        <div class="flex items-center space-x-4">
                  
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative text-gray-700 hover:text-blue-600 transition-colors duration-200 p-2">
                            <i class="fas fa-bell text-xl"></i>
                            <?php if (count($notifications) > 0): ?>
                                <span class="notification-dot"></span>
                            <?php endif; ?>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="font-semibold text-gray-900">Notifications</h3>
                            </div>
                            
                            <?php if (count($notifications) > 0): ?>
                                <?php foreach ($notifications as $notification): ?>
                                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                                    <p class="text-sm text-gray-800"><?php echo $notification['message']; ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?></p>
                                </div>
                                <?php endforeach; ?>
                                <div class="px-4 py-2 text-center">
                                    <a href="#" class="text-blue-600 text-sm hover:text-blue-800 font-medium">View All</a>
                                </div>
                            <?php else: ?>
                                <div class="px-4 py-4 text-center">
                                    <p class="text-gray-500 text-sm">No new notifications</p>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- User Menu ============================================================================================ -->
                    <!-- <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <span class="hidden sm:block font-medium"><?php echo $_SESSION['user_name']; ?></span>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                            <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                <p class="font-medium"><?php echo $_SESSION['user_name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></p>
                            </div>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-user-circle mr-2"></i>Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                                <i class="fas fa-cog mr-2"></i>Settings
                            </a>
                            <hr class="my-2 border-gray-200">
                            <a href="includes/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors duration-200">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </a>
                        </div>
                    </div> -->

                </div>

                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-600 hover:text-blue-600 transition-colors duration-200" id="mobile-menu-button">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="hidden md:hidden bg-white border-t border-gray-200" id="mobile-menu">
            <div class="px-4 py-3 space-y-3">
                <!-- <a href="#" class="block text-blue-600 font-medium">Dashboard</a> -->

                <hr class="my-2 border-gray-200">
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors duration-200">Profile</a>
                <a href="#" class="block text-gray-600 hover:text-blue-600 transition-colors duration-200">Settings</a>
                <a href="#" class="block text-red-600 hover:text-red-800 transition-colors duration-200">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

   <div class=" mx-auto mx-4 mt-10 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Edit User</h2>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Role:</label>
            <select name="role"
                class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="approver" <?= $user['role'] === 'approver' ? 'selected' : '' ?>>Approver</option>
                <option value="requester" <?= $user['role'] === 'requester' ? 'selected' : '' ?>>Requester</option>
            </select>
        </div>

        <div class="flex items-center space-x-2">
            <input type="checkbox" name="is_active" id="is_active" <?= $user['is_active'] ? 'checked' : '' ?>
                class="h-4 w-4 text-blue-500 border-gray-300 rounded">
            <label for="is_active" class="text-gray-700 font-medium">Active</label>
        </div>

        <div>
            <button type="submit"
                class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition duration-200">
                Update User
            </button>
        </div>
    </form>
</div>


    </main>
      
</body>
</html>








