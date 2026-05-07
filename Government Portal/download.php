<?php

    require "./header.php";
    $user_id = $user_data['user_id'];

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        $id = $_GET['id'];

        $qry = "select * from requests where request_id = '$id' limit 1";
        $result = mysqli_query($con, $qry);

        if($result -> num_rows > 0){
            while($row = $result -> fetch_assoc()){
                $request_name = $row['request_type'];
                $cat = $row['request_category'];
                $date = $row['created_at'];
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> -->
    <style>
        .page{
            height: 850px;
            background-image: url("./Images/zm2.png");
            background-position: center;
            background-size: cover;
        }
        @media screen and (max-width: 768px) {
            .page{
                margin: 0px;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
<?php include "./architecture/top_bar2.php" ?>
    
<button onclick="generatePDF()" class=" fixed bottom-10 right-20 bg-green-700 text-white p-2 rounded hover:bg-green-800 px-4">
    <i class="si-arrow-down"></i>
    Download as PDF
</button>

    <div id="content" class="page p-14 bg-white shadow mt-5 mx-5">
        <div class="logo flex justify-center mt-3 ">
            <img src="./Images/zm.png" class=" w-28 h-30 object-cover" alt="">
        </div>

        <div class="letter text-md mt-10">
            This is a comfirmation of the approval to your 
            <?php echo $request_name ?> application 
            submitted on <?php
                $date = substr($date, 0 , 10);
                echo $date; 
            ?>,  
            by the Government of Zambia,
            through the Citizen service portal. 
            You are therefore, permitted to proceed with the 
            process, with this document acting as permission by the 
            Government Of Zambia under the Local Government.
            <br><br>
            Further enquiries submitted through the Citizen portal.
        </div>


            <div class="sign mt-28 absolute bottom-0">
                <img src="./Images/sign.png" class="w-20 h-20" alt="">
                <div class="sign-name">Government of Zambia</div>
                <div class="sign-name">Office of the Local Government</div>
            </div>
    </div>



    <script>
        function generatePDF() {
            const content = document.getElementById("content");
            html2pdf().from(content).save();
        }
    </script>
</body>
</html>