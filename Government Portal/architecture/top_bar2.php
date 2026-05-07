<style>
    @media (max-width: 750px) {
    .left-sidebar{
        display: none;
    } 
    .main-dashboard{
        margin: 10px;
    }    
    .desktop-top-nav{
        display: none;
    }
}
</style>

<div class="desktop-top-nav fixed 2xs:hidden w-full top-0 left-0 bg-green-700 p-2 shadow-md flex justify-between gap-2">
    <div class="logo-name text-lg px-4 pt-2 text-white"> Online Citizen Service Portal </div>
    <div class="right flex justify-between w-1/2">
        <a href="./requests.php" class="section text-white w-full flex justify-center gap-2 rounded-md p-2 hover:bg-green-900 hover:text-white transition-all cursor-pointer text-2xl">
            <div class="home text-sm pt-1">Requests</div>
        </a> 

        <a href="./user_request.php" class="section text-white w-full flex justify-center gap-2 rounded-md p-2 hover:bg-green-900 hover:text-white transition-all cursor-pointer text-2xl">
            <div class="home text-sm pt-1">Submit request</div>
        </a>   
        <a href="./notifications.php" class="section text-white w-full flex justify-center gap-2 rounded-md p-2 hover:bg-green-900 hover:text-white transition-all cursor-pointer text-2xl">
            <div class="home text-sm pt-1">Updates</div>
        </a>        

        <a href="./includes/logout.php" class="section text-red-500 w-full flex justify-center gap-2 rounded-md p-2 bg-white hover:bg-red-500 ml-2 hover:text-white transition-all cursor-pointer text-2xl">
          
            <div class="home text-sm pt-1">Logout</div>
        </a>
    </div>
</div>




<div id="menu-tab" class="fixed md:hidden w-full top-0 left-0 bg-green-700 p-2 shadow-md">
    <div class="main-top-nav flex justify-between items-center">
        <div class="logo-name text-lg px-4 text-white"> Online Citizen Service Portal </div>
        <div class="right flex border gap-4 mr-4 md:mr-10 bg-gray-100 p-2 rounded-md">
            <i id="menu" class="si-text-right"></i>
        </div>
    </div>
    

    <div id="nav-list" class="tabs hidden bg-green-700 mt-4">
        <a href="./requests.php" class="section  text-white w-full flex gap-2 rounded-md mt-2 p-2 hover:bg-green-900 0 transition-all cursor-pointer text-2xl">
            <i class="si-archive"></i>
            <div class="home text-sm text-white pt-1">My requests</div>
        </a> 

        <a href="./user_request.php" class="section  text-white w-full flex gap-2 rounded-md mt-2 p-2 hover:bg-green-900 0 transition-all cursor-pointer text-2xl">
            <i class="si-check-square"></i>
            <div class="home text-sm text-white pt-1">Submit request</div>
        </a>        

        <a href="./includes/logout.php" class="section  text-white w-full flex gap-2 rounded-md mt-2 p-2 hover:bg-green-900 0 transition-all cursor-pointer text-2xl">
            <i class="si-lock"></i>
            <div class="home text-sm text-white pt-1">Logout</div>
        </a>
    </div>
</div>




<script>
    let menu = document.getElementById("menu");
    let navbar = document.getElementById("menu-tab");
    let navList = document.getElementById("nav-list");
    let navState = false;
    menu.addEventListener("click", ()=>{
        if(navState){
            navbar.style.height = "50px";
            navbar.style.transition = "0.5s";
            menu.setAttribute("class", "si-text-right");
            navList.style.display =  "none";
            navState = false;
        }else{
            navbar.style.height = "100vh";
            navbar.style.transition = "0.5s";
            menu.setAttribute("class", "si-text-justify");
            navList.style.display =  "block";
            
            navState = true;
        }
    })

</script>



