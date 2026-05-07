<?php

$role = $_SESSION['role'];
?>

<div class="w-64 h-screen bg-gray-800 text-white fixed top-0 left-0 shadow-lg">
    <div class="p-4 text-xl font-bold border-b border-gray-700">
        Bus System
    </div>

    <ul class="mt-4 space-y-2 px-4">
        <li><a href="dashboard.php" class="block py-2 px-3 rounded hover:bg-gray-700">Dashboard</a></li>

        <?php if ($role === 'admin'): ?>
            <li><a href="manage_routes.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Routes</a></li>
            <li><a href="bus_students.php" class="block py-2 px-3 rounded hover:bg-gray-700">Student trips</a></li>
            <li><a href="manage_stops.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Stops</a></li>
            <li><a href="manage_buses.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Buses</a></li>
            <li><a href="manage_drivers.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Drivers</a></li>
            <li><a href="manage_students.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Students</a></li>
            <li><a href="manage_parents.php" class="block py-2 px-3 rounded hover:bg-gray-700">Manage Parents</a></li>
        <?php elseif ($role === 'driver'): ?>
            <li><a href="my_route.php" class="block py-2 px-3 rounded hover:bg-gray-700">My Route</a></li>
            <li><a href="student_list.php" class="block py-2 px-3 rounded hover:bg-gray-700">Student List</a></li>
            <li><a href="attendance.php" class="block py-2 px-3 rounded hover:bg-gray-700">Mark Attendance</a></li>
        <?php elseif ($role === 'parent'): ?>
            <li><a href="child_info.php" class="block py-2 px-3 rounded hover:bg-gray-700">My Child Info</a></li>
            <li><a href="track_bus.php" class="block py-2 px-3 rounded hover:bg-gray-700">Track Bus</a></li>
            <li><a href="attendance_record.php" class="block py-2 px-3 rounded hover:bg-gray-700">Attendance Record</a></li>
        <?php endif; ?>

        <li><a href="../actions/logout.php" class="block py-2 px-3 rounded hover:bg-red-600 text-red-300">Logout</a></li>
    </ul>
</div>
