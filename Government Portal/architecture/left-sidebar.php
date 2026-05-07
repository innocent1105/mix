<style>
    .side-nav-ele{
        height: 90%;
    }
</style>
<div class="left-sidebar w-1/5 p-2  fixed h-screen left-0 top-0">
    <div class="header text-xl text-green-700 mt-4 pl-4 font-medium">Online Citizen Service Portal</div>
    <div class="side-nav-ele mt-2 flex justify-between flex-col">
        <div class="sections w-full p-2 mt-4">

            <a href="./dashboard.php" class="section w-full flex gap-2 hover:border rounded-md mt-2 p-2 hover:bg-gray-100 hover:text-green-700 transition-all cursor-pointer text-2xl">
                <i class="si-grid"></i>
                <div class="home text-sm pt-1">Dashboard</div>
            </a>

            <a href="./permits.php" class="section w-full flex gap-2 hover:border rounded-md mt-2 p-2 hover:bg-gray-100 hover:text-green-700 transition-all cursor-pointer text-2xl">
                <i class="si-file-check"></i>
                <div class="home text-sm pt-1">Permits</div>
            </a>

            <a href="./certifications.php" class="section w-full flex gap-2 hover:border rounded-md mt-2 p-2 hover:bg-gray-100 hover:text-green-700 transition-all cursor-pointer text-2xl">
                <i class="si-file"></i>
                <div class="home text-sm pt-1">Certifications</div>
            </a>

            <!-- <a href="#" class="section w-full flex gap-2 rounded-md mt-2 p-2 hover:bg-gray-100 hover:text-green-700 transition-all cursor-pointer text-2xl">
                <i class="si-home"></i>
                <div class="home text-sm pt-1"></div>
            </a> -->
        
        </div>

        <div class="btm p-2">
            <a href="./includes/logout.php" class="section w-full flex gap-2 rounded-md mt-2 p-2 hover:bg-gray-100 hover:text-green-700 transition-all cursor-pointer text-2xl">
                <i class="si-lock"></i>
                <div class="home text-sm pt-1">Logout</div>
            </a>
        </div>
    </div>
</div>
