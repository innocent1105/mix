<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>

<!-- header.php -->
<nav class="bg-blue-600 shadow-md">
    <div class=" fixed top-0 w-full left-0 right-0 backdrop-blur-md p-4 mx-auto flex items-center justify-between">
    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] == "admin"): ?>
        <a href="./admin.php" class="text-white text-2xl font-semibold">EMS</a>
    <?php else: ?>
        <a href="index.php" class="text-white text-2xl font-semibold">EMS</a>
    <?php endif; ?>
        <div class="flex items-center space-x-4">
          
        </div>
    </div>
</nav>
