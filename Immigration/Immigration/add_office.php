<?php 
    include "./db.php";

    $response = "";

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        if(isset($_GET['response'])){
            $response = $_GET['response'];
        }
    }

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
</head>
<body>

<div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
    <div class="bg-white p-6 rounded-2xl shadow-lg w-full max-w-2xl">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Add New Office</h2>

        <?php
            if($response == "success" && $response != ""){
        ?>
        <div class="border border-2 bg-green-50 border-green-300 text-green-500 p-4 rounded-md my-2">Successfully added an Office.</div>
        <?php
            }else if($response == "error" && $response != ""){
        ?>
        <div class="border border-2 bg-red-50 border-red-300 text-red-500 p-4 rounded-md my-2">Failed : please try submitting again.</div>

        <?php
            }
        ?>
        <form action="./includes/add_office.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <!-- Province Selection -->
            <div>
                <label for="province_id" class="block text-gray-700 font-medium mb-1">Select Province</label>
                <select id="province_id" name="province_id" required
                    class="w-full px-4 py-2 border rounded-xl cursor-pointer bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected>-- Select Province --</option>

                    <?php
                        $stmt = $conn->prepare("SELECT id, name FROM provinces");
                        $stmt->execute();

                        // Use get_result() to fetch rows as an associative array
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
                        }

                        $stmt->close();
                    ?>
                   
                </select>
            </div>

            <div>
                <label for="office_name" class="block text-gray-700 font-medium mb-1">Office Name</label>
                <input type="text" id="office_name" name="office_name" required placeholder="Enter province name" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea id="description" name="description" required placeholder="Add office description or additional information" class="w-full px-4 py-2 resize-none border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div> 

            <!-- <div>
                <div class=" block text-gray-700 font-medium mb-1">Add image (optional)</div>
                <label for="image" id="image-lable" class=" border-2 gap-2 p-8 rounded-md hover:bg-gray-100 hover:shadow hover:border-blue-500 cursor-pointer hover:text-blue-500 transition-all flex justify-center ">
                    <i class="si-image"></i> 
                </label>
                
                <input type="file" id="image" name="image" accept="image/*"
                    class="w-full hidden px-4 py-2 border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <script>
                let imageTag = document.getElementById("image");
                let imageLable = document.getElementById("image-lable");
                    imageTag.addEventListener("change", (e)=>{
                        let imageValue = imageTag.value;
                           imageLable.innerHTML = "Image has been added";
                    })
            </script> -->

            <!-- Email -->
            <div>
                <label for="email" class="block text-gray-700 font-medium mb-1">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="Enter office email address" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Contact Details -->
            <div>
                <label for="contact" class="block text-gray-700 font-medium mb-1">Contact Number</label>
                <input type="text" id="contact" name="contact" required placeholder="contact details" class="w-full px-4 py-2 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-xl hover:bg-blue-700 transition">Add Office</button>
            </div>
        </form>
    </div>
</div>

    

<script src="./js/jquery.js"></script>
</body>
</html>