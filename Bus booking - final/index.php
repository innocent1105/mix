<?php
    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: pages/dashboard.php");
        exit;
    }

    // $email = 'admin@example.com';
    // $password = password_hash('admin123', PASSWORD_DEFAULT);
    // $role = 'admin';



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Bus Management</title>
    <link href="../css/tailwind.css" rel="stylesheet">
    <link href="./css/tailwind.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="fixed top-0 left-0 right-0 z-10 h-16 bg-blue-600 flex items-center px-6 shadow text-white">
        <h1 class="text-xl font-bold">Student Bus Management System</h1>
    </header>
    <div class="pt-16"> 


<div class="flex items-center justify-center min-h-screen">
    <form method="POST" action="actions/login.php" class="bg-white shadow-md p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <label class="block mb-2">Email</label>
        <input type="email" name="email" required class="w-full p-2 border rounded mb-4">

        <label class="block mb-2">Password</label>
        <input type="password" name="password" required class="w-full p-2 border rounded mb-4">

        <label class="block mb-2">Login as</label>
        <select name="role" required class="w-full p-2 border rounded mb-6">
            <option value="admin">Admin</option>
            <option value="driver">Driver</option>
            <option value="parent">Parent</option>
        </select>

        <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700">Login</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
