<?php
require 'auth.php';
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_role = $_SESSION['user_role'] ?? 'user';

ob_start();



// Zambian map
if (isset($_GET['proxy']) && $_GET['proxy'] === 'country') {
    // PHP proxy mode: fetch country info from amCharts
    header("Content-Type: application/json");
    echo file_get_contents("https://www.amcharts.com/tools/country/?v=xz6Z");
    exit;
}
?>

<div class="flex h-screen overflow-hidden bg-gray-100">

    <?php include "./includes/sidebar.php"; ?>

    <!-- Main Content -->
    <main class="ml-72 w-full p-6 overflow-y-auto">
        <h1 class="text-2xl font-bold mb-4">Welcome back!</h1>

        <?php if ($user_role === 'admin'): ?>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-lg font-semibold mb-3">Admin Panel Overview</h2>
                <p class="text-gray-700 text-sm">You can manage users and view all immigration records here.</p>

                <!-- Admin User Table -->
                <div class="mt-4">
                    <h3 class="text-md font-semibold mb-2">Registered Users</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="p-2 text-left">Name</th>
                                    <th class="p-2 text-left">Email</th>
                                    <th class="p-2 text-left">Contact</th>
                                    <th class="p-2 text-left">Role</th>
                                    <th class="p-2 text-left"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = $conn->query("SELECT first_name, last_name, email, phone_number, user_role FROM users");
                                while ($row = $users->fetch_assoc()):
                                ?>
                                    <tr class="border-t">
                                        <td class="p-2"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                        <td class="p-2"><?= htmlspecialchars($row['email']) ?></td>
                                        <td class="p-2"><?= htmlspecialchars($row['phone_number']) ?></td>
                                        <td class="p-2">
                                            <?php 
                                                $user_role = htmlspecialchars($row['user_role']);
                                                if($user_role == "user"){
                                                    echo "citizen";
                                                }else{
                                                    echo htmlspecialchars($row['user_role']);
                                                }
                                            ?>
                                        </td>
                                        <td class="p-2 text-blue-500 font-semibold hover:text-blue-700">
                                            <a href="#">view</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <div class="flex justify-between gap-2">
                <div class="bg-white w-auto p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-3">Apply Immigration</h2>
                    <p class="text-sm text-gray-700">Access the Immigration application form online. Fill in the form and follow the instructions.</p>
                    <a href="apply.php" class="text-green-600 hover:underline ">
                        <div class="apply font-medium p-2 px-3 mt-4 hover:bg-green-50 border w-full rounded-md">
                            Apply
                        </div>
                    </a>
                </div>
<!-- 
                <div class="bg-white p-4 rounded shadow ">
                    <h2 class="text-lg font-semibold mb-3">Application and status</h2>
                    <p class="text-sm text-gray-700">You can check your application details and current status.</p>
                    <a href="#" class="text-green-600 hover:underline font-medium">View My Application</a>
                </div>
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-3">Your Immigration Summary</h2>
                    <p class="text-sm text-gray-700">View your immigration application response from the Immigration office.</p>
                    <a href="#" class="text-green-600 hover:underline font-medium">See feedback</a>
                </div> -->

            </div>
            

            




















        <?php endif; ?>
    </main>
</div>

<?php
$content = ob_get_clean();
$title = "Dashboard";
include 'layout.php';
?>
