<?php
    require "./header.php";
    $username = $user_data['user_name'];

    if(isset($_GET['response'])){
        $response = $_GET['response'];
        $response = `
            <div class="response-area border w-3/4 mt-4 p-2 border-red-300 bg-red-50 text-red-900">
                {$response}
            </div>
        `;
    }else{
        $response = "";
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
    <link rel="stylesheet" href="./css/main_style.css">
</head>
<body class="bg-green-100 p-5">
    <?php include "./architecture/top_bar2.php" ?>

    <div class="welcome-msg text-xl text-green-800">
        Welcome, <span><?php echo $username ?></span>
    </div>

    <div class="w-full flex flex-col md:flex-row gap-2 md:mt-8 px-20 justify-end">
        <div class="w-full md:full md:mt-2 md:mx-96  bg-white p-6 md:pr-6 mt-28 border pt-6 top-0 shadow-lg rounded-lg">
            <div class="header text-lg text-green-700 font-semibold">Citizen Requests</div>
            <div class="re text-sm font-normal text-green-500">Request submission portal</div>
            
            <?php echo $response ?>
       
            <!-- <div class="form-box border w-full p-6 shadow-sm mt-8 rounded-lg"> -->
                <form action="./process_request.php" method="POST" class=" mt-6">

                    <label for="request-type" class="text-green-700 text-sm pb-1 font-medium">Request Type</label>
                    <select name="request-type" class="w-full border border-green-400 text-green-900 mb-4 cursor-pointer p-2 rounded-lg focus:ring-2 focus:ring-green-500 transition" id="request-type">
                        <option value="permit" class="cursor-pointer">Permit</option>
                        <option value="certificate" class="cursor-pointer">Certification</option>
                    </select>

                    <label for="request-type" class="text-gray-500 text-sm pb-1 font-normal">Category</label>
                    <select name="request-category1" class="w-full border border-green-400 text-green-900 mb-4 cursor-pointer p-2 rounded-lg focus:ring-2 focus:ring-green-500 transition" id="request-category-permit">
                        <option value="business" class="cursor-pointer">Business permit</option>
                        <option value="construction" class="cursor-pointer">Construction permit</option>
                        <option value="event" class="cursor-pointer">Event permit</option>
                    </select>

                    <select name="request-category2" class=" hidden w-full border border-green-400 text-green-900 mb-4 cursor-pointer p-2 rounded-lg focus:ring-2 focus:ring-green-500 transition" id="request-category-certificate">
                        <option value="birth" class="cursor-pointer">Birth certificate</option>
                        <option value="marriage" class="cursor-pointer">Marriage Certificate</option>
                        <option value="police clearance" class="cursor-pointer">Police Clearance Certificate</option>
                    </select>
                    
                    <button value="submit" type="submit" class="p-2 px-4 rounded text-white bg-green-700 focus:bg-green-900 transition-all hover:bg-green-800 border-none font-medium text-sm w-full md:w-auto">Submit Request</button>
                </form>
            <!-- </div> -->
        </div>
    </div>


    <script>
        let certificateCategories = document.getElementById("request-category-certificate");
        let permitCategories = document.getElementById("request-category-permit");

        let requestType = document.getElementById("request-type");
            requestType.addEventListener("change", ()=>{
                if(requestType.value == "permit"){
                    permitCategories.style.display = "block";
                    certificateCategories.style.display = "none";
                }else{
                    permitCategories.style.display = "none";
                    certificateCategories.style.display = "block";
                }
            })

    </script>
</body>
</html>
