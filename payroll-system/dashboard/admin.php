<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$adminName = $_SESSION['user']['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/header.php'; ?>
    <title>Admin Dashboard | EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-slate-900 text-slate-300 hidden md:flex flex-col">
            <div class="p-6">
                <div class="flex items-center gap-2 text-white mb-8">
                    <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center font-bold">E</div>
                    <span class="text-xl font-bold tracking-tight uppercase">EMS Admin</span>
                </div>
                
                <nav class="space-y-1">
                    <a href="#" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    <a href="attendance_logs.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Attendance
                    </a>
                    <a href="payroll.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Payroll
                    </a>
                    <a href="../register.php" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        Add Employee
                    </a>
                </nav>
            </div>
            
            <div class="mt-auto p-6 border-t border-slate-800">
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-400/10 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </aside>

        <main class="flex-1">

            <div class="p-8">
           

                <h3 class="text-lg font-bold text-slate-900 mb-6">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-500 transition cursor-pointer">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            Register User
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4 4H3"></path></svg>
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">Create accounts for staff and assign roles.</p>
                        <a href="../register.php" class="mt-4 block text-sm font-semibold text-blue-600 hover:text-blue-800">Go to Registration &rarr;</a>
                    </div>

                    <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-green-500 transition cursor-pointer">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            Attendance Logs
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4 4H3"></path></svg>
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">Track sign-ins, sign-outs and daily productivity.</p>
                        <a href="attendance_logs.php" class="mt-4 block text-sm font-semibold text-green-600 hover:text-green-800">View Logs &rarr;</a>
                    </div>

                    <div class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-amber-500 transition cursor-pointer">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            Payroll Manager
                            <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transition text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4 4H3"></path></svg>
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">Calculate monthly salaries and generate slips.</p>
                        <a href="payroll.php" class="mt-4 block text-sm font-semibold text-amber-600 hover:text-amber-800">Open Payroll &rarr;</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>