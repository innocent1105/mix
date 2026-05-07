<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $hourlyRate = $_POST['hourly_rate'] ?? 0;

    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All required fields must be filled.";
    } elseif ($role === 'employee' && (!is_numeric($hourlyRate) || $hourlyRate <= 0)) {
        $errors[] = "Please enter a valid hourly rate for employees.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, hourly_rate) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword, $role, $hourlyRate]);
            $success = true;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $errors[] = "This email is already registered.";
            } else {
                $errors[] = "Database error: Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/header.php'; ?>
    <title>Register User | EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-input {
            @apply w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all;
        }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="w-full max-w-xl">
        <a href="dashboard/admin.php" class="inline-flex items-center text-sm font-semibold text-slate-500 hover:text-blue-600 mb-6 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            Back to Dashboard
        </a>

        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
            <div class="bg-slate-900 p-8 text-white">
                <h2 class="text-2xl font-bold">Register New User</h2>
                <p class="text-slate-400 text-sm mt-1">Add a new member to the organization.</p>
            </div>

            <div class="p-8">
                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-lg flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                        User registered successfully!
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg">
                        <?php foreach ($errors as $error) echo "<p class='text-sm font-medium'>$error</p>"; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Full Name</label>
                        <input type="text" name="full_name" required placeholder="John Doe" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Email Address</label>
                        <input type="email" name="email" required placeholder="john@company.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Initial Password</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">User Role</label>
                        <select name="role" id="roleSelect" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none appearance-none">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div id="rateContainer">
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Hourly Rate (K)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3 text-slate-400 font-medium">K</span>
                            <input type="number" name="hourly_rate" step="0.01" min="0" placeholder="0.00" class="w-full pl-8 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="md:col-span-2 mt-4">
                        <button type="submit" class="w-full py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
                            Create User Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Toggle Hourly Rate visibility based on Role
        const roleSelect = document.getElementById('roleSelect');
        const rateContainer = document.getElementById('rateContainer');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'admin') {
                rateContainer.style.opacity = '0.3';
                rateContainer.style.pointerEvents = 'none';
            } else {
                rateContainer.style.opacity = '1';
                rateContainer.style.pointerEvents = 'auto';
            }
        });
    </script>
</body>
</html>