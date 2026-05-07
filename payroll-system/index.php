<?php
session_start();
require 'includes/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'role' => $user['role']
        ];
        header("Location: dashboard/" . ($user['role'] == 'admin' ? 'admin.php' : 'employee.php'));
        exit();
    } else {
        $errors[] = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <?php include 'includes/header.php'; ?>
    <title>EMS | Modern Payroll Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(rgba(17, 24, 39, 0.8), rgba(17, 24, 39, 0.8)), url("./bg.jpg");
            background-size: cover;
            background-position: center;
        }
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

<nav class="fixed w-full z-50 glass border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">E</div>
                <span class="text-xl font-bold tracking-tight text-slate-800 uppercase">EMS<span class="text-blue-600">.</span></span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#" class="hover:text-blue-600 transition">Home</a>
                <a href="#features" class="hover:text-blue-600 transition">Features</a>
                <a href="#how" class="hover:text-blue-600 transition">Workflow</a>
                <a href="#login" class="px-5 py-2.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition shadow-md shadow-blue-200">Sign In</a>
            </div>

            <div class="md:hidden">
                <button id="mobile-menu-btn" class="text-slate-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>
        </div>
    </div>
    <div id="mobile-menu" class="hidden md:hidden bg-white border-b px-4 py-4 space-y-3">
        <a href="#" class="block text-slate-600 font-medium">Home</a>
        <a href="#features" class="block text-slate-600 font-medium">Features</a>
        <a href="#login" class="block text-blue-600 font-bold">Sign In</a>
    </div>
</nav>

<section class="hero-gradient pt-32 pb-20 md:pt-48 md:pb-40 text-white">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <span class="inline-block px-4 py-1.5 mb-6 text-xs font-semibold tracking-widest uppercase bg-blue-500/20 border border-blue-400/30 rounded-full">New: Automated Payroll v2.0</span>
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight">Better Payroll, <br><span class="text-blue-400">Smarter Workforce.</span></h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-10">Effortlessly manage your workforce with real-time attendance, automated salary calculations, and beautiful analytics.</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="#login" class="px-8 py-4 bg-blue-600 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">Get Started Now</a>
            <a href="#features" class="px-8 py-4 bg-white/10 backdrop-blur rounded-xl font-bold hover:bg-white/20 transition border border-white/10">View Features</a>
        </div>
    </div>
</section>

<section id="features" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-blue-600 font-bold uppercase tracking-wider text-sm mb-3">Capabilities</h2>
            <h3 class="text-3xl md:text-4xl font-bold text-slate-900">Everything you need to scale</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="p-8 border border-slate-100 rounded-3xl bg-slate-50 hover:shadow-xl transition group">
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h4 class="text-xl font-bold mb-3">Live Attendance</h4>
                <p class="text-slate-600 leading-relaxed">Real-time GPS-ready tracking of employee shifts, breaks, and overtime with instant logs.</p>
            </div>

            <div class="p-8 border border-slate-100 rounded-3xl bg-slate-50 hover:shadow-xl transition group">
                <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h4 class="text-xl font-bold mb-3">Auto Payroll</h4>
                <p class="text-slate-600 leading-relaxed">Smart algorithms calculate hourly rates, deductions, and taxes instantly. One-click payslips.</p>
            </div>

            <div class="p-8 border border-slate-100 rounded-3xl bg-slate-50 hover:shadow-xl transition group">
                <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <h4 class="text-xl font-bold mb-3">Rich Insights</h4>
                <p class="text-slate-600 leading-relaxed">Detailed dashboards for both Admins and Employees to monitor performance and earnings.</p>
            </div>
        </div>
    </div>
</section>

<section id="login" class="py-24 bg-slate-100 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-200/50 rounded-full blur-3xl -mr-32 -mt-32"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-200/50 rounded-full blur-3xl -ml-32 -mb-32"></div>

    <div class="max-w-md mx-auto px-6 relative z-10">
        <div class="bg-white p-10 rounded-3xl shadow-2xl border border-white">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-slate-900">Welcome Back</h3>
                <p class="text-slate-500 mt-2">Please enter your details to login</p>
            </div>

            <?php if ($errors): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-r-lg">
                    <?php foreach ($errors as $error) echo "<p class='font-medium'>$error</p>"; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Email Address</label>
                    <input type="email" name="email" required placeholder="name@company.com" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" required placeholder="••••••••" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition">
                </div>
                <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                    Sign In to Portal
                </button>
            </form>
        </div>
    </div>
</section>

<footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <div class="flex items-center justify-center gap-2 mb-6 text-white">
            <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center font-bold text-sm">E</div>
            <span class="font-bold tracking-tight uppercase">EMS.</span>
        </div>
        <p class="text-sm">&copy; <?php echo date('Y'); ?> Employee Management System. Built for high-performance teams.</p>
    </div>
</footer>

<script>
    // Simple Mobile Menu Toggle
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });
</script>

</body>
</html>