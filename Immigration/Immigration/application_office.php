<?php 

    require 'auth.php';
    require 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_name = $_SESSION['user_name'] ?? 'User';
    $user_role = $_SESSION['user_role'] ?? 'user';
    $office_name = "";
    $office_id = '';
    $office_description = "";
    $office_email = "";
    $office_contact = "";

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        if(isset($_GET['id'])){
            $office_id = $_GET['id'];
            $qry = "select * from offices where id='$office_id' limit 1";
            $result = mysqli_query($conn, $qry);
            if($result -> num_rows > 0){
                while($row = $result -> fetch_assoc()){
                    $office_name = $row['name'];
                    $office_description = $row['description'];
                    $office_email = $row['email'];
                    $office_contact = $row['contact'];
                    
                }
            }
        }else{
            header("Location: ./apply.php");
        }
    }

    $response = "";

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_GET['id'])){
            $office_id = $_GET['id'];
            $qry = "select * from offices where id='$office_id' limit 1";
            $result = mysqli_query($conn, $qry);
            if($result -> num_rows > 0){
                while($row = $result -> fetch_assoc()){
                    $office_name = $row['name'];
                    $office_description = $row['description'];
                    $office_email = $row['email'];
                    $office_contact = $row['contact'];
                    
                }
            }
        }

        $user_id = $_SESSION['user_id'];
        $name = stripslashes($_POST['name']);
        $description = stripslashes($_POST['description']);
        $type = stripslashes($_POST['type']);
        $nationality = stripslashes($_POST['nationality']);
        $destination = stripslashes($_POST['destination']);
        $contact = stripslashes($_POST['contact']);

        $qry = "insert into applications ( user_id, office_id, name, type, description, nationality, destination, contact, status) values ('$user_id', '$office_id', '$name', '$description', '$type','$nationality', '$destination', '$contact', 'pending')";
        $result = mysqli_query($conn, $qry);
        if(!$result){
            $response = "error";
        }else{
            $response = "success";
        }
    }







    ob_start();
?>


<div class="flex pb-10overflow-hidden bg-gray-100">

    <?php include "./includes/sidebar.php"; ?>
    <div class="ml-72 w-full pb-10">
    <div class="bg-gray-100 pb-10 flex items-center justify-center p-4">
    <div class="bg-white pb-10 p-6 rounded-2xl shadow-lg w-full max-w-2xl">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Application to <?php echo $office_name ?></h2>

        <?php
            if($response == "success" && $response != ""){
        ?>
            <div class="border border-2 bg-green-50 border-green-300 text-green-500 p-4 rounded-md my-2">
                Application submitted successfully.
            </div>
        <?php
            }else if($response == "error" && $response != ""){
        ?>
            <div class="border border-2 bg-red-50 border-red-300 text-red-500 p-4 rounded-md my-2">
                Failed : please try submitting again.
            </div>

        <?php
            }
        ?>
        <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div class="details text-md text-gray-500">
                <?php echo $office_description ?>
            </div>

            <div>
                <label for="name" class="block text-gray-700 px-1 font-medium mb-1">Applicant name</label>
                <input type="text" id="name" name="name" required placeholder="Enter province name" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="description" class="block text-gray-700 px-1 font-medium mb-1">Application description</label>
                <textarea id="description" name="description" required placeholder="Add a description" class="w-full px-4 py-2 resize-none border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <!-- Add more types here !! -->
            <div>
                <label for="description" class="block text-gray-700 px-1 font-medium mb-1">Application type</label>
                <select name="type" id="type" class="w-full px-4 py-2 border rounded-xl cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="migrant">Migrant</option>
                    <option value="work_permit">International work permit</option>
                </select>    
            </div>

            <div>
                <label for="nationality" class="block text-gray-700 px-1 font-medium mb-1">Country of Nationality</label>
                <input type="text" id="nationality" name="nationality" required placeholder="What is your Nationality?" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="destination" class="block text-gray-700 px-1 font-medium mb-1">Country of Destination</label>
                <input type="text" id="destination" name="destination" required placeholder="What is your desitination?" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">   
            </div>

            <!-- Contact Details -->
            <div>
                <label for="contact" class="block text-gray-700 px-1 font-medium mb-1">Contact Number</label>
                <input type="text" id="contact" name="contact" required placeholder="contact details" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition">Submit</button>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Dashboard";
include 'layout.php';
?>











































