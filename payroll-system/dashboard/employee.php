<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: ../index.php");
    exit();
}

$employeeId = $_SESSION['user']['id'];
$employeeName = $_SESSION['user']['name'];
$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $now = date('Y-m-d H:i:s');

    if ($action === 'clock_in') {
        $check = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND sign_out_time IS NULL");
        $check->execute([$employeeId]);

        if ($check->rowCount() > 0) {
            $errorMessage = "You are already clocked in!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, sign_in_time) VALUES (?, ?)");
            $stmt->execute([$employeeId, $now]);
            $successMessage = "Welcome! You've clocked in.";
        }
    } elseif ($action === 'clock_out') {
        $stmt = $pdo->prepare("UPDATE attendance SET sign_out_time = ? WHERE user_id = ? AND sign_out_time IS NULL ORDER BY id DESC LIMIT 1");
        $stmt->execute([$now, $employeeId]);

        if ($stmt->rowCount() > 0) {
            $successMessage = "Great job! You've clocked out.";
        } else {
            $errorMessage = "No active session found.";
        }
    }
}

$records = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY id DESC");
$records->execute([$employeeId]);
$attendanceList = $records->fetchAll(PDO::FETCH_ASSOC);

$today = date('Y-m-d');
$checkToday = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(sign_in_time) = ? ORDER BY id DESC LIMIT 1");
$checkToday->execute([$employeeId, $today]);
$todayRecord = $checkToday->fetch(PDO::FETCH_ASSOC);

$showClockIn = true;
$showClockOut = false;
$currentStatusText = "Off Duty";

if ($todayRecord) {
    if ($todayRecord['sign_out_time'] === null) {
        $showClockIn = false;
        $showClockOut = true;
        $currentStatusText = "On Shift";
    } else {
        $showClockIn = false;
        $showClockOut = false;
        $currentStatusText = "Shift Completed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Log | EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <nav class="bg-white border-b border-slate-200 px-6 py-4 mb-8">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">E</div>
                <span class="font-bold text-slate-800 tracking-tight">EMS</span>
            </div>
            <a href="../logout.php" class="text-sm font-semibold text-slate-500 hover:text-red-600 transition">Logout</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6">
        
        <header class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Hello, <?php echo htmlspecialchars($employeeName); ?>!</h1>
            <p class="text-slate-500 font-medium mt-1" id="liveClock"><?php echo date('l, F jS'); ?></p>
        </header>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden mb-8">
            <div class="p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-2 h-2 rounded-full <?php echo ($currentStatusText == 'On Shift') ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'; ?>"></span>
                        <span class="text-xs font-bold uppercase tracking-widest text-slate-400"><?php echo $currentStatusText; ?></span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900" id="currentTime">--:--:--</h2>
                </div>

                <form method="POST" class="w-full md:w-auto">
                    <?php if ($showClockIn): ?>
                        <button type="submit" name="action" value="clock_in" class="w-full md:w-48 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold shadow-lg shadow-emerald-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            Clock In
                        </button>
                    <?php endif; ?>

                    <?php if ($showClockOut): ?>
                        <button type="submit" name="action" value="clock_out" class="w-full md:w-48 py-4 bg-rose-500 hover:bg-rose-600 text-white rounded-2xl font-bold shadow-lg shadow-rose-200 transition-all transform active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                            Clock Out
                        </button>
                    <?php endif; ?>

                    <?php if (!$showClockIn && !$showClockOut): ?>
                        <div class="px-6 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold text-center border border-slate-200">
                            Shift Finished Today
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if ($successMessage || $errorMessage): ?>
                <div class="px-8 py-3 <?php echo $successMessage ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'; ?> border-t border-slate-100 text-sm font-medium">
                    <?php echo $successMessage ?: $errorMessage; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex items-center justify-between mb-4 px-2">
            <h3 class="text-lg font-bold text-slate-800">Recent Activity</h3>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Clock In</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Clock Out</th>
                        <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($attendanceList as $row): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                <?php echo date('M d, Y', strtotime($row['sign_in_time'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php echo date('h:i A', strtotime($row['sign_in_time'])); ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                <?php echo $row['sign_out_time'] ? date('h:i A', strtotime($row['sign_out_time'])) : '<span class="text-amber-500 font-medium">Active...</span>'; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-mono font-bold text-slate-900">
                                    <?php
                                        if ($row['sign_out_time']) {
                                            $start = strtotime($row['sign_in_time']);
                                            $end = strtotime($row['sign_out_time']);
                                            echo round(($end - $start) / 3600, 2) . 'h';
                                        } else {
                                            echo '--';
                                        }
                                    ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Update clock every second
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('currentTime').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>