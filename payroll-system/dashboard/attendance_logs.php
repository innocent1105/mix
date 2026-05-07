<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.*, u.full_name 
    FROM attendance a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.work_date DESC, a.sign_in_time DESC
");
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Attendance Logs | EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <div class="max-w-7xl mx-auto p-4 md:p-8 mt-20">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <nav class="flex text-sm text-slate-500 mb-2">
                    <a href="admin.php" class="hover:text-blue-600">Dashboard</a>
                    <span class="mx-2">/</span>
                    <span class="text-slate-900 font-medium">Attendance Logs</span>
                </nav>
                <h1 class="text-3xl font-bold text-slate-900">Attendance History</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition flex items-center gap-2 font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Export PDF
                </button>
                <a href="admin.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium shadow-md shadow-blue-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Dashboard
                </a>
            </div>
        </div>

        <!-- <div class="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-sm flex flex-wrap gap-4 items-center">
            <div class="relative flex-1 min-w-[200px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"></path></svg>
                </span>
                <input type="text" placeholder="Search employee..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>
            <select class="py-2 px-4 border border-slate-200 rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-blue-500">
                <option>All Statuses</option>
                <option>Signed In</option>
                <option>Signed Out</option>
            </select>
            <input type="date" class="py-2 px-4 border border-slate-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-blue-500">
        </div> -->

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Employee</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Date</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Sign In / Out</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500 text-center">Duration</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($logs) > 0): ?>
                        <?php foreach ($logs as $row): ?>
                            <tr class="hover:bg-slate-50 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                            <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                        </div>
                                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600 text-sm italic">
                                    <?php echo date('M d, Y', strtotime($row['work_date'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-slate-700">
                                        <span class="text-blue-600"><?php echo $row['sign_in_time'] ? date('h:i A', strtotime($row['sign_in_time'])) : '--:--'; ?></span>
                                        <span class="text-slate-300 mx-2">→</span>
                                        <span class="text-indigo-600"><?php echo $row['sign_out_time'] ? date('h:i A', strtotime($row['sign_out_time'])) : '--:--'; ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                        <?php
                                        if ($row['sign_in_time'] && $row['sign_out_time']) {
                                            $start = new DateTime($row['sign_in_time']);
                                            $end = new DateTime($row['sign_out_time']);
                                            $interval = $start->diff($end);
                                            echo $interval->format('%h h %i m');
                                        } else {
                                            echo 'In Progress';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['status'] === 'Signed In'): ?>
                                        <span class="flex items-center gap-1.5 text-amber-600 font-bold text-xs uppercase">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="flex items-center gap-1.5 text-emerald-600 font-bold text-xs uppercase">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            Completed
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <p class="text-slate-500 font-medium">No activity logs found for this period.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex items-center justify-between text-sm text-slate-500 px-2">
            <p>Showing <?php echo count($logs); ?> entries</p>
            <div class="flex gap-2">
                <button class="px-3 py-1 border border-slate-200 rounded bg-white hover:bg-slate-50 disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 border border-slate-200 rounded bg-white hover:bg-slate-50">Next</button>
            </div>
        </div>
    </div>

</body>
</html>