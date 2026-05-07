<!-- Sidebar -->
<aside class="w-72 bg-white shadow-md fixed inset-y-0 left-0 z-10">
    <div class="p-4 border-b flex">
        <img src="./img/logo_prev_ui (1).png" class=" w-10 h-6 mt-2" alt="">
        <h2 class="text-md font-bold text-yellow-500 px-2">Immigration Services Of Zambia</h2>
    </div>
    <nav class="p-4 space-y-2">
        <?php if ($user_role === 'admin'): ?>
            <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-green-100">Dashboard</a>
            <!-- <a href="#" class="block px-4 py-2 rounded hover:bg-green-100">Manage Users</a> -->
            <a href="registrations.php" class="block px-4 py-2 rounded hover:bg-green-100">View Registrations</a>
            <a href="add_office.php" class="block px-4 py-2 rounded hover:bg-green-100">Add office</a>
            <a href="#" class="block px-4 py-2 rounded hover:bg-green-100">Settings</a>
        <?php else: ?>
            <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-green-100">Dashboard</a>
            <a href="my_applications.php" class="block px-4 py-2 rounded hover:bg-green-100">My Applications</a>
            <!-- <a href="#" class="block px-4 py-2 rounded hover:bg-green-100">Status</a> -->
        <?php endif; ?>
        <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-100 rounded">Logout</a>
    </nav>
</aside>