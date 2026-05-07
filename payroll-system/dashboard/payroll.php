<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT id, full_name, hourly_rate FROM users WHERE role = 'employee'");
$stmt->execute();
$employees = $stmt->fetchAll();

$payroll = [];
$totalCompanyPayout = 0;

foreach ($employees as $employee) {
    $userId = $employee['id'];
    $hourlyRate = $employee['hourly_rate'];

    $stmt = $pdo->prepare("
        SELECT SUM(TIMESTAMPDIFF(MINUTE, sign_in_time, sign_out_time)) AS total_minutes
        FROM attendance
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    $totalHours = ($result['total_minutes'] ?? 0) / 60;
    $salary = $totalHours * $hourlyRate;
    $totalCompanyPayout += $salary;
    
    $payroll[] = [
        'id' =>  $employee['id'],
        'name' => $employee['full_name'],
        'hourly_rate' => $hourlyRate,
        'hours_worked' => round($totalHours, 2),
        'salary' => $salary, 
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Payroll Management | EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
            .print-only { display: block; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <div class="max-w-6xl mx-auto p-4 mt-20 md:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 no-print">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Payroll Overview</h1>
                <p class="text-slate-500 mt-1">Monthly earnings report for all employees.</p>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <button onclick="window.print()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 transition shadow-sm font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    Print Report
                </button>
                <a href="admin.php" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 transition font-semibold shadow-md">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-blue-200">
                <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Company Payout</p>
                <h2 class="text-3xl font-bold mt-2">K<?php echo number_format($totalCompanyPayout, 2); ?></h2>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Staff</p>
                <h2 class="text-3xl font-bold mt-2 text-slate-900"><?php echo count($payroll); ?></h2>
            </div>
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <p class="text-slate-500 text-sm font-medium uppercase tracking-wider">Avg. Hourly Rate</p>
                <h2 class="text-3xl font-bold mt-2 text-slate-900">
                    K<?php echo count($payroll) > 0 ? number_format(array_sum(array_column($payroll, 'hourly_rate')) / count($payroll), 2) : '0.00'; ?>
                </h2>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Employee ID</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Full Name</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Hours</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-right">Rate</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-right">Total Salary</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($payroll) > 0): ?>
                        <?php foreach ($payroll as $row): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-mono text-xs text-slate-400">#EMP-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-800"><?php echo htmlspecialchars($row['name']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 bg-slate-100 rounded-full text-sm font-medium text-slate-700">
                                        <?php echo $row['hours_worked']; ?> <span class="text-xs text-slate-400">hrs</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-slate-600">
                                    K<?php echo number_format($row['hourly_rate'], 2); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-lg font-bold text-blue-600">
                                        K<?php echo number_format($row['salary'], 2); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                No payroll data found for the current selection.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right text-slate-500 uppercase tracking-wider">Grand Total:</td>
                        <td class="px-6 py-4 text-right text-xl text-slate-900 font-black">
                            K<?php echo number_format($totalCompanyPayout, 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-8 text-center no-print">
            <p class="text-xs text-slate-400 uppercase tracking-widest">Confidential Payroll Document • Generated on <?php echo date('Y-m-d'); ?></p>
        </div>
    </div>

</body>
</html>