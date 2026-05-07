<?php 

    require 'auth.php';
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_name = $_SESSION['user_name'] ?? 'User';
    $user_role = $_SESSION['user_role'] ?? 'user';
    $user_id = $_SESSION['user_id'];

    ob_start();

?>
<link rel="stylesheet" href="./css/tailwind.css">
<div class="flex h-screen overflow-hidden bg-gray-100">

<?php include "./includes/sidebar.php"; ?>
 
    <main class="ml-72 w-full p-6 overflow-y-auto">
        <div class="header-text text-lg font-semibold">Applications</div>
        <?php 
            $sql = "select * from applications where user_id ='$user_id'";
            $res = mysqli_query($conn, $sql);
            if($res -> num_rows > 0){
                while($row = $res -> fetch_assoc()){
        ?>
            <div class="applications block">
                <div class="application border bg-white p-5 mt-2 rounded">
                    <div class="name text-sm font-semibold"><?php echo $row['name'] ?></div>
                    <div class="des text-sm text-gray-500 my-4"><?php echo $row['description']?></div>

                    <div class="nationality text-gray-700">From : <?php echo $row['nationality'] ?></div>
                    <div class="desitination text-gray-700">To : <?php echo $row['destination']?></div>

                    <div class="office mt-4">

                    </div>
                    <?php 
                        if($row['status'] == "pending"){
                    ?>
                        <div class="status w-full p-4 rounded-md bg-gray-50 mt-5 border">Pending</div>
                    <?php
                        }else if($row['status'] == "approved"){
                    ?>
                        <div class="status w-full p-4 rounded-md bg-green-50 mt-5 border border-green-200">Approved</div>
                    <?php
                        }else{
                    ?>
                        <div class="status w-full p-4 rounded-md bg-red-50 mt-5 border">Rejected</div>
                    <?php
                        }
                    ?>
                    
                </div>
            </div>
        <?php
                }
            }else{
        ?>
            <div class="no-application border bg-white p-5 mt-2 rounded">
                No applications have been submitted
            </div>
        <?php
            }
        ?>

       
    </main>
</div>


<?php
$content = ob_get_clean();
$title = "Dashboard";
include 'layout.php';
?>
































