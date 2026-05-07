<?php
    require "./header.php";
    $user_id = $user_data['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/tailwind.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/main_style.css">
    <style>
        .application-request{
            width: 95%;
            margin: 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
<?php include "./architecture/top_bar2.php" ?>

    <div class=" text-2xl font-medium mt-16 mx-6">Updates</div>

<div class=" w-full p-2">

    <?php
        $qry = "select * from notifications where user_id = '$user_id'";
        $result = mysqli_query($con, $qry);

        if($result -> num_rows > 0){
            while($row = $result -> fetch_assoc()){
                $note_name = $row['notification_name'];
                $note_des = $row['notification_description'];
                $status = $row['status'];
                $date = $row['created_at'];
                $request_id = $row['request_id'];
                
                if($status == "approved"){
    ?>

<div class="application-request shadow mt-2 bg-white p-4 rounded-md border-gray-200 cursor-default hover:border-blue-500 hover:shadow-md transition-all w-full p-5 shadow-sm mb-4 rounded-md">
    <div class="top-area flex justify-between gap-2">
        <div class="username font-medium">
            <span class="text-md"><?php echo $note_name ?> approved</span>
            <span class=" text-xs text-gray-400 pl-4"><?php echo $date ?></span>
        </div>
        <div class="btns flex justify-between gap-4">
            <a href="./delete_note.php?id=<?php echo $row['id'] ?>">
                <button class="p-2 px-4 rounded text-gray-500 focus:bg-gray-100 hover:bg-gray-100 hover:text-red-400 font-medium text-md">Delete</button>
            </a>


            <a href="./download.php?id=<?php echo $request_id ?>">
                <button class="p-2 px-4 rounded text-white bg-green-700 focus:bg-green-700 hover:bg-green-900 font-medium text-sm">Download</button>
            </a>

        </div>
    </div>
    <div class="username font-medium text-gray-500 text-xs mb-4 w-full">
        <?php echo $note_des ?>.
        <a href="download.php?id=<?php echo $request_id ?>" class=" text-green-700 hover:underline">Download as a document.</a>
    </div>  
</div>  


    <?php
                }else{
    ?>

<div class="application-request shadow w-full mt-2 bg-white p-4 rounded-md border-gray-200 cursor-default hover:border-blue-500 hover:shadow-md transition-all w-full p-5 shadow-sm mb-4 rounded-md">
    <div class="top-area flex justify-between gap-2">
        <div class="username font-medium">
            <span class="text-md text-red-500"> <?php echo $note_name ?> rejected</span>
            <span class=" text-xs text-gray-400 pl-4"><?php echo $date ?></span>
        </div>
        <div class="btns flex justify-between gap-4">
            <a href="./delete_note.php?id=<?php echo $row['id'] ?>">
                <button class="p-2 px-4 rounded text-gray-500 focus:bg-gray-100 hover:bg-gray-100 hover:text-red-400 font-medium text-md"><i class="si-trash"></i></button>
            </a>

            <!-- <a href="./update_request.php?id=<?php echo '3' ?>">
                <button class="p-2 px-4 rounded text-white bg-white border text-gray-700 hover:bg-gray-200 hover:text-gray-700 font-medium text-sm">Send response</button>
            </a> -->

        </div>
    </div>
    <div class="username font-medium text-gray-500 text-xs mb-4 w-full">
        <?php echo $note_des ?>
        <a href="#" class=" text-blue-700 hover:underline">Respond.</a>
    </div>  
</div>

    <?php
                }
            }
        }

    ?>   


</div>
</body>
</html>